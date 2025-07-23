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

use JBZoo\Data\JSON;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Helper\OrderHelper;
use HYPERPC\Elements\ElementCredit;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Controller\ControllerForm;

/**
 * Class HyperPcControllerOrder
 *
 * @since   2.0
 */
class HyperPcControllerCredit extends ControllerForm
{

    /**
     * Process merchant callback.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function callback()
    {
        $element = $this->_getElementFromCallback();

        if ($element === null) {
            $logMsg = json_encode([
                'error' => 'Credit element type not found',
                'query' => $_GET
            ], JSON_PRETTY_PRINT);
            OrderHelper::writeLog(0, 'credit_callback', $logMsg);

            $this->_callbackClose();
        }

        $orderId = $element->getRequestOrderId();

        OrderHelper::writeLog($orderId, $element->getLogType(), $element->getLogContent());

        /** @var Order $order */
        $order = $this->hyper['helper']['order']->findById($orderId);
        if (!$order->id) {
            $logMsg = json_encode([
                'error' => "Order {$orderId} not found",
                'query' => $_GET
            ], JSON_PRETTY_PRINT);
            OrderHelper::writeLog($orderId, $element->getLogType(), $logMsg);

            $this->_callbackClose($element);
        }

        $element->setOrder($order);

        if (!$element->checkOrder()) {
            $logMsg = json_encode([
                'error' => 'Can\'t match order number',
                'query' => $_GET
            ], JSON_PRETTY_PRINT);
            OrderHelper::writeLog($orderId, $element->getLogType(), $logMsg);

            $this->_callbackClose($element);
        }

        if ($element->needUpdateOrder()) {
            try {
                $element
                    ->setOrderStatusHistory($order)
                    ->setMerchantStatusHistory($order)
                    ->processLead($this->_findLeadIdByOrder($order))
                    ->checkApprove($order);

                $this->getModel()->save($order->getArray());
            } catch (\Throwable $th) {
                $logMsg = json_encode([
                    'error' => 'Element threw an error: ' . $th->getMessage()
                ]);
                OrderHelper::writeLog($orderId, $element->getLogType(), $logMsg);
            }
        } else {
            $logMsg = json_encode([
                'notice' => 'No need to update order'
            ]);
            OrderHelper::writeLog($orderId, $element->getLogType(), $logMsg);
        }

        $this->_callbackClose($element);
    }

    /**
     * Close app
     *
     * @param  ElementCredit|null $element
     */
    protected function _callbackClose($element = null)
    {
        if ($element === null || !($element instanceof \ElementCreditHappylend)) {
            $this->hyper['cms']->close(200);
        }

        header('Content-type: application/json');
        $this->hyper['cms']->close('{"Result": true}');
    }

    /**
     * Get credit element
     *
     * @return  ElementCredit|null
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getElementFromCallback()
    {
        $elementType = '';
        if ($this->hyper['input']->get('task') === 'callback-sberbank') {
            $elementType = 'sberbank';
        } else {
            $method = strtolower($this->hyper['input']->get('method', ''));
            $elementType = $method;
        }

        if (empty($elementType)) {
            return null;
        }

        switch ($elementType) {
            case 'tinkoff':
                $input = new JSON(file_get_contents('php://input'));
                $orderId = $input->get('id');
                if (is_string($orderId) && strpos($orderId, 'Р') === 0) {
                    $elementType .= 'installment';
                }
                break;
            case 'happylend':
                $input = new JSON(file_get_contents('php://input'));
                $merchantName = $input->get('MerchantName');
                if ($merchantName === 'ГИПЕРПК_Рассрочка') {
                    $elementType = 'installment';
                }
                break;
        }

        /** @var ElementCredit */
        $element = Manager::getInstance()->create(
            $elementType,
            Manager::ELEMENT_TYPE_CREDIT,
            (array) $this->hyper['params']->find('credit.' . $elementType)
        );

        return $element;
    }

    /**
     * Process sberbank merchant.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function callbackSberBank()
    {
        $this->callback();
    }

    /**
     * Get model.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  JModelLegacy|HyperPcModelOrder
     *
     * @since   2.0
     */
    public function getModel($name = 'Order', $prefix = HP_MODEL_CLASS_PREFIX, $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('callback', 'callback')
            ->registerTask('callback-sberbank', 'callbackSberBank')
            ->registerTask('send-profile-request', 'sendProfileRequest');

        parent::initialize($config);
    }

    /**
     * Send credit request from user profile.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function sendProfileRequest()
    {
        $conditions = [];
        $user       = $this->hyper['user'];
        $id         = $this->hyper['input']->get('id');
        $token      = $this->hyper['input']->get('token');
        $elType     = $this->hyper['input']->get('element');

        $db = $this->hyper['db'];

        if ($user->id) {
            $conditions[] = [
                'conditions' => [$db->quoteName('a.created_user_id') . ' = ' . $db->quote($user->id)]
            ];
        }

        /** @var Order $order */
        $order = $this->hyper['helper']['order']->findById($id, $conditions);

        if ($order->getToken() !== $token) {
            throw new \Exception('COM_HYPERPC_ORDER_NOT_FOUND', 404);
        }

        $orderViewUrl = $this->hyper['route']->build([
            'view'  => 'order',
            'id'    => $order->id,
            'token' => $order->getToken()
        ], true);

        if ($this->hyper['user']->id) {
            $orderViewUrl = $this->hyper['route']->build([
                'id'   => $order->id,
                'view' => 'profile_order'
            ], true);
        }

        if (!$this->hyper['helper']['credit']->checkClearanceDayLimit($order)) {
            $this->hyper['cms']->enqueueMessage(
                Text::plural(
                    'COM_HYPERPC_CREDIT_CLEARANCE_DAY_LIMIT_INFO',
                    $this->hyper['helper']['credit']->getClearanceDayLimit()
                ),
                'info'
            );

            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_CREDIT_CLEARANCE_DAY_LIMIT_ERROR'), 'error');
            $this->hyper['cms']->redirect($orderViewUrl);
        }

        $table = $this->getModel()->getTable();
        $table->bind($order->getArray());

        $elParams = array_merge(
            (array) $this->hyper['params']->find('credit.' . $elType),
            [
                'table'      => $table,
                'successUrl' => $orderViewUrl
            ]
        );

        try {
            /** @var ElementCredit $element */
            $element = Manager::getInstance()->create($elType, 'credit', $elParams);
            if ($element->notify()) {
                /** @var Order $order */
                $order = $this->hyper['helper']['order']->findById($order->id, ['new' => true]);
                $this->hyper['cms']->redirect($element->getRedirectUrl($order));
            }
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_CREDIT_METHODS_SEND_OTHER_ERROR'), 'info');
            $this->hyper['cms']->redirect($orderViewUrl);
        } catch (\Exception $error) {
            $this->hyper['cms']->enqueueMessage($error->getMessage(), 'info');
            $this->hyper['cms']->redirect($orderViewUrl);
        }

        $this->hyper['cms']->redirect($orderViewUrl);
    }

    /**
     * Find Amo lead id.
     *
     * @param   Order  $order
     *
     * @return  int|mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _findLeadIdByOrder(Order $order)
    {
        $orderLeadId = $order->getAmoLeadId();

        if (!$orderLeadId) {
            /** @var CrmHelper $crmHelper */
            $crmHelper = $this->hyper['helper']['crm'];
            $leads = $crmHelper->getLeadByQuery(['query' => $order->id]);
            if (count($leads)) {
                foreach ($leads as $lead) {
                    $lead = new JSON($lead);
                    $customFields = (array) $lead->get('custom_fields');
                    if (count($customFields)) {
                        foreach ($customFields as $customField) {
                            $customField = new JSON($customField);
                            $fieldId     = $customField->get('id', 0, 'int');
                            $fieldValue  = $customField->find('values.0.value', 0, 'int');
                            if ($fieldId === $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_ORDER_ID_KEY) && $fieldValue === $order->id) {
                                return $lead->get('id', 0, 'int');
                            }
                        }
                    }
                }
            }
        }

        return $orderLeadId;
    }
}
