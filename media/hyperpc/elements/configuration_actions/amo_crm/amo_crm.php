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

use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Elements\ElementConfiguratorActions;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ElementConfigurationActionsAmoCrm
 *
 * @property    SaveConfiguration   $_configuration
 * @property    array               $_leadData
 * @property    int                 $_leadId
 * @property    array               $_leadNote
 * @property    ProductMarker       $_product
 * @property    CrmHelper           $crm
 * @property    User                $user
 *
 * @since       2.0
 */
class ElementConfigurationActionsAmoCrm extends ElementConfiguratorActions
{

    const ACTION_METHOD_UPDATE = 'update';
    const MIN_PHONE_STR_LENGTH = 4;

    /**
     * Hold message.
     *
     * @var     null|string
     *
     * @since   2.0
     */
    protected $_message = null;

    /**
     * Action send request to amo crm.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function actionSendRequest()
    {
        $output = new JSON([
            'result'  => false,
            'message' => null,
            'lead_id' => 0
        ]);

        $method = $this->hyper['input']->get('method');

        $this
            ->_setConfiguration($this->getConfiguration())
            ->_setProduct($this->_configuration->getProduct());

        $amoCrmLeadId = $this->_configuration->getAmoLeadId();

        if ($amoCrmLeadId && $method !== self::ACTION_METHOD_UPDATE) {
            $output
                ->set('result', true)
                ->set('lead_id', $amoCrmLeadId)
                ->set('message', Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_AMO_CRM_LEAD_HAS_BEEN_ADDED_EARLIE'));

            $this->hyper['cms']->close($output->write());
        }

        if (!$this->_checkContactData($this->_configuration)) {
            $output->set('message', Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_AMO_CRM_LEAD_NOT_FIND_SAVED_DATA'));
            $this->hyper['cms']->close($output->write());
        }

        $this->_addLead();

        if ($this->_leadId) {
            $output
                ->set('result', true)
                ->set('lead_id', $this->_leadId)
                ->set('message', Text::_('COM_HYPERPC_AMO_CRM_LEAD_SUCCESS_ADD'));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Action save lead.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function actionSaveLead()
    {
        $this
            ->_setConfiguration($this->getConfiguration())
            ->_setProduct($this->_configuration->getProduct());

        if (!$this->_checkContactData($this->_configuration)) {
            $this->_message = Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_AMO_CRM_LEAD_NOT_FIND_SAVED_DATA');
        }

        $this->_addLead();
    }

    /**
     * Action update lead.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function actionUpdateLead()
    {
        $this
            ->_setConfiguration($this->getConfiguration())
            ->_setProduct($this->_configuration->getProduct());

        $amoCrmLeadId = $this->_configuration->getAmoLeadId();

        if ($this->_checkContactData($this->_configuration) && $amoCrmLeadId) {
            $this->hyper['input']->set('method', self::ACTION_METHOD_UPDATE);
            $this->_addLead();
        }
    }

    /**
     * Get message.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Get user email.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUserEmail()
    {
        return $this->_configuration->params->get('email');
    }

    /**
     * Get username.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUsername()
    {
        return $this->_configuration->params->get('username');
    }

    /**
     * Get user phone.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUserPhone()
    {
        return $this->_configuration->params->get('phone');
    }

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->crm  = $this->hyper['helper']['crm'];
        $this->user = $this->hyper['helper']['user']->findById($this->hyper['user']->id, [
            'load_fields' => true
        ]);

        $this->loadAssets();
        $this->registerAction('sendRequest');
    }

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $this->hyper['helper']['assets']
            ->js('elements:' . $this->_group . '/' . $this->_type . '/assets/js/widget.js')
            ->widget('body', 'HyperPC.SiteConfigurationActionsAmoCrm', [
                'goToAmoTitle' => Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_AMO_CRM_GO_TO_AMO_LEAD')
            ]);
    }

    /**
     * Render action button in profile account.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function renderActionButton()
    {
        $configuration = $this->getConfiguration();
        $amoCrmLeadId  = $configuration->getAmoLeadId();

        $actionUrl = $this->hyper['route']->build([
            'tmpl'       => 'component',
            'action'     => 'sendRequest',
            'task'       => 'elements.call',
            'group'      => $this->getGroup(),
            'identifier' => $this->getIdentifier(),
            'id'         => $configuration->id
        ]);

        if ($amoCrmLeadId) {
            $this->_config
                ->set(
                    'account_action_name',
                    Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_AMO_CRM_GO_TO_AMO_LEAD')
                )
                ->set('action_icon', 'link');

            return implode('', [
                '<a href="' . $this->crm->getLeadUrl($amoCrmLeadId) . '" target="_blank">',
                    $this->getAccountActionTile(),
                '</a>'
            ]);
        }

        return implode('', [
            '<a href="' . $actionUrl . '" class="jsSendRequestForAmoCrm">',
                $this->getAccountActionTile(),
            '</a>'
        ]);
    }

    /**
     * Add contact.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _addContact()
    {
        $contactData = [
            'created_at'    => time(),
            'name'          => $this->getUsername(),
            'custom_fields' => $this->crm->getContactCustomFields(
                $this->getUserEmail(),
                $this->getUserPhone()
            )
        ];

        $newContactBody = $this->crm->addContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }

    /**
     * Add lead in amo crm.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _addLead()
    {
        list ($pipeline, $statusId) = $this->_getPipelineStatus();

        if ($pipeline && $statusId) {
            $userEmail    = $this->getUserEmail();
            $contactsBody = $this->crm->getContacts(['query' => $userEmail]);
            $contacts     = (array) $contactsBody->find('_embedded.items');
            $contactId    = $this->crm->findContactByEmail($userEmail, $contacts);

            if ($contactId === null) {
                $contactId = $this->_addContact();
            } else {
                $this->_updateContact($contactId);
            }

            $this
                ->_setDefaultLeadData($contactId)
                ->_setLeadNote();

            $currentLeadId = $this->_configuration->getAmoLeadId();
            if ($currentLeadId > 0) {
                $userFirstOpenLead = $this->crm->getLeadById($currentLeadId);
                if (!$userFirstOpenLead->get('id')) {
                    $userLeads = $this->crm->findAllUserOpenLeads(
                        $this->getUserPhone(),
                        $this->getUserEmail()
                    );

                    $userFirstOpenLead = current($userLeads);
                }
            } else {
                $userLeads = $this->crm->findAllUserOpenLeads(
                    $this->getUserPhone(),
                    $this->getUserEmail()
                );

                $userFirstOpenLead = current($userLeads);
            }

            if ($userFirstOpenLead instanceof JSON) {
                $newLeadBody   = new JSON((array) Hash::merge($userFirstOpenLead->getArrayCopy(), $this->_leadData));
                $this->_leadId = $newLeadBody->get('id');

                $newLeadBody
                    ->set('status_id', $userFirstOpenLead->get('status_id'))
                    ->set('pipeline_id', $userFirstOpenLead->get('pipeline_id'));

                $this->crm->addNote(
                    $this->_leadId,
                    Text::sprintf(
                        'COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_SITE_LEAD_DOUBLE',
                        $userFirstOpenLead->get('name')
                    ),
                    CrmHelper::NOTE_EVENT_DEAL_SYSTEM
                );

                $this->crm->updateLead([$newLeadBody->getArrayCopy()]);
            } else {
                $newLeadBody = $this->crm->addLead([$this->_leadData]);
                $this->_leadId = $newLeadBody->find('_embedded.items.0.id');

                $this->crm->addNote(
                    $this->_leadId,
                    Text::sprintf(
                        'COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_SITE_LEAD_ADD_BY_MANAGER',
                        $this->hyper['user']->username
                    ),
                    CrmHelper::NOTE_EVENT_DEAL_SYSTEM
                );
            }

            if ($this->_leadId) {
                $this->_configuration->params->set('amo_lead_id', $this->_leadId);
                $this->hyper['helper']['configuration']->getTable()->save($this->_configuration->getArray());

                /** @var ElementConfigurationActionsCommercialProposal $kpElement */
                $kpElement = $this->getManager()->getElement($this->_group, 'commercial_proposal');
                if ($kpElement instanceof ElementConfigurationActionsCommercialProposal) {
                    $this->crm->addNote(
                        $this->_leadId,
                        Text::sprintf(
                            'COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_CONFIGURATION_KP_PDF_URL',
                            $kpElement->getActionButtonUrl(true)
                        ),
                        CrmHelper::NOTE_EVENT_DEAL_SYSTEM
                    );
                }

                $this->crm->addNote($this->_leadId, implode(PHP_EOL, $this->_leadNote));
            }
        }
    }

    /**
     * Get lead name.
     *
     * @return  string|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getLeadName()
    {
        $leadName = null;
        if ($this->_configuration->id) {
            $leadName = Text::sprintf(
                'COM_HYPERPC_AMO_CRM_CONFIGURATION_SAVE_LEAD_NAME',
                $this->_configuration->getName()
            );
        } else {
            $leadName = Text::_('COM_HYPERPC_AMO_CRM_CONFIGURATION_NATIVE_SAVE_LEAD_NAME');
        }

        $phoneNumber  = $this->crm->clearMobilePhone($this->getUserPhone());
        if ($phoneNumber && strlen($this->getUserPhone()) > self::MIN_PHONE_STR_LENGTH) {
            $leadName .= '. тел. ' . $phoneNumber;
        } else {
            $leadName .= '. ' . $this->getUserEmail();
        }

        return $leadName;
    }

    /**
     * Get current pipeline and status.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getPipelineStatus()
    {
        $pipeLineDef     = $this->_config->get('pipeline_default', ':');
        $pipelineDefault = ($pipeLineDef) ? $pipeLineDef : ':';

        return explode(':', $pipelineDefault);
    }

    /**
     * Setup configuration.
     *
     * @param   SaveConfiguration  $configuration
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setConfiguration(SaveConfiguration $configuration)
    {
        $this->_configuration = $configuration;
        return $this;
    }

    /**
     * Setup default lead data.
     *
     * @param   int $contactId
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _setDefaultLeadData($contactId)
    {
        list ($pipeline, $statusId) = $this->_getPipelineStatus();

        $this->_leadData = [
            'created_at'    => time(),
            'pipeline_id'   => $pipeline,
            'name'          => $this->_getLeadName(),
            'status_id'     => $statusId,
            'contacts_id'   => $contactId,
            'tags'          => [],
            'sale'          => $this->_configuration->getDiscountedPrice()->val(),
            'custom_fields' => [
                [
                    'id'     => $this->crm->getCustomFieldId(CrmHelper::LEAD_FIELD_CONFIGURATION_ID_KEY),
                    'values' => [
                        [
                            'value' => $this->_configuration->id
                        ]
                    ]
                ],
                [
                    'id'     => $this->crm->getCustomFieldId(CrmHelper::LEAD_FIELD_ROISTAT_KEY),
                    'values' => [
                        [
                            'value' => $this->hyper['input']->cookie->get('roistat_visit', '')
                        ]
                    ]
                ],
                [
                    'id'     => $this->crm->getCustomFieldId(CrmHelper::LEAD_FIELD_GOOGLE_CLIENT_ID_KEY),
                    'values' => [
                        [
                            'value' => $this->hyper['helper']['google']->getCID()
                        ]
                    ]
                ]
            ]
        ];

        /** @var ElementConfigurationActionsData $dataElement */
        $dataElement = $this->getManager()->getElement($this->_group, 'data');
        if ($dataElement instanceof ElementConfigurationActionsData) {
            foreach ($dataElement->getConfig()->getArrayCopy() as $cKey => $amoCustomFieldId) {
                if (preg_match('/^amo_/', $cKey)) {
                    $amoCustomFieldVal = $this->_configuration->params->get($cKey);
                    if ($amoCustomFieldVal) {
                        $this->_leadData['custom_fields'][] = [
                            'id'     => $amoCustomFieldId,
                            'values' => [
                                [
                                    'value' => $amoCustomFieldVal
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        $userAmoCrmContactId = $this->user->getField($this->_config->get('amo_crm_contact_id'));
        if ($userAmoCrmContactId->get('value')) {
            $this->_leadData['responsible_user_id'] = $userAmoCrmContactId->get('value');
        }

        $this->_setLeadTags();

        return $this;
    }

    /**
     * Setup lead note.
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _setLeadNote()
    {
        $phoneLength = strlen($this->getUserPhone());

        $this->_leadNote = [];
        if ($this->hyper['input']->get('method') !== self::ACTION_METHOD_UPDATE) {
            $this->_leadNote = [
                '-=Информация из формы=-',
                sprintf('Имя отправителя: %s', $this->getUsername()),
                sprintf('Email получателя: %s', $this->getUserEmail())
            ];

            if ($phoneLength > self::MIN_PHONE_STR_LENGTH) {
                $this->_leadNote[] = sprintf('Телефон: %s', $this->getUserPhone());
            }
        }

        $this->_leadNote[] = implode("\n", [
            '-----------------------------------',
            (!$this->_configuration->id) ?
                '-=Информация о конфигурации=-' :
                sprintf('-=Информация о конфигурации №%s=-', $this->_configuration->id)
        ]);

        $productName = $this->_product->getName();
        if ($this->_product->quantity > 1) {
            $productName = $this->_product->quantity . ' x ' . $productName;
        }

        $totalPrice = clone $this->_product->price;
        $hasPromo   = $this->_product->get('rate');

        if ($hasPromo) {
            $totalPrice->add('-' . $this->_product->get('rate') . '%');
        }

        $this->_leadNote[] = $productName . ' по цене ' . $totalPrice->text();

        $this->crm->addLeadNoteTextByProductConfiguration($this->_product, $this->_leadNote);

        if ($this->_product->quantity > 1) {
            $this->_leadNote[] = '   Итого: ' . $this->_product->quantity . ' x ' . $totalPrice->text() .
                ' = ' . $this->_product->getQuantityPrice()->text();
        }

        return $this;
    }

    /**
     * Setup lead tags.
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setLeadTags()
    {
        $phoneLength = strlen($this->getUserPhone());
        if ($phoneLength <= self::MIN_PHONE_STR_LENGTH) {
            $this->_leadData['tags'][] = Text::_('COM_HYPERPC_CRM_TAG_CONFIGURATION_WITHOUT_PHONE');
        }

        return $this;
    }

    /**
     * Setup product.
     *
     * @param   ProductMarker $product
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setProduct(ProductMarker $product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Update contact.
     *
     * @param   int $contactId  AmoCRM contact id.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _updateContact($contactId)
    {
        $contactData = [
            'id'            => $contactId,
            'updated_at'    => time(),
            'name'          => $this->getUsername(),
            'custom_fields' => $this->crm->getContactCustomFields(
                $this->getUserEmail(),
                $this->getUserPhone()
            )
        ];

        $newContactBody = $this->crm->updateContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }
}
