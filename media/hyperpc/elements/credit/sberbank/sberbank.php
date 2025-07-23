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
use Joomla\CMS\Mail\Mail;
use Cake\Utility\Inflector;
use HYPERPC\Joomla\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use JBZoo\SimpleTypes\Type\Money;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;
use Joomla\CMS\Mail\MailerFactoryInterface;
use HYPERPC\Object\Order\PositionDataCollection;

/**
 * Class ElementCreditSberbank
 *
 * @since   2.0
 */
class ElementCreditSberbank extends ElementCredit
{

    const PARAM_KEY              = 'sberbank';
    const PARAM_ORDER_KEY        = 'order_id';
    const SUCCESS_REQUEST_STATUS = 'deposited_1';

    /**
     * @var     string
     *
     * @since   2.0
     */
    protected $_orderPrefix = 'Р';

    /**
     * Fire uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriFire = 'https://securepayments.sberbank.ru';

    /**
     * Test uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriTest = 'https://3dsec.sberbank.ru';

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
        $savedOrderId = $this->_order->params->find($this->getParamKey() . '.' . self::PARAM_ORDER_KEY);
        return ($this->getRequestData()->get('mdOrder') === $savedOrderId);
    }

    /**
     * Get cabinet link.
     *
     * @return    string|null
     *
     * @since    2.0
     */
    public function getCabinetLink()
    {
        $nbNumber = $this->getRequestData()->get('mdOrder');
        return ($nbNumber) ? 'https://securepayments.sberbank.ru/mportal3/admin/orders/' . $nbNumber : null;
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
        $path = $this->getPath('config/debug_data.php');
        if ($path) {
            /** @noinspection PhpIncludeInspection */
            $data = require_once $path;
            return (array) $data;
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
            'deposited_0',
            'reversed',
            'declinedByTimeout'
        ];
    }

    /**
     * Get http.
     *
     * @return  \Joomla\CMS\Http\Http
     *
     * @since   2.0
     */
    protected function _getHttp()
    {
        return HttpFactory::getHttp([], 'curl');
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
        return (new JSON($_GET))->write();
    }

    /**
     * Get request operation status.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOperationStatus()
    {
        return $this->getRequestData()->get('status', '');
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
     * Get API password.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPassword()
    {
        return $this->hyper['params']->get('sberbank_password', '');
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

        return $this->_getUri() . '/sbercredit/rbs-common.html?mdOrder=' . $orderKey;
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
            $data = (!$this->isDebug()) ? $this->hyper['input']->getArray() : $this->getDebugData();
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
        $orderId = str_replace($this->_orderPrefix, '', $this->getRequestData()->get('orderNumber', '0'));
        return (int) $orderId;
    }

    /**
     * Get request status.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getRequestStatus()
    {
        $status = $this->getRequestData()->get('operation', '');
        if ($status=== 'deposited') {
            $status .= '_' . $this->getOperationStatus();
        }

        return $status;
    }

    /**
     * Get status list.
     *
     * @return  \JBZoo\Data\JSON
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getStatusList()
    {
        static $instances = [];

        if (key_exists($this->getType(), $instances)) {
            return $instances[$this->getType()];
        }

        $statusList = new JSON([]);
        $statusMap  = $this->getStatusMap();

        foreach ($this->_config->getArrayCopy() as $paramKey => $statusData) {
            if (preg_match('/^status/', $paramKey)) {
                $statusData = new JSON($statusData);
                list (, $paramId) = explode('_', $paramKey, 2);
                preg_match('/(.+)(_[0|1])$/', $paramId, $segments);

                $mapKey = Inflector::variable($paramId);
                if (isset($segments[2])) {
                    $mapKey = Inflector::variable($segments[1]) . $segments[2];
                }
                if (is_array($statusMap->get($mapKey))) {
                    $statusList->set($statusMap->find($mapKey . '.alias'), array_merge(
                        $statusMap->get($mapKey),
                        [
                            'pipeline'    => $statusData->get('pipeline'),
                            'site_status' => $statusData->get('site_status')
                        ]
                    ));
                }
            }
        }

        $instances[$this->getType()] = $statusList;

        return $statusList;
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
        static $instances = [];

        if (key_exists($this->getType(), $instances)) {
            return $instances[$this->getType()];
        }

        $statuses = [
            'created',
            'approved',
            'deposited', // for B/C only
            'deposited_0',
            'deposited_1',
            'reversed',
            'refunded',
            'declinedByTimeout',
            'subscriptionCreated'
        ];

        $map = new JSON();
        foreach ($statuses as $status) {
            $langStatusKey = strtoupper(Inflector::underscore($status));
            $map->set($status, [
                'id'    => $status,
                'alias' => $status,
                'label' => Text::_(implode('_', [
                    'HYPER_ELEMENT_CREDIT',
                    strtoupper($this->getType()),
                    'STATUS',
                    $langStatusKey
                ]))
            ]);
        }

        $instances[$this->getType()] = $map;

        return $map;
    }

    /**
     * Get status title.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getStatusTitle()
    {
        return Text::_(implode('_', [
            'HYPER_ELEMENT_CREDIT',
            strtoupper($this->getType()),
            'STATUS',
            strtoupper(Inflector::underscore($this->getRequestStatus()))
        ]));
    }

    /**
     * Get API username.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUsername()
    {
        return $this->hyper['params']->get('sberbank_username', '');
    }

    /**
     * Check current success merchant status.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isSuccessRequestStatus()
    {
        return $this->getRequestStatus() === self::SUCCESS_REQUEST_STATUS;
    }

    /**
     * Element notify action.
     *
     * @return  bool
     *
     * @throws  \Exception
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
        $loanType = $this->getConfig('loan_type_logic', 'credit');

        $data = [
            'userName'      => $this->getUsername(),
            'password'      => $this->getPassword(),
            'amount'        => (int) $order->getTotal()->multiply(100)->val(),
            'currency'      => 643,
            'language'      => 'ru',
            'orderNumber'   => $this->_orderPrefix . $order->getName(),
            'returnUrl'     => $successUrl,
            'failUrl'       => $successUrl,
            'jsonParams'    => json_encode([
                'email' => $order->getBuyerEmail(),
                'phone' => $this->_clearPhone($order->getBuyerPhone())
            ]),
            'orderBundle'   => (new JSON([
                'cartItems' => [
                    'items' => $this->_getOrderItems($order),
                ],
                'installments' => [
                    'productID'   => '10',
                    'productType' => strtoupper($loanType)
                ]
            ]))->write()
        ];

        $client = $this->_getHttp();
        try {
            $response = $client->post($this->_getUri() . '/sbercredit/register.do', $data);
            $body = new JSON($response->body);

            if ($body->get('formUrl')) {
                $order = $this->_getOrder();

                $order->params->set($this->getParamKey(), [
                    self::PARAM_ORDER_KEY => $body->get('orderId'),
                    'status_history' => [[
                        'statusId'  => 'created',
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

                $errorMsg = Text::sprintf('HYPER_ELEMENT_CREDIT_SBERBANK_HTTP_ERROR', $body->get('errorMessage'));
                $this->hyper['cms']->enqueueMessage($errorMsg, 'error');
                $this->writeLogError($order, $errorMsg, $information);
            }
        } catch (\Exception $e) {
            $this->hyper['cms']->enqueueMessage(
                Text::_('HYPER_ELEMENT_CREDIT_SBERBANK_HTTP_CONNECT_ERROR'),
                'error'
            );

            $this->writeLogError($order, $e->getMessage());
        }

        return false;
    }

    /**
     * Setup status history from credit callback.
     *
     * @param   Order $order
     *
     * @return  ElementCredit
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function setOrderStatusHistory(Order &$order)
    {
        if ($this->isSuccessRequestStatus()) {
            $recipients = (array) Str::parseLines($this->getConfig('mail_recipient'));
            if ($this->getConfig('send_mail', true) && count($recipients)) {
                /** @var Mail $mailer */
                $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();

                $mailer
                    ->setBody($this->render(['layout' => 'mail']))
                    ->setSubject($this->getConfig('mail_subject', 'Callback sberbank'))
                    ->addRecipient($recipients)
                    ->Send();
            }
        }

        return parent::setOrderStatusHistory($order);
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
        return !empty($statusList->get($this->getRequestStatus()));
    }

    /**
     * Get order items.
     *
     * @param   Order $order
     *
     * @return  array
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getOrderItems(Order $order)
    {
        $i = 0;
        $return = [];

        $positionsData = PositionDataCollection::create($order->positions->getArrayCopy());
        foreach ($positionsData as $itemKey => $positionData) {
            $price = (int) ($positionData->price * (1 - $positionData->discount / 100) * 100);

            if ($price === 0) {
                continue; // Don't send free items
            }

            $return[] = [
                'itemCode'    => $itemKey,
                'positionId'  => ++$i,
                'name'        => $positionData->name,
                'itemDetails' => (object) [],
                'itemPrice'   => $price,
                'itemAmount'  => $price * $positionData->quantity,
                'quantity'    => ['value' => $positionData->quantity, 'measure' => 'шт.']
            ];
        }

        $deliveryData = $this->getDeliveryData($order);

        if ($deliveryData->get('isShipping')) {
            /** @var Money $deliveryPrice */
            $deliveryPrice = $deliveryData->get('price');

            if ($deliveryPrice->compare(0, '>')) {
                $deliveryPrice->multiply(100);

                $return[] = [
                    'itemCode'    => 'delivery',
                    'positionId'  => $i + 1,
                    'name'        => $deliveryData->get('title'),
                    'itemDetails' => (object) [],
                    'itemPrice'   => (int) $deliveryPrice->val(),
                    'itemAmount'  => (int) $deliveryPrice->val(),
                    'quantity'    => ['value' => 1, 'measure' => 'шт.']
                ];
            }
        }

        return $return;
    }
}
