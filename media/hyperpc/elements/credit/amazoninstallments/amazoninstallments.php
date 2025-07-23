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

use JBZoo\Data\JSON;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use HYPERPC\Helper\OrderHelper;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementCreditAmazonInstallments
 *
 * @since   2.0
 */
class ElementCreditAmazonInstallments extends ElementCredit
{
    const PARAM_KEY = 'amazoninstallments';
    const SUCCESS_STATUS_CODES = ['02', '14', '44'];
    const PAYMENT_ID_PARAM_NAME = 'fort_id';

    /**
     * Production uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriFire = 'https://checkout.payfort.com/FortAPI/paymentPage';

    /**
     * Sandbox uri.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uriTest = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this
            ->registerAction('request')
            ->registerAction('return');
    }

   /**
     * Handling a request for redirect to amazon payment page
     *
     * @return  void
     *
     * @since   2.0
     */
    public function actionRequest()
    {
        $orderId = $this->hyper['input']->get('order_id', 0);
        $token = $this->hyper['input']->get('token', '');

        $order = $this->hyper['helper']['order']->findById($orderId);
        if (!$order->id || $token !== $order->getToken()) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'error');
            $this->hyper['cms']->redirect($this->hyper['route']->build(['view' => 'profile_orders']));
        }

        $multiplier = 100; // multiplier may differ for different currencies
        $total = $order->getTotal()->multiply($multiplier);

        $language = 'en';
        if (strpos($this->hyper['cms']->getLanguage()->getTag(), 'ar') === 0) {
            $language = 'ar';
        }

        $requestParams = [
            'command'               => 'PURCHASE',
            'language'              => $language,
            'merchant_identifier'   => $this->getConfig('merchant_identifier'),
            'access_code'           => $this->getConfig('access_code'),
            'merchant_reference'    => $order->id,
            'amount'                => $total->val(),
            'currency'              => $this->hyper['helper']['money']->getCurrencyIsoCode($total),
            'customer_email'        => $order->getBuyerEmail(),
            'order_description'     => Text::sprintf('HYPER_ELEMENT_CREDIT_AMAZONINSTALLMENTS_ORDER_DESCRIPTION', $order->id, Uri::root()),
            'installments'          => 'STANDALONE',
            'return_url'            => $this->getReturnUrl()
        ];

        $signature = $this->_calculateRequestSignature($requestParams);
        $requestParams['signature'] = $signature;

        $apiUrl = $this->_getUri();

        echo "<html xmlns='https://www.w3.org/1999/xhtml'>\n<head></head>\n<body>\n";
        echo "<form action='$apiUrl' method='post' name='frm'>\n";
        foreach ($requestParams as $name => $value) {
            echo "\t<input type='hidden' name='".htmlentities($name)."' value='".htmlentities($value)."'>\n";
        }
        echo "\t<script type='text/javascript'>\n";
        echo "\t\tdocument.frm.submit();\n";
        echo "\t</script>\n";
        echo "</form>\n</body>\n</html>";
    }

    /**
     * Handling a return request
     *
     * @return  void
     *
     * @since   2.0
     */
    public function actionReturn()
    {
        $orderId = $this->getRequestOrderId();

        /** @var Order $order */
        $order = $this->hyper['helper']['order']->findById($orderId);
        if (!$order->id) {
            $this->writeLogError($order, Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'Order id: ' . $orderId);
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'error');
            $this->hyper['cms']->redirect($this->hyper['route']->build(['view' => 'profile_orders']));
        }

        $responseData = $this->getRequestData();

        if (!count($responseData)) {
            $this->hyper['cms']->redirect($order->getAccountViewUrl());
        }

        if (!$this->isResponseValid($responseData->getArrayCopy())) {
            $errorMessage = 'Response signature failed';
            $this->writeLogError($order, $errorMessage, $this->getLogContent());
            $this->hyper['cms']->enqueueMessage($errorMessage, 'error');
            $this->hyper['cms']->redirect($order->getAccountViewUrl());
        }

        $messageType = 'error';
        if ($this->isSuccessRequestStatus()) {
            $messageType = 'message';

            $order->params->set($this->getParamKey(), [
                static::PAYMENT_ID_PARAM_NAME => $responseData->get(static::PAYMENT_ID_PARAM_NAME)
            ]);

            $this->setMerchantStatusHistory($order);

            $this->hyper['helper']['order']->getTable()->save($order->getArray());

            $this->processLead($order->getAmoLeadId());
        }

        OrderHelper::writeLog($order->id, $this->getLogType(), $this->getLogContent());

        $this->hyper['cms']->enqueueMessage($responseData->get('response_message'), $messageType);
        $this->hyper['cms']->redirect($order->getAccountViewUrl());
    }

    /**
     * @inheritdoc
     */
    public function checkOrder()
    {
        $savedOrderReference = $this->_order->params->find($this->getParamKey() . '.' . static::PAYMENT_ID_PARAM_NAME);
        return ($this->getRequestData()->get(static::PAYMENT_ID_PARAM_NAME) === $savedOrderReference);
    }

    /**
     * @inheritdoc
     */
    public function getFailedStatusList()
    {
        $statuses = array_keys($this->getStatusMap()->getArrayCopy());
        return array_filter($statuses, function ($status) {
            return !in_array($status, static::SUCCESS_STATUS_CODES, true);
        });
    }

    /**
     * @inheritdoc
     */
    public function getLogContent()
    {
        return $this->getRequestData()->write();
    }

    /**
     * @inheritdoc
     */
    public function getParamKey()
    {
        return static::PARAM_KEY;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrl(Order $order)
    {
        return $this->hyper['route']->build([
            'tmpl'       => 'raw',
            'action'     => 'request',
            'task'       => 'elements.call',
            'group'      => $this->getGroup(),
            'identifier' => $this->getIdentifier(),
            'order_id'   => $order->id,
            'token'      => $order->getToken()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getRequestData()
    {
        static $resultData;

        if (!$resultData) {
            $paymentResponseData = filter_input_array(INPUT_POST);
            if (empty($paymentResponseData)) {
                $paymentResponseData = filter_input_array(INPUT_GET);
                foreach (['tmpl', 'action', 'task', 'group', 'identifier'] as $param) {
                    unset($paymentResponseData[$param]);
                }
            }

            $resultData = new JSON($paymentResponseData);
        }

        return $resultData;
    }

    /**
     * @inheritdoc
     *
     * @return  int
     */
    public function getRequestOrderId()
    {
        return $this->getRequestData()->get('merchant_reference', 0, 'int');
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
        return $this->getRequestData()->get('status');
    }

    /**
     * Get url for return from amazon payment service
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getReturnUrl(): string
    {
        return $this->hyper['route']->build([
            'tmpl'       => 'raw',
            'action'     => 'return',
            'task'       => 'elements.call',
            'group'      => $this->getGroup(),
            'identifier' => $this->getIdentifier()
        ], true);
    }

    /**
     * @inheritdoc
     */
    public function getStatusList()
    {
        static $instances = [];

        if (!array_key_exists($this->getType(), $instances)) {
            $statusList = new JSON([]);
            $statusMap  = $this->getStatusMap();

            $successStatusData = new Registry($this->getConfig('status_success'));

            foreach ($statusMap as $statusId => $title) {
                if (!in_array($statusId, static::SUCCESS_STATUS_CODES)) {
                    continue;
                }

                $statusList->set($statusId, [
                    'id'          => $statusId,
                    'alias'       => $statusId,
                    'label'       => $title,
                    'pipeline'    => $successStatusData->get('pipeline'),
                    'site_status' => $successStatusData->get('site_status')
                ]);
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
        static $statusMap;

        if (!isset($statusMap)) {
            $map = [];
            $filePath = $this->_getStatusMapPath();
            if (File::exists($filePath)) {
                /** @noinspection PhpIncludeInspection */
                $map = require_once $filePath;
            }

            $statusMap = new JSON($map);
        }

        return $statusMap;
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
        $status = $this->getRequestStatus();
        $statusMap = $this->getStatusMap();

        return $status ? $statusMap->get($status, '') : '';
    }

    /**
     * @inheritdoc
     */
    public function getViewUrl(Order $order)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function isAvailableInOrder(Order $order): bool
    {
        $available = parent::isAvailableInOrder($order);
        if ($available) {
            $enabledOrderStatuses = $this->getConfig('render_on_statuses', [], 'arr');

            return in_array($order->status, $enabledOrderStatuses);
        }

        return $available;
    }

    /**
     * Checks response signature
     *
     * @param   array $paymentResponseData
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function isResponseValid(array $paymentResponseData): bool
    {
        if (!isset($paymentResponseData['signature'])) {
            return false;
        }

        $signature = $paymentResponseData['signature'];
        unset($paymentResponseData['signature']);

        if ($signature !== $this->_calculateResponseSignature($paymentResponseData)) {
            return false;
        }

        return true;
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
        $status = $this->getRequestStatus();

        return in_array($status, static::SUCCESS_STATUS_CODES, true);
    }

    /**
     * @inheritdoc
     */
    public function notify()
    {
        $order = $this->_getOrder();

        if (!$order->id) {
            return false;
        }

        return true;
    }

    /**
     * Get request signature
     *
     * @param  array $requestParams
     *
     * @return string
     */
    protected function _calculateRequestSignature(array $requestParams): string
    {
        $shaRequestPhrase = $this->getConfig('sha_request_phrase');

        return $this->_calculateSignature($requestParams, $shaRequestPhrase);
    }

    /**
     * Get response signature
     *
     * @param  array $responseParams
     *
     * @return string
     */
    protected function _calculateResponseSignature(array $responseParams): string
    {
        $shaRequestPhrase = $this->getConfig('sha_response_phrase');

        return $this->_calculateSignature($responseParams, $shaRequestPhrase);
    }

    /**
     * Get signature
     *
     * @param  array $requestParams
     * @param  string $passPhrase
     *
     * @return string
     */
    protected function _calculateSignature(array $requestParams, string $passPhrase): string
    {
        $shaString = '';

        ksort($requestParams);
        foreach ($requestParams as $key => $value) {
            $shaString .= "$key=$value";
        }

        $shaString = $passPhrase . $shaString . $passPhrase;
        $signature = hash($this->getConfig('sha_type'), $shaString);

        return $signature;
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
        return $this->hyper['path']->get('elements:' . $this->_group . '/' . $this->_type . '/config/status_map.php');
    }

    /**
     * @inheritdoc
     */
    protected function _onProcessLead(&$updateData)
    {
        $leadTags = $this->hyper['helper']['crm']->getLeadTags($updateData['id']);

        $tags = [
            $this->getTypeName(),
            Text::sprintf(
                'HYPER_ELEMENT_CREDIT_AMAZONINSTALLMENTS_TAG_PERIOD',
                $this->getRequestData()->get('number_of_installments', '')
            )
        ];

        foreach ($tags as $tag) {
            if (!in_array($tag, $leadTags)) {
                $leadTags[] = $tag;
            }
        }

        $updateData['tags'] = $leadTags;
    }
}
