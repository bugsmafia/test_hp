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

use HYPERPC\App;
use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use HYPERPC\ORM\Entity\User;
use HYPERPC\Helper\CrmHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/**
 * Class UserLibAmoCrm
 *
 * @since       2.0
 */
class UserLibAmoCrm
{

    const MIN_PHONE_STR_LENGTH = 4;

    /**
     * Hold CrmHelper object.
     *
     * @var     CrmHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Hold hyper application.
     *
     * @var     App
     *
     * @since   2.0
     */
    protected $_hyper;

    /**
     * Hold plugin params.
     *
     * @var     Registry
     *
     * @since   2.0
     */
    protected $_params;

    /**
     * Hold user entity object.
     *
     * @var     User
     *
     * @since   2.0
     */
    protected $_user;

    /**
     * UserLibAmoCrm constructor.
     *
     * @param   int         $userId
     * @param   Registry    $params
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function __construct($userId, Registry $params)
    {
        $this->_params = $params;
        $this->_hyper  = App::getInstance();
        $this->_user   = $this->_hyper['helper']['user']->findById($userId, ['load_fields' => true]);
        $this->_helper = $this->_hyper['helper']['crm'];
    }

    /**
     * Send request to AmoCRM.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function sendToAmo()
    {
        $sendToAmo = Filter::bool($this->_params->get('send_to_amo', true));
        if ($this->_user->id && $sendToAmo) {
            $phoneNumber  = $this->_helper->clearMobilePhone($this->_user->getField('phone')->get('value'));
            $contactsBody = $this->_helper->getContacts(['query' => $this->_user->email]);
            $contacts     = (array) $contactsBody->find('_embedded.items');
            $contactId    = $this->_helper->findContactByEmail($this->_user->email, $contacts);

            if ($contactId === null) {
                $contactId = $this->_addAmoCrmContact();
            } else {
                $this->_updateAmoCrmContact($contactId);
            }

            $leadName = Text::sprintf('COM_HYPERPC_CRM_LEAD_NEW_NAME', $this->_user->email);
            if ($phoneNumber && strlen($phoneNumber) > self::MIN_PHONE_STR_LENGTH) {
                $leadName = Text::sprintf('COM_HYPERPC_CRM_LEAD_NEW_NAME', $phoneNumber);
            }

            $this->_requestLead($this->_getDefaultLeadData($leadName, $contactId));
        }
    }

    /**
     * Send request to create contact.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _addAmoCrmContact()
    {
        $contactData = [
            'created_at'    => time(),
            'name'          => $this->_user->name,
            'custom_fields' => $this->_getContactCustomFields()
        ];

        $newContactBody = $this->_helper->addContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }

    /**
     * Get contact custom field data.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getContactCustomFields()
    {
        $customFields = [
            [
                'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_EMAIL_KEY),
                'values' => [
                    [
                        'enum'  => 'WORK',
                        'value' => $this->_user->email
                    ]
                ]
            ]
        ];

        $phoneNumber = $this->_user->getField('phone')->get('value');

        if (strlen($phoneNumber) > self::MIN_PHONE_STR_LENGTH) {
            $customFields[] = [
                'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_PHONE_KEY),
                'values' => [
                    [
                        'enum'  => 'MOB',
                        'value' => $phoneNumber
                    ]
                ]
            ];
        }

        return $customFields;
    }

    /**
     * Get default AmoCrm lead data.
     *
     * @param   string  $leadName
     * @param   int     $contactId
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getDefaultLeadData($leadName, $contactId)
    {
        $defaultPipeline = $this->_params->get('default_pipeline', ':');
        $pipelineDefault = ($defaultPipeline) ? $defaultPipeline : ':';

        list ($pipeline, $statusId) = explode(':', $pipelineDefault);

        return [
            'created_at'    => time(),
            'pipeline_id'   => $pipeline,
            'name'          => $leadName,
            'status_id'     => $statusId,
            'contacts_id'   => $contactId,
            'tags'          => (array) $this->_hyper['helper']['string']->toArray($this->_params->get('tags')),
            'custom_fields' => [
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ROISTAT_KEY),
                    'values' => [
                        [
                            'value' => $this->_hyper['input']->cookie->get('roistat_visit', '')
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_GOOGLE_CLIENT_ID_KEY),
                    'values' => [
                        [
                            'value' => $this->_hyper['helper']['google']->getCID()
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * Send lead request to AmoCRM.
     *
     * @param   array $leadData
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _requestLead(array $leadData)
    {
        $userLeads = $this->_helper->findAllUserOpenLeads(
            $this->_user->getField('phone')->get('value'),
            $this->_user->email
        );

        $userFirstOpenLead = current($userLeads);

        if ($userFirstOpenLead instanceof JSON) {
            $leadId = $userFirstOpenLead->get('id');
            if (!in_array((int) $userFirstOpenLead->get('pipeline_id'), [2315622, 2315625])) {
                $newLeadBody = new JSON((array) Hash::merge(
                    $userFirstOpenLead->getArrayCopy(),
                    $leadData
                ));

                $leadId = $newLeadBody->get('id');

                $this->_helper->updateLead([$newLeadBody->getArrayCopy()]);
            }

            $this->_helper->addNote(
                $leadId,
                Text::sprintf('COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_SITE_LEAD_DOUBLE', $userFirstOpenLead->get('name')),
                CrmHelper::NOTE_EVENT_DEAL_SYSTEM
            );
        } else {
            $newLeadBody = $this->_helper->addLead([$leadData]);
            $leadId = $newLeadBody->find('_embedded.items.0.id');
        }

        $this->_helper->addNote(
            $leadId,
            Text::sprintf('COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_USER_REGISTRATION', $this->_user->name),
            CrmHelper::NOTE_EVENT_DEAL_SYSTEM
        );
    }

    /**
     * Send request to update contact data.
     *
     * @param   int $contactId
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _updateAmoCrmContact($contactId)
    {
        $contactData = [
            'updated_at'    => time(),
            'id'            => $contactId,
            'name'          => $this->_user->name,
            'custom_fields' => $this->_getContactCustomFields()
        ];

        $newContactBody = $this->_helper->updateContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }
}
