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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Position;

/**
 * Class ElementCreditFinboxCredit
 */
class ElementCreditFinboxCredit extends ElementCredit
{
    const PARAM_KEY = 'finboxcredit';

    /**
     * Fire uri.
     *
     * @var     string
     */
    protected $_uriFire = 'https://go.fbox.me';

    /**
     * Test uri.
     *
     * @var     string
     */
    protected $_uriTest = 'https://preprod.test.fbox.me';

    /**
     * Verifies that callback request relates the site's order.
     *
     * @return  bool
     */
    public function checkOrder()
    {
        return true;
        return $this->hyper['input']->get('token') === $this->_getOrderToken();
    }

    /**
     * Get failed status list.
     *
     * @return  string[]
     */
    public function getFailedStatusList()
    {
        return [
            'cancel',
            'decline'
        ];
    }

    /**
     * Get log content.
     *
     * @return  string
     */
    public function getLogContent()
    {
        return $this->getRequestData()->toString();
    }

    /**
     * Get method key for order params.
     *
     * @return  string
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
     */
    public function getRedirectUrl(Order $order)
    {
        return $order->params->find($this->getParamKey() . '.form_url', '');
    }

    /**
     * Get request data.
     *
     * @return  Registry
     */
    public function getRequestData()
    {
        static $resultData;

        if (!$resultData) {
            $data = file_get_contents('php://input');
            $resultData = new Registry($data);
        }

        return $resultData;
    }

    /**
     * Get order id from webhook data.
     *
     * @return  string|int
     */
    public function getRequestOrderId()
    {
        return $this->hyper['input']->get('order_id', 0);
    }

    /**
     * Get status from calback request.
     *
     * @return  string
     */
    public function getRequestStatus()
    {
        return $this->getRequestData()->get('status', '');
    }

    /**
     * Get status map.
     *
     * @return  JSON
     */
    public function getStatusMap()
    {
        $statuses = [
            '[ACCEPTED]'    => 'accepted',
            '[SCORING]'     => 'scoring',
            '[CONTRACT]'    => 'contract',
            '[END]'         => 'end',
            '[DECLINE]'     => 'decline',
            '[CANCEL]'      => 'cancel'
        ];

        $map = new JSON();
        foreach ($statuses as $key => $status) {
            $map->set($key, [
                'id'    => $status,
                'alias' => $status,
                'label' => Text::_(implode('_', [
                    'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_STATUS',
                    strtoupper($status)
                ]))
            ]);
        }

        return $map;
    }

    /**
     * Get current status title.
     *
     * @return  string
     */
    public function getStatusTitle()
    {
        return Text::_(implode('_', [
            'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_STATUS',
            strtoupper($this->getRequestStatus())
        ]));
    }

    /**
     * Is the current status a success status?
     *
     * @return  bool
     */
    public function isSuccessRequestStatus()
    {
        return $this->getRequestStatus() === 'end';
    }

    /**
     * Get data array for order create request
     *
     * @param   Order $order
     *
     * @return  array
     *
     * @throws  \Exception
     */
    protected function _getOrderData(Order $order)
    {
        $orderUrl = $order->getAccountViewUrl(true);

        return [
            'orderId' => $order->getName(),
            'pointId' => $this->_getPointId(),
            'creditTypes' => ['classic'],
            'firstName' => $order->getBuyer(),
            'phone' => $this->isDebug() ? '9991112233' : $order->getBuyerPhone(clear: true),
            'goods' => $this->_getGoods($order),
            'generateForm' => true,
            'formSuccessUrl' => $orderUrl,
            'formCancelUrl' => $orderUrl,
            'callbackUrl' => $this->_getCallbackUrl($order)
        ];
    }

    /**
     * Element notify action.
     *
     * @return  bool
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

        try {
            $requestData = $this->_getOrderData($order);

            $response = $this->_getHttp()->post($this->_getUri() . '/broker/api/v2/orders/create', json_encode($requestData), [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->_getApiLogin() . ':' . $this->_getApiPassword())
            ]);
        } catch (\Exception $e) {
            $this->hyper['cms']->enqueueMessage(
                Text::_('HYPER_ELEMENT_CREDIT_FINBOXCREDIT_HTTP_CONNECT_ERROR'),
                'error'
            );

            $this->writeLogError($order, $e->getMessage());

            return false;
        }

        $body = new Registry($response->body);

        if ($response->code !== 200 || $body->get('accepted') !== 'true') {
            $errorMessage = $body->get('errorMessage') ?: $body->get('reason');
            $errorMessage = $errorMessage ?: Text::_('HYPER_ELEMENT_CREDIT_FINBOXCREDIT_HTTP_CONNECT_ERROR');
            $this->hyper['cms']->enqueueMessage(
                $errorMessage,
                'error'
            );

            $this->writeLogError($order, $response->code, $response->body);

            return false;
        }

        $order->params->set($this->getParamKey(), [
            'id' => $body->get('id'),
            'form_url' => $this->_encryptFormUrl($body->get('formUrl')),
            'status_history' => [[
                'statusId'  => 'accepted',
                'timestamp' => time()
            ]]
        ]);

        $table->save($order->getArray());

        $this->_firstUpdateCrmLead($order->getAmoLeadId());

        return true;
    }

    /**
     * Get API login.
     *
     * @return string
     */
    protected function _getApiLogin()
    {
        return trim($this->getConfig(($this->isDebug() ? 'api_login_debug' : 'api_login_prod'), ''));
    }

    /**
     * Get API password.
     *
     * @return string
     */
    protected function _getApiPassword()
    {
        return trim($this->getConfig(($this->isDebug() ? 'api_password_debug' : 'api_password_prod'), ''));
    }

    /**
     * Get callback url.
     *
     * @param   Order $order
     *
     * @return  string
     */
    protected function _getCallbackUrl(Order $order)
    {
        return $this->hyper['route']->build([
            'task'     => 'credit.callback',
            'method'   => $this->getIdentifier(),
            'order_id' => $order->id,
            'token'    => $this->_getOrderToken()
        ], true);
    }

    /**
     * Get array of order items.
     *
     * @param   Order $order
     *
     * @return  array
     *
     * @throws  \Exception
     */
    protected function _getGoods(Order $order)
    {
        $goods = [];

        $positions = $order->getPositions();
        foreach ($positions as $position) {
            $salePrice = $position->getSalePrice()->val();
            if ($salePrice === 0.0) {
                continue;
            }

            $goods[] = [
                'title' => $position->getName(),
                'groupId' => $this->_getGroupId($position),
                'price' => $salePrice,
                'type' => $position->isService() ? 'service' : 'product',
                'count' => $position->quantity
            ];
        }

        $deliveryData = $this->getDeliveryData($order);
        if ($deliveryData->get('isShipping')) {
            $deliveryPrice = $deliveryData->get('price');
            if ($deliveryPrice->val() >= 0) {
                $goods[] = [
                    'title' => $deliveryData->get('title'),
                    'price' => $deliveryPrice->val(),
                    'groupId' => 'OT11180',
                    'type' => 'service',
                    'count' => 1
                ];
            }
        }

        return $goods;
    }

    /**
     * Get FinBox dictionary group id.
     *
     * @param   Position $position
     *
     * @return  string
     */
    protected function _getGroupId(Position $position)
    {
        $groupId = 'VT10510'; // Компьютеры и моноблоки
        if ($position->isPart()) {
            $groupId = 'VT10490'; // Компьютерные аксессуары и комплектующие
        } elseif ($position->isService()) {
            $groupId = 'OT11180'; // Дополнительные услуги
        } else {
            $folder = $position->getFolder();
            if ($folder->getItemsType() === 'notebook') {
                $groupId = 'VT10540'; // Ноутбуки
            }
        }

        return $groupId;
    }

    /**
     * Get http.
     *
     * @return  \Joomla\CMS\Http\Http
     *
     * @throws  \RuntimeException
     */
    protected function _getHttp()
    {
        return HttpFactory::getHttp([], 'curl');
    }

    /**
     * Get order token.
     *
     * @return  string
     */
    protected function _getOrderToken()
    {
        return md5($this->_order->id . $this->_order->created_time . $this->_order->created_user_id);
    }

    /**
     * Get point id.
     *
     * @return string
     */
    protected function _getPointId()
    {
        return trim($this->getConfig(($this->isDebug() ? 'point_id_debug' : 'point_id_prod'), ''));
    }

    /**
     * Encrypt url to credit form.
     *
     * @param   string $formUrl
     *
     * @return  string
     *
     * @todo encryption (not needed now)
     */
    protected function _encryptFormUrl(string $formUrl)
    {
        return $formUrl;
    }
}
