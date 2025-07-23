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
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Elements\ElementConfigurationHook;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ElementConfiguratorHookAmoCrm
 *
 * @property    CrmHelper   $_helper
 * @property    null|int    $_leadId
 *
 * @since       2.0
 */
class ElementConfiguratorHookAmoCrm extends ElementConfigurationHook
{

    const MIN_PHONE_STR_LENGTH = 4;

    /**
     * Hold configuration.
     *
     * @var     SaveConfiguration
     *
     * @since   2.0
     */
    protected $_configuration;

    /**
     * Hold lead data.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_leadData = [];

    /**
     * Hold lead note.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_leadNote = [];

    /**
     * Hold product object.
     *
     * @var     ProductMarker
     *
     * @since   2.0
     */
    protected $_product;

    /**
     * Hook action.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function hook()
    {
        if ($this->_checkDisallowUserGroup()) {
            return;
        }

        $jform = new JSON($this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));
        $needConsulting = $jform->get('need_consulting', 0, 'int'); // 0 or 1
        $sendData = $this->_config->get('send_data', 1, 'int'); // -1, 0 or 1

        if (($sendData + $needConsulting) < 1) {
            return;
        }

        $elementContext = $this->getContext();

        $this
            ->_setProduct($this->getProduct())
            ->_setConfiguration($this->getConfiguration());

        if (($elementContext === 'create' || ($elementContext === 'send_by_email')) &&
            $this->_product->id && $this->getUserEmail()) {
            $this->_addLead();
        }

        if (!empty($this->_leadId)) {
            $this->hyper['helper']['dealMap']->addCrmLeadId($this->_leadId);
        }
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
        $this->_helper = $this->hyper['helper']['crm'];
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
            'custom_fields' => $this->_helper->getContactCustomFields(
                $this->getUserEmail(),
                $this->getUserPhone()
            )
        ];

        $newContactBody = $this->_helper->addContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }

    /**
     * Add lead.
     *
     * @return  void
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _addLead()
    {
        list ($pipeline, $statusId) = $this->_getPipelineStatus();

        if ($pipeline && $statusId) {
            $contactsBody = $this->_helper->getContacts(['query' => $this->getUserEmail()]);
            $contacts     = (array) $contactsBody->find('_embedded.items');
            $contactId    = $this->_helper->findContactByEmail($this->getUserEmail(), $contacts);

            if ($contactId === null) {
                $contactId = $this->_addContact();
            } else {
                $this->_updateContact($contactId);
            }

            $this
                ->_setDefaultLeadData($contactId)
                ->_setLeadNote();

            $userLeads = $this->_helper->findAllUserOpenLeads(
                $this->getUserPhone(),
                $this->getUserEmail()
            );

            $userFirstOpenLead = current($userLeads);

            if ($userFirstOpenLead instanceof JSON) {
                unset($this->_leadData['pipeline_id']);
                unset($this->_leadData['status_id']);

                $newLeadBody   = new JSON((array) Hash::merge($userFirstOpenLead->getArrayCopy(), $this->_leadData));
                $this->_leadId = $newLeadBody->get('id');

                $this->_helper->addNote(
                    $this->_leadId,
                    Text::sprintf('COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_SITE_LEAD_DOUBLE', $userFirstOpenLead->get('name')),
                    CrmHelper::NOTE_EVENT_DEAL_SYSTEM
                );

                $this->_helper->updateLead([$newLeadBody->getArrayCopy()]);
            } else {
                $newLeadBody = $this->_helper->addLead([$this->_leadData]);
                $this->_leadId = $newLeadBody->find('_embedded.items.0.id');
            }

            if ($this->_leadId) {
                $configuration = $this->getConfiguration();
                if ($configuration->id) {
                    $configuration->params->set('amo_lead_id', $this->_leadId);
                    $this->hyper['helper']['configuration']->getTable()->save($configuration->getArray());
                }

                $this->_helper->addNote($this->_leadId, implode(PHP_EOL, $this->_leadNote));
            }
        }

        $this->_leadData['sale'] = $this->_product->getListPrice()->val();
    }

    /**
     * Check disallow user groups.
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function _checkDisallowUserGroup()
    {
        $disallowUserGroups = (array) $this->_config->get('disallow_groups');
        foreach ((array) $this->hyper['user']->groups as $groupId) {
            if (in_array($groupId, $disallowUserGroups)) {
                return true;
            }
        }

        return false;
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
            $leadName = Text::sprintf('COM_HYPERPC_AMO_CRM_CONFIGURATION_SAVE_LEAD_NAME', $this->_configuration->getName());
        } else {
            $leadName = Text::_('COM_HYPERPC_AMO_CRM_CONFIGURATION_NATIVE_SAVE_LEAD_NAME');
        }

        $phoneNumber  = $this->_helper->clearMobilePhone($this->getUserPhone());
        if ($phoneNumber && strlen($this->getUserPhone()) > self::MIN_PHONE_STR_LENGTH) {
            $leadName .= '. ' . Text::sprintf('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_PHONE_SHORT', $phoneNumber);
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
     * Check need consulting.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   version
     */
    protected function _isNeedConsulting()
    {
        return $this->_config->find('data.form.need_consulting', false, 'bool');
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
            'tags'          => (array) $this->hyper['helper']['string']->toArray($this->_config->get('tags')),
            'custom_fields' => [
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PRODUCT_NAME_KEY),
                    'values' => [[
                        'value' => $this->_product->get('name')
                    ]]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_CONFIGURATION_ID_KEY),
                    'values' => [[
                        'value' => $this->_configuration->id
                    ]]
                ],
                [
                    'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PRODUCT_KEY),
                    'values' => [[
                        'value' => match ($this->_product->getFolder()->getItemsType()) {
                            'pc'            => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_PC_KEY),
                            'concept'       => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_CONCEPT_KEY),
                            'notebook'      => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_NOTEBOOK_KEY),
                            'workstation'   => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_WORKSTATION_KEY),
                            'server'        => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_SERVER_KEY),
                            'station'       => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_STATION_KEY)
                        }
                    ]]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ROISTAT_KEY),
                    'values' => [[
                        'value' => $this->hyper['input']->cookie->get('roistat_visit', '')
                    ]]
                ]
            ]
        ];

        $gclientid = $this->hyper['helper']['google']->getCID();
        if (!empty($gclientid) && $gclientid !== '0.0') {
            $this->_leadData['custom_fields'][] = [
                'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_GOOGLE_CLIENT_ID_KEY),
                'values' => [[
                    'value' => $gclientid
                ]]
            ];
        }

        $ymuid = $this->hyper['input']->cookie->get('_ym_uid', '');
        $ymCounterId = $this->hyper['params']->get('ym_counter_id', '');
        if (!empty($ymuid) && !empty($ymCounterId)) {
            $this->_leadData['custom_fields'][] = [
                'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_COUNTER_KEY),
                'values' => [[
                    'value' => $ymCounterId
                ]]
            ];

            $this->_leadData['custom_fields'][] = [
                'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_UID_KEY),
                'values' => [[
                    'value' => $ymuid
                ]]
            ];
        }

        $this->_setLeadTags();

        return $this;
    }

    /**
     * Setup lead note.
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _setLeadNote()
    {
        $phoneLength = strlen($this->getUserPhone());

        $this->_leadNote = [
            Text::_('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_FORM_DATA'),
            Text::sprintf('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_SENDER_NAME', $this->getUsername()),
            Text::sprintf('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_RECIPIENT_EMAIL', $this->getUserEmail())
        ];

        $needConsulting    = $this->_config->find('data.form.need_consulting', false, 'bool');
        $needConsultingMsg = Text::_('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_NEED_CONSULTING') . ': ';
        $needConsultingMsg .= $needConsulting ? Text::_('JYES') : Text::_('JNO');

        $this->_leadNote[] = $needConsultingMsg;

        if ($phoneLength > self::MIN_PHONE_STR_LENGTH) {
            $this->_leadNote[] = Text::sprintf('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_PHONE', $this->getUserPhone());
        }

        $this->_leadNote[] = implode("\n", [
            '-----------------------------------',
            Text::_('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_CONFIGURATION_DATA')
        ]);

        $productName = $this->_product->getName();
        $quantity = $this->_product->quantity;

        if ($quantity > 1) {
            $productName = $quantity . ' x ' . $productName;
        }

        $price = $this->_product->getListPrice();

        $this->_leadNote[] = $productName . ' ' . Text::_('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_BY_PRICE') . ' ' . $price->text();

        $this->_helper->addLeadNoteTextByProductConfiguration($this->_product, $this->_leadNote);

        if ($quantity > 1) {
            $this->_leadNote[] = '   ' . Text::_('COM_HYPERPC_TOTAL') . ': ' . $quantity . ' x ' . $price->text() .
                ' = ' . $price->multiply($quantity, true)->text();
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
        if ($this->_isNeedConsulting()) {
            $this->_leadData['tags'][] = utf8_strtolower(Text::_('HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_NEED_CONSULTING'));
        }

        $phoneLength = strlen($this->getUserPhone());
        if ($phoneLength <= self::MIN_PHONE_STR_LENGTH) {
            $this->_leadData['tags'][] = Text::_('COM_HYPERPC_CRM_TAG_CONFIGURATION_WITHOUT_PHONE');
        }

        return $this;
    }

    /**
     * Setup product.
     *
     * @param   ProductMarker  $product
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
            'custom_fields' => $this->_helper->getContactCustomFields(
                $this->getUserEmail(),
                $this->getUserPhone()
            )
        ];

        $newContactBody = $this->_helper->updateContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }
}
