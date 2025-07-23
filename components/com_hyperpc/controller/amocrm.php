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
use JBZoo\Utils\Filter;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Helper\OrderHelper;
use HYPERPC\Helper\MindboxHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Controller\ControllerForm;

/**
 * Class    HyperPcControllerAmoCrm
 *
 * @since   2.0
 */
class HyperPcControllerAmoCrm extends ControllerForm
{

    /**
     * Hold CrmHelper object.
     *
     * @var     CrmHelper
     *
     * @since   2.0
     */
    protected $_helper;

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
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_helper = $this->hyper['helper']['crm'];

        $this
            ->registerTask('secrets', 'secrets')
            ->registerTask('on_create_lead', 'onCreateLead')
            ->registerTask('on_update_lead', 'onUpdateLead')
            ->registerTask('on_update_contact', 'onUpdateContact')
            ->registerTask('on_update_lead_status', 'onUpdateLeadStatus');
    }

    /**
     * Handle CRM oAuth hook.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function secrets()
    {
        $data = file_get_contents('php://input');

        if (strlen($data) === 0) {
            $this->hyper['cms']->close();
        }

        $input = new Registry($data);

        $error = $input->get('error');
        if ($error) {
            $this->hyper['cms']->close();
        }

        $state = $input->get('state');
        if ($state !== $this->_helper->getOauthStateHash()) {
            $this->hyper['cms']->close();
        }

        $clientId = $input->get('client_id');
        $clientSecret = $input->get('client_secret');

        if (!$clientId || !$clientSecret) {
            $this->hyper['cms']->close();
        }

        $this->_helper->saveSecrets($clientId, $clientSecret);

        $this->hyper['cms']->close();
    }

    /**
     * On create crm lead.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onCreateLead()
    {
        $responseCode = 200;
        $result       = 'Skipped';

        $response = new Registry($_POST);

        $dataKey = 'leads.add.0.';

        $customFields = $response->get($dataKey . 'custom_fields', []);
        $order  = $this->_findOrderByLeadCustomFields($customFields);
        $leadId = $response->get($dataKey . 'id');
        if ($order->id) {
            $result = 'Order found';
            if ($order->getUuid() && !$order->getAmoLeadId()) {
                if ($leadId) {
                    $order->params->set('amo_lead_id', $leadId);
                    $this->hyper['helper']['dealMap']->bindCrmLeadToSiteOrder($leadId, $order->id);

                    $result = 'Lead id set successfully';
                    if (!$order->helper->getTable()->save($order)) {
                        $result = 'Error saving order';
                    }

                    $leadData = $this->_helper->getLeadById($leadId);
                    $leadTags = $leadData['tags'];
                    $leadTags[] = Text::_('COM_HYPERPC_CRM_TAG_' . ($order->hasOnlyAccessories() ? 'ACCESSORIES' : 'PC_SALE'));
                    if ($order->isCredit()) {
                        $leadTags[] = Text::_('COM_HYPERPC_CRM_TAG_CREDIT');
                    }

                    if ($order->hasInstockProducts()) {
                        $leadTags[] = Text::_('COM_HYPERPC_CRM_TAG_FROM_STOCK');
                    }

                    $leadData['tags'] = $leadTags;

                    $user = $order->getCreatedUser();

                    $leadCustomFields = $leadData['custom_fields'];

                    // Ready to pay
                    $allInStock = !$order->hasZeroStockItems();
                    if ($allInStock) {
                        $leadCustomFields[] = [
                            'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_READY_TO_PAY_KEY),
                            'values' => [['value' => (int) $allInStock]]
                        ];
                    }

                    // Clients segmentation
                    $leadCustomFields[] = [
                        'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_BUYER_TYPE_KEY),
                        'values' => [[
                            'value' => ($order->isBuyerACompany()) ?
                                $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_BUYER_TYPE_LEGAL_KEY) :
                                $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_BUYER_TYPE_INDIVIDUAL_KEY)
                        ]]
                    ];

                    // Order type
                    $leadCustomFields[] = [
                        'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PRODUCT_KEY),
                        'values' => [[
                            'value' => match ($order->getOrderType()) {
                                Order::ORDER_TYPE_UPGRADE,
                                Order::ORDER_TYPE_ACCESSORIES
                                    => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_ACCESSORY_KEY),
                                Order::ORDER_TYPE_NOTEBOOKS
                                    => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_NOTEBOOK_KEY),
                                Order::ORDER_TYPE_PRODUCTS
                                    => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_PC_KEY)
                            }
                        ]]
                    ];

                    // Analytics
                    $leadCustomFields[] = [
                        'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_GOOGLE_CLIENT_ID_KEY),
                        'values' => [['value' => $order->cid]]
                    ];
                    $leadCustomFields[] = [
                        'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_USER_ID_KEY),
                        'values' => [['value' => $user->getUid()]]
                    ];
                    $leadCustomFields[] = [
                        'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_COUNTER_KEY),
                        'values' => [['value' => $order->getYmCounter()]]
                    ];
                    $leadCustomFields[] = [
                        'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_UID_KEY),
                        'values' => [['value' => $order->getYmUid()]]
                    ];

                    $this->_helper->updateLead([[
                        'updated_at'    => time(),
                        'id'            => $leadId,
                        'tags'          => $leadTags,
                        'custom_fields' => $leadCustomFields
                    ]]);
                }
            }

            $log = new Registry($response->toArray());
            $log->set('response_code', $responseCode);
            $log->set('result', $result);

            OrderHelper::writeLog($order->id, 'amo_crm_create_lead', $log->toString('JSON', ['bitmask' => JSON_PRETTY_PRINT]));
        } else {
            $moyskladOrderUrl = $this->_getCustomFieldValue(
                $customFields,
                $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_MOYSKLAD_ORDER_URL_KEY)
            );

            if (!empty($moyskladOrderUrl)) {
                $uuid = $this->hyper['helper']['moysklad']->getEntityUuidFromEditUrl($moyskladOrderUrl);
                if (empty($uuid)) {
                    $this->hyper['helper']['dealMap']->addCrmLeadId($leadId);
                }
            }
        }

        $this->hyper->log(
            'Result: ' . $result . '. Order: ' . $order->id . ' . Data: ' . $response->toString(),
            null,
            'amo/' . date('Y/m/d') . '/lead_create.php'
        );

        http_response_code($responseCode);
        $this->hyper['cms']->close();
    }

    /**
     * On CRM update contact.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function onUpdateContact()
    {
        $responseCode = 200;
        $requestData  = ($this->_helper->isDebug()) ? $this->_helper->getTestData('contact_update') : $_POST;
        $response     = new JSON($requestData);
        $contact      = $response->find('contacts.update.0');
        $customFields = (array) $response->find('contacts.update.0.custom_fields');
        $leads        = (array) $response->find('contacts.update.0.linked_leads_id');

        $email = null;
        $phone = null;

        if (count($customFields)) {
            foreach ($customFields as $customField) {
                $customField = new JSON($customField);
                $fieldId = Filter::int($customField->get('id'));
                if ($fieldId === $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_EMAIL_KEY)) {
                    $email = $customField->find('values.0.value');
                } elseif ($fieldId === $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_PHONE_KEY)) {
                    $phone = $customField->find('values.0.value');
                }
            }

            if ($phone !== null && $email !== null && $leads) {
                foreach ($leads as $leadId => $data) {
                    /** @var JSON $lead */
                    $lead = $this->_helper->getLeadById($leadId);
                    if ($lead->get('id')) {
                        $customFields = (array) $lead->get('custom_fields');

                        $order = $this->_findOrderByLeadCustomFields($customFields);
                        if ($order->id) {
                            $log = new JSON($response->getArrayCopy());

                            $order->elements
                                ->set('phone', ['value' => $phone])
                                ->set('email', ['value' => $email])
                                ->set('username', ['value' => $contact['name']]);

                            if (!$order->helper->getTable()->save($order)) {
                                $responseCode = 404;
                            }

                            $log->set('response_code', $responseCode);

                            OrderHelper::writeLog($order->id, 'amo_crm_update_contact', $log->write());
                        }
                    }
                }
            }
        }

        $this->hyper->log(
            'Response code: ' . $responseCode . '. Data: ' . json_encode($response->getArrayCopy()),
            null,
            'amo/' . date('Y/m/d') . '/contact_update.php'
        );

        http_response_code($responseCode);
        $this->hyper['cms']->close();
    }

    /**
     * On update crm lead.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function onUpdateLead()
    {
        $requestData  = ($this->_helper->isDebug()) ? $this->_helper->getTestData() : $_POST;
        $request      = new JSON($requestData);

        $customFields = $request->find('leads.update.0.custom_fields', [], 'arr');

        $leadData = new JSON($request->find('leads.update.0'));
        $this->hyper['helper']['worker']->setLastAmoCrmActionByLead($leadData);

        $order = $this->_findOrderByLeadCustomFields($customFields);

        if (!$order->id) {
            $moyskladSync = $this->_getCustomFieldValue(
                $customFields,
                $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_MOYSKLAD_SYNC_KEY)
            );

            $moyskladOrderUrl = $this->_getCustomFieldValue(
                $customFields,
                $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_MOYSKLAD_ORDER_URL_KEY)
            );

            if ($moyskladSync && empty($moyskladOrderUrl)) {
                // Todo create moysklad order with AmoCrm lead data and bindMoyskladOrderToCrmLead
            }

            $this->hyper->log(
                'Result: Order not found. Lead: ' . $request->find('leads.update.0.id'),
                null,
                'amo/' . date('Y/m/d') . '/lead_update.php'
            );

            http_response_code(200);
            $this->hyper['cms']->close();
        }

        $changes = [];
        $logMessage = 'Not changed';

        $currentWorkerId = $order->worker_id;
        $this->_helper
            ->updateOrderWorkerByResponsibleUserId($order, $request->find('leads.update.0.responsible_user_id', 0, 'int'));
        if ($currentWorkerId !== $order->worker_id) {
            $changes[] = 'worker_id';
        }

        $leadId = $request->find('leads.update.0.id', 0, 'int');
        if ($order->getAmoLeadId() !== $leadId) {
            $order->params->set('amo_lead_id', $leadId);
            $changes[] = 'amo_lead_id';
        }

        $logPriority = Log::INFO;

        if (!empty($changes)) {
            $logMessage = 'Success';

            // Try to update status if the order has changes.
            // Prevents overwriting order status when hooks are processed at the same time.
            $oldCrmStatusId = $request->find('leads.update.0.old_status_id', 0, 'int');
            if (!empty($oldCrmStatusId)) {
                $crmStatusId = $request->find('leads.update.0.status_id', 0, 'int');
                $pipelineId  = $request->find('leads.update.0.pipeline_id', 0, 'int');

                $siteStatusData = $this->_helper->findSiteStatusByPipelineStatus($crmStatusId, $pipelineId);
                if (!empty($siteStatusData)) {
                    list(, , $siteStatusId) = $siteStatusData;
                    if ($order->status !== $siteStatusId) {
                        $this->_helper->updateOrderStatusByAmoStatusId($order, $crmStatusId, $pipelineId);
                        $changes[] = 'status';
                    }
                }
            }

            $isSaved = $order->helper->getTable()->save($order);
            if (!$isSaved) {
                $logPriority = Log::ERROR;
                $logMessage = 'Error: Order not saved';
            }

            $logMessage .= '. Changes: ' . join(', ', $changes);
        }

        $this->hyper->log(
            'Result: ' . $logMessage . '. Order: ' . $order->id,
            $logPriority,
            'amo/' . date('Y/m/d') . '/lead_update.php'
        );

        $logData = array_merge(['result' => $logMessage], $request->getArrayCopy());

        OrderHelper::writeLog($order->id, 'amo_crm_update_lead', json_encode($logData, JSON_PRETTY_PRINT));

        http_response_code(200);
        $this->hyper['cms']->close();
    }

    /**
     * Update order status on update crm pipeline status id.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function onUpdateLeadStatus()
    {
        $data         = ($this->_helper->isDebug()) ? $this->_helper->getTestData('lead_status') : $_POST;
        $request      = new JSON($data);
        $customFields = $request->find('leads.status.0.custom_fields', [], 'arr');

        $order = $this->_findOrderByLeadCustomFields($customFields);

        if (!$order->id) {
            $this->hyper->log(
                'Result: Order not found. Lead: ' . $request->find('leads.status.0.id'),
                null,
                'amo/' . date('Y/m/d') . '/lead_status.php'
            );

            http_response_code(200);
            $this->hyper['cms']->close();
        }

        $log        = new JSON();
        $logMessage = '';

        $crmStatusId = Filter::int($request->find('leads.status.0.status_id'));
        $pipelineId  = $request->find('leads.status.0.pipeline_id');

        $statusName = '';
        $pipelines  = $this->_helper->getPipelineTmpData();
        $pipeline   = $pipelines->get($pipelineId);
        $result     = true;
        if (!$pipeline) {
            $result     = false;
            $logMessage = 'Error: Pipeline ' . $pipelineId . ' not found. Need to update Amo status map';
        } else {
            $pipelineData = new JSON($pipeline);
            $statusData = $pipelineData->find('statuses.' . $crmStatusId);
            if (empty($statusData)) {
                $result     = false;
                $logMessage = 'Error: Status ' . $crmStatusId . ' not found. Need to update Amo status map';
            } else {
                $statusName = $pipelineData['name'] . ': ' . $statusData['name'];
            }
        }

        if ($result === false) {
            $log->set('result', $logMessage);

            goto Log;
        }

        $siteStatus = $this->_helper->findSiteStatusByPipelineStatus($crmStatusId, $pipelineId);
        if (empty($siteStatus)) {
            $logMessage = 'Warning: Amo status not exits on the site';
            $log
                ->set('result', $logMessage)
                ->set('status', $statusName);

            goto Log;
        }

        $oldOrderStatus = $order->status;

        $this->_helper->updateOrderStatusByAmoStatusId($order, $crmStatusId, $pipelineId);
        $order->params->set('amo_lead_id', $request->find('leads.status.0.id'));

        $isSaved = $order->helper->getTable()->save($order);
        if (!$isSaved) {
            $logMessage = 'Error: Order not saved';
            $log->set('result', $logMessage);

            goto Log;
        }

        /** @var Order $newOrder */
        $newOrder = $this->hyper['helper']['order']->findById($order->id, ['new' => true]);
        $newOrderStatus = $newOrder->status;

        $statuses = $this->hyper['helper']['status']->findById([$newOrderStatus, $oldOrderStatus]);
        if ($newOrderStatus === $oldOrderStatus) {
            $logMessage = 'Same status';
            $log
                ->set('result', $logMessage)
                ->set('status', $statuses[$newOrderStatus]->name ?? '');

            goto Log;
        }

        /** @todo handle update status in order save event */
        /** @var MindboxHelper $mindboxHelper */
        $mindboxHelper = $this->hyper['helper']['mindbox'];
        $mindboxHelper->updateOrderStatus($order);

        $logMessage = 'Status changed';
        $log
            ->set('result', $logMessage)
            ->set('status_old', $statuses[$oldOrderStatus]->name ?? '')
            ->set('status_new', $statuses[$newOrderStatus]->name ?? '');

        // Set status in MoySklad
        if ($this->hyper['params']->get('moysklad_sync_order_statuses', false, 'bool')) {
            try {
                $this->hyper['helper']['moyskladCustomerOrder']->updateState($order);
            } catch (\Throwable $th) {
            }
        }

        Log:

        OrderHelper::writeLog($order->id, 'amo_crm_update_lead_status', $log->write());

        $log->remove('result');
        $this->hyper->log(
            'Result: ' . $logMessage . '. Order: ' . $order->id . PHP_EOL . 'Details: ' . json_encode($log->getArrayCopy(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),
            null,
            'amo/' . date('Y/m/d') . '/lead_status.php'
        );

        http_response_code(200);
        $this->hyper['cms']->close();
    }

    /**
     * Find order by lead custom fields
     *
     * @param   array $customFields
     *
     * @return  Order
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _findOrderByLeadCustomFields(array $customFields)
    {
        $orderId = $this->_getCustomFieldValue(
            $customFields,
            $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ORDER_ID_KEY)
        );

        $orderHelper = $this->hyper['helper']['order'];

        if (!empty($orderId)) {
            return $orderHelper->findById($orderId);
        }

        $moyskladOrderUrl = $this->_getCustomFieldValue(
            $customFields,
            $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_MOYSKLAD_ORDER_URL_KEY)
        );

        if (!empty($moyskladOrderUrl)) {
            $uuid = $this->hyper['helper']['moysklad']->getEntityUuidFromEditUrl($moyskladOrderUrl);
            if (!empty($uuid)) {
                return $orderHelper->findByUuid($uuid);
            }
        }

        return $orderHelper->findById(0);
    }

    /**
     * Get custom field value.
     *
     * @param   array   $customFields
     * @param   int     $customFieldId
     * @param   string  $key
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getCustomFieldValue(array $customFields, $customFieldId, $key = 'values.0.value')
    {
        foreach ($customFields as $customField) {
            $customField = new JSON($customField);
            if ($customField->get('id', 0, 'int') === Filter::int($customFieldId)) {
                return $customField->find($key);
            }
        }

        return null;
    }
}
