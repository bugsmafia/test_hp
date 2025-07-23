<?php
/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     HYPERPC
 * @license     Proprietary
 * @copyright   Proprietary https://hyperpc.ru/license
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Elements;

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use HYPERPC\Helper\CrmHelper;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\OrderHelper;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementCredit
 *
 * @since   2.0
 */
abstract class ElementCredit extends ElementPayment
{

    /**
     * Hold order object.
     *
     * @var     Order
     *
     * @since   2.0
     */
    protected $_order;

    /**
     * Fire uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriFire;

    /**
     * Test uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriTest;

    /**
     * Set approve flag to order if request status is success
     *
     * @param   Order $order
     *
     * @since   2.0
     */
    public function checkApprove(Order &$order)
    {
        if ($this->isSuccessRequestStatus()) {
            $order->params->set('loan_approved', true);
        }
    }

    /**
     * Check request order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    abstract public function checkOrder();

    /**
     * Get debug data.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getDebugData()
    {
        $path = $this->hyper['path']->get('elements:' . $this->_group . '/' . $this->_type . '/config/debug_data.php');
        if ($path) {
            /** @noinspection PhpIncludeInspection */
            $data = require_once $path;
            return (array) $data;
        }

        return [];
    }

    /**
     * Get delivery data by order.
     *
     * @param   Order  $order
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getDeliveryData(Order $order)
    {
        $delivery        = $order->getDelivery();
        $deliveryService = $delivery->getService();

        $deliveryPrice = $this->hyper['helper']['money']->get();
        //$shippingDates = $this->hyper['helper']['order']->getOrderShippingDates($order);

        $return = new JSON([
            'title'      => null,
            'price'      => $deliveryPrice,
            'isShipping' => $delivery->isShipping()
        ]);

        if ($return->get('isShipping')) {
            $deliveryItemName = [
                Text::_('COM_HYPERPC_DELIVERY'),
                '"' . $deliveryService . '"'
            ];

            // if (!empty($shippingDates->find('sending.dates'))) {
            //     $deliveryItemName[] = '(' . Text::_('COM_HYPERPC_ORDER_SENDING_DATE');
            //     $deliveryItemName[] = $shippingDates->find('sending.dates') . ')';
            // }

            $return
                ->set('price', $delivery->getPrice())
                ->set('title', implode(' ', $deliveryItemName));
        }

        return $return;
    }

    /**
     * Get failed status list.
     *
     * @return  array
     *
     * @since   2.0
     */
    abstract public function getFailedStatusList();

    /**
     * Get lof content.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getLogContent();

    /**
     * Get log type.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getLogType()
    {
        return 'credit_callback_' . $this->getType();
    }

    /**
     * Get payment method name.
     *
     * @return  \HYPERPC\Data\JSON|mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getMethodName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get order param element key.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getParamKey();

    /**
     * Get service redirect url.
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getRedirectUrl(Order $order);

    /**
     * Get request data.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getRequestData()
    {
        return new JSON([]);
    }

    /**
     * Get request order id.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function getRequestOrderId();

    /**
     * Get request status.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function getRequestStatus();

    /**
     * Get site status id by request status.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getSiteStatusByRequest() : int
    {
        $statusList = $this->getStatusList();
        return $statusList->find($this->getRequestStatus() . '.site_status', 0, 'int');
    }

    /**
     * Get status by alias.
     *
     * @param   string $alias
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getStatusByAlias($alias)
    {
        foreach ($this->getStatusMap() as $status) {
            $status = new JSON($status);
            if ($status->get('alias') === $alias) {
                return $status;
            }
        }

        return new JSON([]);
    }

    /**
     * Get status map.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getStatusMap()
    {
        return new JSON([]);
    }

    /**
     * Get status list.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getStatusList()
    {
        static $instances = [];

        if (!array_key_exists($this->getType(), $instances)) {
            $statusList = new JSON([]);
            $statusMap  = $this->getStatusMap();

            foreach ($this->_config->getArrayCopy() as $paramKey => $statusData) {
                if (preg_match('/^status/', $paramKey)) {
                    $statusData = new JSON($statusData);
                    list (, $sysId) = explode('_', $paramKey);
                    $sysId = '[' . Str::up($sysId) . ']';
                    if (is_array($statusMap->get($sysId))) {
                        $statusList->set($statusMap->find($sysId . '.alias'), array_merge(
                            $statusMap->get($sysId),
                            [
                                'pipeline'    => $statusData->get('pipeline'),
                                'site_status' => $statusData->get('site_status')
                            ]
                        ));
                    }
                }
            }

            $instances[$this->getType()] = $statusList;
        }

        return $instances[$this->getType()];
    }

    /**
     * Get status title.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function getStatusTitle();

    /**
     * Get success full url.
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUrlSuccess(Order $order)
    {
        $configSuccessUrl = $this->_config->get('successUrl');
        return ($configSuccessUrl) ? $configSuccessUrl : $this->hyper['route']->build([
            'view'  => 'order',
            'id'    => $order->id,
            'token' => $order->getToken()
        ], true);
    }

    /**
     * Get view url.
     *
     * @param   Order  $order
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(Order $order)
    {
        return $this->getRedirectUrl($order);
    }

    /**
     * Check is debug.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDebug()
    {
        return Filter::bool($this->_config->get('debug'));
    }

    /**
     * Check is enabled element.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isEnabled()
    {
        return Filter::bool($this->_config->get('is_enable', HP_STATUS_PUBLISHED));
    }

    /**
     * Is element available for render on an order page
     *
     * @param   Order $order
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isAvailableInOrder(Order $order): bool
    {
        if (!$order->id || !$this->isEnabled()) {
            return false;
        }

        if ($this->isForManager() && !$this->hyper['user']->isManager()) {
            return false;
        }

        return true;
    }

    /**
     * Check failed status.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isFailedRequestStatus()
    {
        return in_array($this->getRequestStatus(), $this->getFailedStatusList());
    }

    /**
     * Check current success merchant status.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function isSuccessRequestStatus();

    /**
     * Checks if order need to be updated by callback
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function needUpdateOrder()
    {
        return true;
    }

    /**
     * Element notify action.
     *
     * @return  bool
     *
     * @since   2.0
     */
    abstract public function notify();

    /**
     * Success lead process action.
     *
     * @param   int|string $id
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function processLead($id)
    {
        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $statusMessage = $this->_getCrmNoteStatusMessage();
        if (!empty($statusMessage)) { // Add note to lead
            $crmHelper->addNote(
                $id,
                implode(':', [
                    $this->_config->get('name'),
                    Text::sprintf('COM_HYPERPC_AMO_NOTE_SYSTEM_LEAD_UPDATE_CREDIT_STATUS', $statusMessage)
                ]),
                CrmHelper::NOTE_EVENT_DEAL_SYSTEM
            );
        }

        if ($this->_checkProcessLeadStatus()) {
            $updateData = [];

            if ($this->_order->isCredit() && !$this->_order->isLoanApproved()) {
                $elementStatusList = $this->getStatusList();
                $newStatusData = new JSON($elementStatusList->get($this->getRequestStatus()));
                $newCrmStatus = $newStatusData->get('pipeline', '');

                if (preg_match('/:/', $newCrmStatus)) {
                    list ($newPipeline, $newStatusId) = explode(':', $newCrmStatus);

                    $updateData['pipeline_id'] = $newPipeline;
                    $updateData['status_id'] = $newStatusId;
                }
            }

            $this->_onProcessLead($updateData);

            if ($this->isSuccessRequestStatus()) { // change default tag to success tag
                $successTag = $this->getConfig('crm_tag_success', '', 'trim');
                if (!empty($successTag)) {
                    $leadTags = $updateData['tags'] ?? $crmHelper->getLeadTags($id);
                    $defaultTag = $this->getConfig('crm_tag_default', '', 'trim');

                    $needUpdateTags = in_array($defaultTag, $leadTags) || !in_array($successTag, $leadTags);
                    if ($needUpdateTags) {
                        $leadTags = array_diff($leadTags, [$defaultTag]);
                        $leadTags = array_merge($leadTags, [$successTag]);
                        $updateData['tags'] = $leadTags;
                    }
                }
            }

            if (!empty($updateData)) {
                $updateData['id'] = $id;
                $updateData['updated_at'] = time();

                if (!isset($updateData['tags'])) {
                    $updateData['tags'] = false;
                }

                $crmHelper->updateLead([
                    $updateData
                ]);
            }
        }

        return $this;
    }

    /**
     * Set merchant status history.
     *
     * @param   Order $order
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function setMerchantStatusHistory(Order &$order)
    {
        $elementHistory      = [];
        $requestStatus       = $this->getRequestStatus();
        $savedElementHistory = (array) $order->params->find($this->getParamKey() . '.status_history', []);

        if (count($savedElementHistory)) {
            foreach ($savedElementHistory as $historyData) {
                $historyData = new JSON($historyData);
                $statusId    = $historyData->get('statusId');
                $timestamp   = $historyData->get('timestamp', 'int');

                if ($timestamp < 20) {
                    $statusId  = $timestamp;
                    $timestamp = 0;
                }

                $elementHistory[] = [
                    'statusId'  => $statusId,
                    'timestamp' => $timestamp
                ];
            }
        }

        $historyParamsData = (array) $order->params->get($this->getParamKey(), []);

        $elementHistory[] = [
            'statusId'  => $requestStatus,
            'timestamp' => time()
        ];

        $historyParamsData = array_merge($historyParamsData, ['status_history' => $elementHistory]);

        $order->params->set($this->getParamKey(), $historyParamsData);

        return $this;
    }

    /**
     * Setup order object.
     *
     * @param   Order  $order
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Setup status history from credit callback.
     *
     * @param   Order $order
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function setOrderStatusHistory(Order &$order)
    {
        if (!$order->isCredit() || $order->isLoanApproved()) {
            return $this;
        }

        $status = $this->hyper['helper']['status']->findById(Filter::int($this->getSiteStatusByRequest()));

        if ($status->id) {
            $history = (array) $order->status_history->getArrayCopy();

            array_push($history, [
                'statusId'  => $status->id,
                'timestamp' => time()
            ]);

            $order->set('status_history', new JSON($history));
            $order->set('status', $status->id);
        }

        return $this;
    }

    /**
     * Write credit error to log
     *
     * @param  Order  $order
     * @param  string $errorMsg
     * @param  string $responseBody
     *
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since  2.0
     */
    public function writeLogError(Order $order, string $errorMsg, $responseBody = '')
    {
        $content = new JSON([
            'id'     => $order->id,
            'status' => 'error',
            'error'  => $errorMsg
        ]);

        if ($responseBody) {
            $content->set('responseBody', $responseBody);
        }

        OrderHelper::writeLog($order->id, $this->getLogType(), $content->write());
    }

    /**
     * Check process lead status.
     *
     * @return      bool
     *
     * @throws      \JBZoo\Utils\Exception
     *
     * @since       2.0
     */
    protected function _checkProcessLeadStatus()
    {
        $statusList = $this->getStatusList();
        return ($statusList->get($this->getRequestStatus()));
    }

    /**
     * Clear phone.
     *
     * @param   string $phone
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _clearPhone($phone)
    {
        return str_replace(['+', '(', ')', '-', ' '], '', $phone);
    }

    /**
     * Get status message for crm note
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getCrmNoteStatusMessage()
    {
        return (string) $this->getStatusTitle();
    }

    /**
     * Update CRM lead on creating form
     *
     * @param   int $leadId
     *
     * @return  void
     *
     * @todo add note to lead if needed
     */
    protected function _firstUpdateCrmLead($leadId)
    {
        if (empty($leadId)) {
            return;
        }

        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $tag = $this->getConfig('crm_tag_default');
        if (!empty($tag)) {
            $leadTags = $crmHelper->getLeadTags($leadId);
            $leadTags[] = $tag;

            $crmHelper->updateLead([[
                'updated_at'  => time(),
                'id'          => $leadId,
                'tags'        => $leadTags
            ]]);
        }
    }

    /**
     * Get stream http.
     *
     * @return  \Joomla\CMS\Http\Http
     *
     * @since   2.0
     */
    protected function _getHttp()
    {
        return HttpFactory::getHttp([], 'stream');
    }

    /**
     * Get order.
     *
     * @return  Order
     *
     * @since   2.0
     */
    protected function _getOrder()
    {
        return $this->hyper['helper']['order']->findById($this->_config->get('table')->id, ['new' => true]);
    }

    /**
     * Get current API uri.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getUri()
    {
        return ($this->isDebug()) ? $this->_uriTest : $this->_uriFire;
    }

    /**
     * On process lead.
     *
     * @param   array $updateData
     *
     * @since   2.0
     */
    protected function _onProcessLead(&$updateData)
    {
    }
}
