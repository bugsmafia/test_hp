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

use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Object\Order\PositionDataCollection;

/**
 * Class ElementCreditTinkoff
 *
 * @since   2.0
 */
class ElementCreditTinkoff extends ElementCredit
{
    const PARAM_KEY         = 'tinkoff';
    const PARAM_ORDER_KEY   = 'order_id';

    /**
     * @var     string
     *
     * @since   2.0
     */
    protected $_orderPrefix = 'К';

    /**
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_responseBody;

    /**
     * Fire uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriFire = 'https://forma.tinkoff.ru/api/partners/v2/orders/create';

    /**
     * Test uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriTest = 'https://forma.tinkoff.ru/api/partners/v2/orders/create-demo';

    /**
     * Check request order.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function checkOrder()
    {
        return ($this->_order->id === $this->getRequestData()->get('id', 0, 'int'));
    }

    /**
     * Get debug data.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getDebugData()
    {
        $path = $this->_getDebugDataPath();
        if ($path) {
            $data = file_get_contents($path);
            return (new JSON($data))->getArrayCopy();
        }

        return [];
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
            'rejected',
            'canceled'
        ];
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
        $orderKey = $order->params->find($this->getParamKey() . '.' . self::PARAM_ORDER_KEY);
        if (empty($orderKey)) {
            return '';
        }

        return 'https://forma.tinkoff.ru/online/' . $orderKey;
    }

    /**
     * Get request data.
     *
     * @return  \JBZoo\Data\JSON
     *
     * @since   2.0
     */
    public function getRequestData()
    {
        static $resultData;

        if (!$resultData) {
            $data = file_get_contents('php://input');
            $resultData = new JSON($data);
        }

        return $resultData;
    }

    /**
     * Get request order id.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getRequestOrderId()
    {
        $orderId = str_replace($this->_orderPrefix, '', $this->getRequestData()->get('id'));
        return $orderId;
    }

    /**
     * Get request status.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getRequestStatus()
    {
        return $this->getRequestData()->get('status');
    }

    /**
     * Get status list.
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
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
                    $sysId = strtolower($sysId);
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
            $file = $this->_getStatusMapPath();
            if ($file) {
                /** @noinspection PhpIncludeInspection */
                $statusMap = require $file;
                foreach ((array) $statusMap as $sysId => $sysAlias) {
                    $langStatusKey = strtoupper(Inflector::underscore($sysAlias));
                    $map[$sysId] = [
                        'id'    => $sysId,
                        'alias' => $sysAlias,
                        'label' => Text::_(implode('_', [
                            'HYPER_ELEMENT_CREDIT_TINKOFF_STATUS',
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
        return in_array($this->getRequestStatus(), ['signed', 'issued']);
    }

    /**
     * Element notify action.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
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

        $successUrl = $this->getUrlSuccess($order);

        $data = [
            'shopId'        => $this->getConfig('shop_id'),
            'showcaseId'    => $this->getConfig('showcase_id'),
            'sum'           => $order->getTotal()->val(),
            'orderNumber'   => $this->_orderPrefix . $order->getName(),
            'failURL'       => $successUrl,
            'successURL'    => $successUrl,
            'items'         => $this->_getOrderItems($order),
            'values'        => [
                'contact' => [
                    'fio'           => $order->getBuyer(),
                    'email'         => $order->getBuyerEmail(),
                    'mobilePhone'   => str_replace('+7', '', $order->getBuyerPhone())
                ]
            ]
        ];

        $promoCode = trim($this->getConfig('promo_code'));
        if (!empty($promoCode)) {
            $data['promoCode'] = $promoCode;
        }

        $client = $this->_getHttp();
        try {
            $response = $client->post($this->_getUri(), (new JSON($data))->write(), [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ]);

            $this->_responseBody = new JSON($response->body);

            if ($this->_responseBody->get('link')) {
                $order = $this->_getOrder();

                $order->params->set($this->getParamKey(), [
                    self::PARAM_ORDER_KEY => $this->_responseBody->get('id'),
                    'status_history' => [[
                        'statusId'  => 'new',
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

                if (count($this->_responseBody->get('errors'))) {
                    foreach ($this->_responseBody->get('errors') as $errorMsg) {
                        $this->hyper['cms']->enqueueMessage($errorMsg, 'error');
                        $this->writeLogError($order, $errorMsg, $information);
                        return false;
                    }
                }

                $errorMsg = Text::_('HYPER_ELEMENT_CREDIT_TINKOFF_HTTP_CONNECT_ERROR');
                $this->hyper['cms']->enqueueMessage($errorMsg, 'error');
                $this->writeLogError($order, $errorMsg, $information);
            }
        } catch (\Exception $e) {
            $this->hyper['cms']->enqueueMessage(
                Text::_('HYPER_ELEMENT_CREDIT_TINKOFF_HTTP_CONNECT_ERROR'),
                'error'
            );

            $this->writeLogError($order, $e->getMessage());
        }

        return false;
    }

    /**
     * Get order items.
     *
     * @param   Order  $order
     *
     * @return  array
     *
     * @throws  Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getOrderItems(Order $order)
    {
        $return = [];

        $positionsData = PositionDataCollection::create($order->positions->getArrayCopy());
        foreach ($positionsData as $positionData) {
            $price = $positionData->price * (1 - $positionData->discount / 100);

            if ((int) $price === 0) {
                continue; // Don't send free items
            }

            $return[] = [
                'name'      => $positionData->name,
                'quantity'  => $positionData->quantity,
                'price'     => $price
            ];
        }

        $deliveryData = $this->getDeliveryData($order);

        if ($deliveryData->get('isShipping')) {
            $deliveryPrice = $deliveryData->get('price');
            if ($deliveryPrice->val() > 0) {
                $return[] = [
                    'quantity'  => 1,
                    'price'     => $deliveryPrice->val(),
                    'name'      => $deliveryData->get('title')
                ];
            }
        }

        return $return;
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
        if (in_array($this->getRequestStatus(), ['rejected', 'approved', 'signed'])) {
            $chosenBank = $this->getRequestData()->get('chosen_bank');
            if (!empty($chosenBank)) {
                $status = $chosenBank . ': ' . $status;
            }
        }

        return $status;
    }

    /**
     * Get debug data file path
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getDebugDataPath()
    {
        return $this->hyper['path']->get('elements:' . $this->_group . '/' . $this->_type . '/config/debug_data.json');
    }

    /**
     * Get status map file path
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getStatusMapPath()
    {
        return $this->hyper['path']->get('elements:' . $this->_group . '/tinkoff/config/status_map.php');
    }
}
