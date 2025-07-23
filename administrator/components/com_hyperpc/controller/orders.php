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
 */

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Elements\Manager;
use HYPERPC\Helper\OrderHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Controller\ControllerAdmin;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcControllerOrders
 *
 * @since   2.0
 */
class HyperPcControllerOrders extends ControllerAdmin
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_ORDER';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  bool|JModelLegacy
     *
     * @since   2.0
     */
    public function getModel($name = 'Order', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
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
            ->registerTask('recount', 'recount')
            ->registerTask('send_to_amo', 'sendToAmo')
            ->registerTask('update_from_amo', 'updateFromAmo');
    }

    /**
     * Recount total order price.
     *
     * @return  bool
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function recount()
    {
        //  Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        //  Get items to remove from the request.
        $cid = $this->input->get('cid', [], 'array');

        $count = 0;
        /** @var OrderHelper $helper */
        $helper = $this->hyper['helper']['order'];
        /** @var HyperPcModelOrder $model */
        $model = $this->getModel();

        if (count($cid)) {
            foreach ($cid as $id) {
                /** @var Order $order */
                $order    = $helper->findById($id);
                $newTotal = $helper->recount($order);
                if ($newTotal !== false) {
                    $order->total->set($newTotal);

                    $data = $order->getArray();
                    if ($model->save($data)) {
                        $count++;
                    }
                }
            }
        }

        $this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_RECOUNTED', $count));

        $this->setRedirect(['view' => '%view']);
        return true;
    }

    /**
     * Send order to AmoCRM.
     *
     * @return  bool
     *
     * @throws  Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function sendToAmo()
    {
        //  Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        //  Get items to remove from the request.
        $ids = $this->input->get('cid', [], 'array');

        if (!count($ids)) {
            //  Checkin failed.
            $message = Text::_('COM_HYPERPC_ERROR_NO_SELECT_ITEM');
            $this->setRedirect(['view' => '%view'], $message, 'error');
            return false;
        }

        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $orders = $this->hyper['helper']['order']->findById($ids);
        $elementManager = Manager::getInstance();

        $count    = 0;
        $alertMsg = [];
        if (count($orders)) {
            /** @var Order $order */
            foreach ($orders as $order) {
                $orderLeadId = null;
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
                                    $orderLeadId = $lead->get('id');
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!$orderLeadId) {
                    $table     = $this->getModel()->getTable();
                    $table->id = $order->id;

                    /** @var \ElementOrderHookAmoCrm $amoElement */
                    $amoElement = $elementManager->create('amo_crm', 'order_hook', array_merge(
                        ['table' => $table],
                        (array) $this->hyper['params']->find('order_after_save.amo_crm')
                    ));

                    $amoElement->hook();
                    if ($amoElement->getLeadId()) {
                        $alertMsg[] = Text::sprintf(
                            'COM_HYPERPC_ORDER_AMO_SUCCESS_SEND_ALERT_MSG',
                            $order->getName(),
                            (string) $order->getAmoLeadUrl(),
                            (string) $order->getAmoLeadUrl()
                        );

                        $count++;
                    }
                }
            }
        }

        if ($count === 0) {
            $this->setMessage(Text::_($this->text_prefix . '_ITEMS_NO_SEND_TO_AMO'));
        } else {
            $this->setMessage(implode('<br />', [
                Text::plural($this->text_prefix . '_N_ITEMS_SEND_TO_AMO', $count),
                implode('<br />', $alertMsg)
            ]));
        }

        $this->setRedirect(['view' => '%view']);
        return true;
    }

    /**
     * Update order data from AmoCRM.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function updateFromAmo()
    {
        //  Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        //  Get items to remove from the request.
        $ids = $this->input->get('cid', [], 'array');

        if (!count($ids)) {
            //  Checkin failed.
            $message = Text::_('COM_HYPERPC_ERROR_NO_SELECT_ITEM');
            $this->setRedirect(['view' => '%view'], $message, 'error');
            return false;
        }

        /** @var HyperPcModelOrder $model */
        $model = $this->getModel();

        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $count  = 0;
        $orders = $this->hyper['helper']['order']->findById($ids);
        if (count($orders)) {
            /** @var Order $order */
            foreach ($orders as $order) {
                $leadData = null;

                $leadId = $order->getAmoLeadId();
                if ($leadId) {
                    $leadData = $crmHelper->getLeadById($leadId);
                } else {
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
                                        $leadData = $lead;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($leadData !== null) {
                    $pipelineId = $leadData->get('pipeline_id');
                    if (!$pipelineId) {
                        $pipelineId = $leadData->find('pipeline.id');
                    }

                    $crmHelper
                        ->updateOrderStatusByAmoStatusId($order, $leadData->get('status_id'), $pipelineId)
                        ->updateOrderDataByCustomFields($order, (array) $leadData->get('custom_fields'))
                        ->updateOrderWorkerByResponsibleUserId($order, $leadData->get('responsible_user_id', 0, 'int'));

                    $leadId = $leadData->get('id');
                    $order->params->set('amo_lead_id', $leadId);

                    if ($model->save($order->getArray())) {
                        $count++;
                    }

                    $this->hyper['helper']['dealMap']->bindCrmLeadToSiteOrder($leadId, $order->id);
                }
            }
        }

        $this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_UPDATE_FROM_AMO', $count));

        $this->setRedirect(['view' => '%view']);

        return true;
    }
}
