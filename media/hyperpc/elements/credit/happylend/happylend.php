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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementCreditHappylend
 *
 * @since   2.0
 */
class ElementCreditHappylend extends ElementCredit
{

    const PARAM_KEY = '7seconds';

    /**
     * Fire uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriFire = 'https://api.7seconds.ru';

    /**
     * Test uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriTest = 'https://test-api.mandarin.io';

    /**
     * Check request order.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function checkOrder()
    {
        $savedOrderAppId = $this->_order->params->find($this->getParamKey() . '.application_id');
        return ($this->getRequestData()->get('ApplicationID') === $savedOrderAppId);
    }

    /**
     * Get failed status list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFailedStatusList()
    {
        return [
            'CreditRejectedByFinOrgs',
            'CustomerCallFailed',
            'CustCancelBeforeAppr',
            'CustCancelAfterAppr'
        ];
    }

    /**
     * Get http instance.
     *
     * @return  \Joomla\CMS\Http\Http
     *
     * @since   2.0
     */
    protected function _getHttp()
    {
        return HttpFactory::getHttp([], 'curl');;
    }

    /**
     * Get log content.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getLogContent()
    {
        $input = file_get_contents('php://input');
        return (new JSON($input))->write();
    }

    /**
     * Get order param element key.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getParamKey()
    {
        return static::PARAM_KEY;
    }

    /**
     * Get service redirect url.
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getRedirectUrl(Order $order)
    {
        $orderKey = $order->params->find($this->getParamKey() . '.application_id');
        if (empty($orderKey)) {
            return '';
        }

        return $this->_getUri() . '/home/uforms?applicationId=' . $orderKey;
    }

    /**
     * Get request data.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getRequestData()
    {
        static $resultData;

        if (!$resultData) {
            $data = (!$this->isDebug()) ? file_get_contents('php://input') : $this->getDebugData();
            $resultData = new JSON($data);
        }

        return $resultData;
    }

    /**
     * Get request order id.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getRequestOrderId()
    {
        $request = $this->getRequestData();
        return abs($request->get('OrderID', 0, 'int'));
    }

    /**
     * Get request status.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function getRequestStatus()
    {
        return $this->getRequestData()->get('StatusID');
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
        static $return;

        if (!$return) {
            $map  = [];
            $file = $this->hyper['path']->get('elements:' . $this->_group . '/happylend/config/status_map.php');
            if ($file) {
                /** @noinspection PhpIncludeInspection */
                $statusMap = require $file;
                foreach ((array) $statusMap as $sysId => $sysAlias) {
                    $langStatusKey = strtoupper(Inflector::underscore($sysAlias));
                    $map[$sysId] = [
                        'id'    => $sysId,
                        'alias' => $sysAlias,
                        'label' => Text::_(implode('_', [
                            'HYPER_ELEMENT_CREDIT_HAPPYLEND_STATUS',
                            $langStatusKey
                        ]))
                    ];
                }
            }

            $return = new JSON($map);
        }

        return $return;
    }

    /**
     * Get status title.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getStatusTitle()
    {
        static $title;
        if (!$title) {
            $title = $this->getStatusByAlias($this->getRequestStatus())->get('label');
        }

        return $title;
    }

    /**
     * Check current success merchant status.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function isSuccessRequestStatus()
    {
        return in_array($this->getRequestStatus(), ['CreditApproved', 'FundsPaidToMerchant']);
    }

    /**
     * Checks if order need to be updated by callback
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function needUpdateOrder()
    {
        $requestStatus = $this->getRequestStatus();
        $statusHistory = $this->_order->params->find($this->getParamKey() . '.status_history', [], 'arr');
        foreach ($statusHistory as $statusData) {
            $statusId = $statusData['statusId'] ?? null;
            if ($statusId === $requestStatus) {
                return false;
            }
        }

        return true;
    }

    /**
     * Element notify action.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function notify()
    {
        $table = $this->_config->get('table');
        /** @var Order $order */
        $order = $table->toEntity();
        if (!$order->id) {
            return false;
        }

        if (!empty($this->getRedirectUrl($order))) {
            return true;
        }

        $amount = (int) $order->getTotal()->multiply(100)->val();
        $successUrl = $this->getUrlSuccess($order);

        $data = [
            'InitialFeeInStore'         => 3,
            'DeliveryCostUse'           => 1,
            'DeliveryCost'              => 0,
            'ClientCanChangeInitialFee' => true,
            'CallBackURLsuccess'        => $successUrl,
            'CallBackURLfail'           => $successUrl,
            'AmountWithDiscount'        => $amount,
            'Amount'                    => $amount,
            'Email'                     => $order->getBuyerEmail(),
            'Cart'                      => $this->_getOrderItems($order),
            'ApiKey'                    => $this->getConfig('api_key', ''),
            'OrderID'                   => $this->_getContextOrderId($order),
            'Phone'                     => $this->_clearPhone($order->getBuyerPhone())
        ];

        $client = $this->_getHttp();
        try {
            $response = $client->post($this->_getUri() . '/api/merch/order', (new JSON($data))->write(), [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ], 15);

            $body = new JSON($response->body);

            if ($body->get('Result') === true) {
                $order = $this->_getOrder();

                $order->params->set($this->getParamKey(), [
                    'application_id' => $body->get('application_id'),
                    'status_history' => [[
                        'statusId'  => 'BasketReceived',
                        'timestamp' => time()
                    ]]
                ]);

                $table->save($order->getArray());

                $this->_firstUpdateCrmLead($order->getAmoLeadId());

                return true;
            } else {
                $information = [
                    'request'  => $data,
                    'response' => $response->body
                ];

                $errors = (array) $body->get('Errors');
                $errorMsg = Text::_('HYPER_ELEMENT_CREDIT_HAPPYLEND_ERROR');

                if (count($errors)) {
                    $errorDescriptions = array_map(function ($errorObj) {
                        return $errorObj['ErrorDescription'] ?? $errorObj['ErrorCode'] ?? 'Unknown error';
                    }, $errors);

                    $errorMsg = Text::sprintf('HYPER_ELEMENT_CREDIT_HAPPYLEND_HTTP_ERROR', implode('<br />', $errorDescriptions));
                }

                $this->hyper['cms']->enqueueMessage($errorMsg, 'error');
                $this->writeLogError($order, $errorMsg, $information);
            }
        } catch (\Exception $e) {
            $this->hyper['cms']->enqueueMessage(
                Text::_('HYPER_ELEMENT_CREDIT_HAPPYLEND_HTTP_CONNECT_ERROR'),
                'error'
            );

            $this->writeLogError($order, $e->getMessage());
        }

        return false;
    }

    /**
     * Get current context order id.
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getContextOrderId(Order $order)
    {
        return implode('-', [
            Str::up(substr($this->hyper->getContext(), 0, 1)),
            $order->id
        ]);
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
        $status = (string) $this->getStatusTitle();

        $chosenBank = $this->getRequestData()->get('FinOrg');
        if (!empty($chosenBank)) {
            $status = $chosenBank . ': ' . $status;
        }

        return $status;
    }

    /**
     * Get order items.
     *
     * @param   Order $order
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getOrderItems(Order $order)
    {
        $return = [];

        foreach ($order->getPositions() as $itemKey => $item) {
            $salePrice = $item->getSalePrice();
            if ((int) $salePrice->val() === 0) {
                continue; // Don't send free items
            }

            // the price has a discount price, because happyland gives an error otherwise, because the price without a discount takes priority over the price with a discount
            //$listPrice = $item->getListPrice();

            $return[] = [
                'ProductID'         => $itemKey,
                'Price'             => (int) ($salePrice->val() * 100),
                'PriceWithDiscount' => (int) ($salePrice->val() * 100),
                'Quantity'          => $item->quantity,
                'Category'          => [$item->getFolder()->title],
                'ProductName'       => $item->getName()
            ];
        }

        $deliveryData = $this->getDeliveryData($order);

        if ($deliveryData->get('isShipping')) {
            $deliveryPrice = $deliveryData->get('price');

            if ($deliveryPrice->val() < 0) {
                $deliveryPrice->set(0);
            }

            $deliveryPrice->multiply(100);

            $return[] = [
                'ProductID'         => 'delivery-' . $order->id,
                'ProductName'       => $deliveryData->get('title'),
                'Price'             => (int) $deliveryPrice->val(),
                'PriceWithDiscount' => (int) $deliveryPrice->val(),
                'Quantity'          => 1,
                'Category'          => [Text::_('COM_HYPERPC_DELIVERY')]
            ];
        }

        return $return;
    }
}
