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

namespace HYPERPC\Helper;

use JBZoo\Utils\Url;
use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use Cake\Utility\Hash;
use JBZoo\Utils\Filter;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Http\Http;
use HYPERPC\Joomla\Factory;
use Joomla\Filesystem\Path;
use Joomla\CMS\Http\Response;
use HYPERPC\Elements\Element;
use HYPERPC\ORM\Entity\Plugin;
use Joomla\CMS\Filesystem\File;
use Joomla\String\StringHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Helper\Traits\CrmOauth;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Status;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use Joomla\CMS\Language\LanguageFactoryInterface;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * AmoCRM Helper.
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class CrmHelper extends AppHelper
{
    use CrmOauth;

    const AUTH_METHOD_HASH  = 'hash';
    const AUTH_METHOD_OAUTH = 'oauth';

    //  CRM API paths.
    const API_PATH_LEADS                    = '/api/v2/leads';
    const API_PATH_CONTACTS                 = '/api/v2/contacts';
    const API_PATH_COMPANIES                = '/api/v2/companies';
    const API_PATH_NOTES                    = '/api/v2/notes';
    const API_PATH_PIPELINES                = '/api/v2/pipelines';
    const API_PATH_COMPANIES_CUSTOM_FIELDS  = '/api/v4/companies/custom_fields';
    const API_PATH_CONTACTS_CUSTOM_FIELDS   = '/api/v4/contacts/custom_fields';
    const API_PATH_EVENTS                   = '/api/v4/events';
    const API_PATH_LEADS_CUSTOM_FIELDS      = '/api/v4/leads/custom_fields';
    const API_PATH_USERS                    = '/api/v4/users';

    const MIN_PHONE_STR_LENGTH = 4;

    //  Element entity types.
    const ELEMENT_TYPE_CONTACT  = 1; // not used
    const ELEMENT_TYPE_LEAD     = 2;
    const ELEMENT_TYPE_COMPANY  = 3; // not used
    const ELEMENT_TYPE_TASK     = 4; // not used
    const ELEMENT_TYPE_BUYER    = 12; // not used

    //  Note event types.
    const NOTE_EVENT_DEAL_CREATED           = 1; // not used
    const NOTE_EVENT_CONTACT_CREATED        = 2; // not used
    const NOTE_EVENT_DEAL_STATUS_CHANGED    = 3; // not used
    const NOTE_EVENT_DEAL_COMMON            = 4;
    const NOTE_EVENT_DEAL_CALL_IN           = 10; // not used
    const NOTE_EVENT_DEAL_CALL_OUT          = 11; // not used
    const NOTE_EVENT_DEAL_COMPANY_CREATED   = 12; // not used
    const NOTE_EVENT_DEAL_TASK_RESULT       = 13; // not used
    const NOTE_EVENT_DEAL_SYSTEM            = 25;
    const NOTE_EVENT_DEAL_SMS_IN            = 102; // not used
    const NOTE_EVENT_DEAL_SMS_OUT           = 103; // not used

    const PIPELINES_TMP_FILE = 'amo_pipelines.json';

    const LEAD_FINISH_STATUS_ID   = 143;
    const LEAD_FINISH_STATUS_2_ID = 142;

    //  Custom field keys
    const LEAD_FIELD_COMMENT_KEY                        = 'comment';
    const LEAD_FIELD_PROMOCODE_KEY                      = 'promocode';
    const LEAD_FIELD_ORDER_ID_KEY                       = 'order_id';
    const LEAD_FIELD_CONFIGURATION_ID_KEY               = 'configuration_id';
    const LEAD_FIELD_DELIVERY_ADDRESS_KEY               = 'delivery_address';
    const LEAD_FIELD_MOYSKLAD_ORDER_URL_KEY             = 'moysklad_order_url';
    const LEAD_FIELD_MOYSKLAD_SYNC_KEY                  = 'moysklad_sync';
    const LEAD_FIELD_COMMERCIAL_PROPOSAL_CREATED_KEY    = 'commercial_proposal_created';
    const LEAD_FIELD_COMMERCIAL_PROPOSAL_URL_KEY        = 'commercial_proposal_url';
    const LEAD_FIELD_ISSUE_KEY                          = 'issue';
    const LEAD_FIELD_PURCHASE_PURPOSE_KEY               = 'purchase_purpose';
    const LEAD_FIELD_READY_TO_PAY_KEY                   = 'ready_to_pay';
    const LEAD_FIELD_PRODUCT_NAME_KEY                   = 'product_name';

    const LEAD_FIELD_BUYER_TYPE_KEY             = 'buyer_type';
    const LEAD_FIELD_BUYER_TYPE_INDIVIDUAL_KEY  = 'buyer_type_individual';
    const LEAD_FIELD_BUYER_TYPE_LEGAL_KEY       = 'buyer_type_legal';

    const LEAD_FIELD_PAYMENT_KEY                = 'payment';
    const LEAD_FIELD_PAYMENT_SPOT_KEY           = 'payment_spot';
    const LEAD_FIELD_PAYMENT_INVOICE_KEY        = 'payment_invoice';
    const LEAD_FIELD_PAYMENT_CARD_KEY           = 'payment_card';
    const LEAD_FIELD_PAYMENT_CREDIT_KEY         = 'payment_credit';
    const LEAD_FIELD_PAYMENT_BEZNAL_KEY         = 'payment_beznal';

    const LEAD_FIELD_PRODUCT_KEY                = 'product';
    const LEAD_FIELD_PRODUCT_ACCESSORY_KEY      = 'product_accessory';
    const LEAD_FIELD_PRODUCT_CONCEPT_KEY        = 'product_concept';
    const LEAD_FIELD_PRODUCT_NOTEBOOK_KEY       = 'product_notebook';
    const LEAD_FIELD_PRODUCT_PC_KEY             = 'product_pc';
    const LEAD_FIELD_PRODUCT_SERVER_KEY         = 'product_server';
    const LEAD_FIELD_PRODUCT_STATION_KEY        = 'product_station';
    const LEAD_FIELD_PRODUCT_WORKSTATION_KEY    = 'product_workstation';

    const LEAD_FIELD_DELIVERY_KEY               = 'delivery';
    const LEAD_FIELD_DELIVERY_PICKUP_KEY        = 'delivery_pickup';
    const LEAD_FIELD_DELIVERY_SHIPPING_KEY      = 'delivery_shipping';

    const LEAD_FIELD_YM_COUNTER_KEY             = 'ym_counter';
    const LEAD_FIELD_YM_UID_KEY                 = 'ym_uid';
    const LEAD_FIELD_GOOGLE_CLIENT_ID_KEY       = 'google_client_id';
    const LEAD_FIELD_USER_ID_KEY                = 'user_id';
    const LEAD_FIELD_ROISTAT_KEY                = 'roistat';

    const CONTACT_FIELD_EMAIL_KEY               = 'contact_email';
    const CONTACT_FIELD_PHONE_KEY               = 'contact_phone';

    const COMPANY_FIELD_INN_KEY                 = 'company_inn';

    /**
     * Payment type list id.
     *
     * @var     int[]
     *
     * @since   2.0
     */
    protected static $_paymentTypes = [];

    /**
     * Map of pipeline statuses.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_pipeStatusesMap = [];

    /**
     * API login.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_login;

    /**
     * API hash key.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_hash;

    /**
     * API cookie file path.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_cookieFile;

    /**
     * Settings for crm integration
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected static JSON $_settings;

    /**
     * Language for transferring data to crm
     *
     * @var     Language
     *
     * @since   2.0
     */
    protected static $_language;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        if (!isset(static::$_settings)) {
            $crmPlugin = new Plugin((array) PluginHelper::getPlugin('system', 'crm'));
            if (!$crmPlugin->id) {
                throw new \Exception('Failed to initialize CrmHelper. Crm plugin not found.', 500);
            }

            static::$_settings = $crmPlugin->params;

            $langTag = static::$_settings->get('language', 'en-GB');
            self::$_language = Factory::getContainer()->get(LanguageFactoryInterface::class)->createLanguage($langTag);

            if ($this->hyper['cms']->isClient('site')) {
                self::$_language->load(HP_OPTION, Path::clean(JPATH_SITE . '/components/' . HP_OPTION));
            } elseif ($this->hyper['cms']->isClient('administrator')) {
                self::$_language->load(HP_OPTION, Path::clean(JPATH_ADMINISTRATOR . '/components/' . HP_OPTION));
            }
        }

        $this->_setPipelineStatusesMap();

        self::$_paymentTypes = [
            'spot'    => $this->getEnumId(self::LEAD_FIELD_PAYMENT_SPOT_KEY),
            'invoice' => $this->getEnumId(self::LEAD_FIELD_PAYMENT_INVOICE_KEY),
            'card'    => $this->getEnumId(self::LEAD_FIELD_PAYMENT_CARD_KEY),
            'credit'  => $this->getEnumId(self::LEAD_FIELD_PAYMENT_CREDIT_KEY),
            'beznal'  => $this->getEnumId(self::LEAD_FIELD_PAYMENT_BEZNAL_KEY)
        ];
    }

    /**
     * Get language for transferring data to crm
     *
     * @return  Language
     */
    public function getCrmLanguage()
    {
        return self::$_language;
    }

    /**
     * Get custom field id by settings key
     *
     * @param   string $fieldKey use class constants as key
     *
     * @return  int
     */
    public function getCustomFieldId($fieldKey)
    {
        return static::$_settings->get('custom_field_' . $fieldKey, 0, 'int');
    }

    /**
     * Get custom field enum id by settings key
     *
     * @param   string $enumKey use class constants as key
     *
     * @return  int
     */
    public function getEnumId($enumKey)
    {
        return static::$_settings->get('custom_field_enum_value_' . $enumKey, 0, 'int');
    }

    /**
     * Get oAuth dir path
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOauthDirPath()
    {
        $settings = static::$_settings;
        $platform = $settings->get('platform', 'amocrm');
        $subdomain = $settings->get('subdomain', 'hyperpc');

        return Path::clean(JPATH_ROOT . "/hyperpc/{$platform}/{$subdomain}/");
    }

    /**
     * Get oAuth state hash
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOauthStateHash()
    {
        return md5(static::$_settings->write());
    }

    /**
     * Get AMO status events for order.
     *
     * @param   Order  $order
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getEventsByOrder(Order $order)
    {
        if (!$order->getAmoLeadId()) {
            return [];
        }

        $apiQuery = [
            'filter' => [
                'entity'    => ['lead'],
                'entity_id' => $order->getAmoLeadId(),
                'type'      => ['lead_status_changed']
            ]
        ];

        try {
            $response = $this->_getHttpRequestGet(self::API_PATH_EVENTS . '?' . http_build_query($apiQuery));
            if ($response->code === 200) {
                $returnStatus = [];
                $responseBody = new JSON($response->body);
                $siteStatus   = $this->hyper['helper']['status']->getStatusList();

                $amoCrmStatusList = (array) $responseBody->find('_embedded.events');
                $amoCrmStatusList = Hash::sort($amoCrmStatusList, '{n}.created at', 'desc');

                /** @var Status $_status */
                $siteStatusListForAmo = new JSON();
                foreach ($siteStatus as $_status) {
                    if ($this->hyper->isSiteContext('hyperpc')) {
                        if (preg_match('/^E/', $_status->name)) {
                            continue;
                        }
                    }

                    $_siteStatusKey = $_status->pipeline_id . ':' . (int) $_status->getAmoStatusId();
                    $statusData = $siteStatusListForAmo->get($_siteStatusKey, $_status);
                    $siteStatusListForAmo->set($_siteStatusKey, $_status);
                }

                foreach ($amoCrmStatusList as $amoCrmStatus) {
                    $amoCrmStatus = new JSON($amoCrmStatus);
                    $statusId     = $amoCrmStatus->find('value_after.0.lead_status.id');
                    $pipelineId   = $amoCrmStatus->find('value_after.0.lead_status.pipeline_id');

                    $siteStatusKey = $pipelineId . ':' . $statusId;

                    if ($siteStatusListForAmo->get($siteStatusKey)) {
                        $_siteStatus = new Status($siteStatusListForAmo->get($siteStatusKey));

                        if ($amoCrmStatus->get('created_at')) {
                            $_siteStatus->params->set('timestamp', $amoCrmStatus->get('created_at'));
                        }

                        $returnStatus[] = $_siteStatus;
                    }
                }

                return $returnStatus;
            }

            return [];
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * Check is debug mode.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDebug()
    {
        return Filter::bool($this->hyper['params']->get('amo_crm_debug_mode', false));
    }

    /**
     * Get lead cart url.
     *
     * @param   int|string $leadId
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getLeadUrl($leadId)
    {
        return $this->_getCrmSiteUrl() . '/leads/detail/' . $leadId;
    }

    /**
     * Get amo lead id from lead url
     *
     * @param   string $leadUrl
     *
     * @return  int|false
     *
     * @since   2.0
     */
    public function getLeadIdFromUrl($leadUrl)
    {
        $path = parse_url($leadUrl, PHP_URL_PATH);
        if (empty($path)) {
            return false;
        }

        $leadId = preg_replace('/^\/leads\/detail\//', '', $path);
        if (is_numeric($leadId)) {
            return (int) $leadId;
        }

        return false;
    }

    /**
     * Get full pipeline file path.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTmpPipelineFilePath()
    {
        $dirPath  = $this->getOauthDirPath();
        $filePath = Path::clean($dirPath . self::PIPELINES_TMP_FILE);

        if (!Folder::exists($dirPath)) {
            Folder::create($dirPath);
        }

        return $filePath;
    }

    /**
     * Get pipelines data from AmoCrm tmp file.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getPipelineTmpData()
    {
        static $data;

        if (!$data) {
            $json     = '{}';
            $filePath = $this->getTmpPipelineFilePath();
            if (File::exists($filePath)) {
                $json = (string) file_get_contents($filePath);
            }

            $data = new JSON($json);
        }

        return $data;
    }

    /**
     * Add note.
     *
     * @param   int     $elementId
     * @param   string  $text
     * @param   int     $noteType
     * @param   int     $elementType
     * @param   string  $userId
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function addNote($elementId, $text, $noteType = self::NOTE_EVENT_DEAL_COMMON, $elementType = self::ELEMENT_TYPE_LEAD, $userId = '')
    {
        $addData = [
            'text'                  => $text,
            'created_at'            => time(),
            'responsible_user_id'   => $userId,
            'note_type'             => $noteType,
            'element_id'            => $elementId,
            'element_type'          => $elementType
        ];

        if ($noteType === self::NOTE_EVENT_DEAL_SYSTEM) {
            $details = explode(':', $text, 2);
            if (count($details) === 2) {
                $addData['params'] = [
                    'text'    => $details[1],
                    'service' => $details[0]
                ];
            }
        }

        $data = ['add' => [$addData]];

        $response = $this->_getHttpRequestPost(self::API_PATH_NOTES, $data);

        return new JSON($response->body);
    }

    /**
     * Add company.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2,0
     */
    public function addCompany($data)
    {
        $data = ['add' => $data];

        $response = $this->_getHttpRequestPost(self::API_PATH_COMPANIES, $data);

        return new JSON($response->body);
    }

    /**
     * Add contact.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function addContact($data)
    {
        return $this->_contact($data);
    }

    /**
     * Update contact.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function updateContact($data)
    {
        return $this->_contact($data, 'update');
    }

    /**
     * Find all open user leads.
     *
     * @param   string $phone
     * @param   string $email
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function findAllUserOpenLeads($phone, $email)
    {
        $leadsByPhone = $this->findOpenUserLeads(
            $this->findLeadsByPhone($phone)
        );

        if (count($leadsByPhone)) {
            return $leadsByPhone;
        }

        return $this->findOpenUserLeads(
            $this->findLeadsByEmail($email)
        );
    }

    /**
     * Contact add or update.
     *
     * @param   array   $data
     * @param   string  $event
     *
     * @return  JSON
     *
     * @since   2.0
     */
    protected function _contact($data, $event = 'add')
    {
        $data = [$event => $data];

        $response = $this->_getHttpRequestPost(self::API_PATH_CONTACTS, $data);

        return new JSON($response->body);
    }

    /**
     * Add new lead.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function addLead($data)
    {
        return $this->_lead($data);
    }

    /**
     * Update lead.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function updateLead($data)
    {
        return $this->_lead($data, 'update');
    }

    /**
     * Add tag to lead data array
     *
     * @param   array $data
     * @param   string $tag
     *
     * @since   2.0
     */
    protected function _addTag(array &$data, $tag)
    {
        $data[0]['tags'] = $data[0]['tags'] ?? [];

        if (is_array($data[0]['tags'])) {
            $data[0]['tags'][] = $tag;
        } else {
            $data[0]['tags'] .= ',' . $tag;
        }
    }

    /**
     * Add lead.
     *
     * @param   array $data
     * @param   string $event
     *
     * @return  JSON
     *
     * @since   2.0
     */
    protected function _lead($data, $event = 'add')
    {
        $data[0]['tags'] = $data[0]['tags'] ?? [];

        if ($data[0]['tags'] === false) {
            unset($data[0]['tags']); // Don't update tags if set to false
        } else {
            $context = StringHelper::strtoupper($this->hyper->getContext());
            $this->_addTag($data, $context);

            if ($this->hyper->getLanguageCode() === 'ar-AA') {
                $this->_addTag($data, 'Arab');
            }
        }

        $data = [$event => $data];

        $response = $this->_getHttpRequestPost(self::API_PATH_LEADS, $data);

        return new JSON($response->body);
    }

    /**
     * Get crm lead by id.
     *
     * @param   int $id
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getLeadById($id)
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_LEADS . '?' . Url::build(['id' => $id]));

        return new JSON((new JSON($response->body))->find('_embedded.items.0'));
    }

    /**
     * Get company custom fields list.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getCompanyCustomFieldsList()
    {
        return $this->_getCustomFields(self::API_PATH_COMPANIES_CUSTOM_FIELDS);
    }

    /**
     * Get contact custom fields list.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getContactCustomFieldsList()
    {
        return $this->_getCustomFields(self::API_PATH_CONTACTS_CUSTOM_FIELDS);
    }

    /**
     * Get lead custom fields list.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getLeadCustomFieldsList()
    {
        return $this->_getCustomFields(self::API_PATH_LEADS_CUSTOM_FIELDS);
    }

    /**
     * Get amo crm user id.
     *
     * @param   int $id
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getUser($id)
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_USERS . '/' . $id . '?with=group');
        return new JSON($response->body);
    }

    /**
     * Get auth method
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getAuthMethod()
    {
        return static::$_settings->get('auth_method', self::AUTH_METHOD_HASH);
    }

    /**
     * Get crm lead by id.
     *
     * @param   array $data
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getLeadByQuery(array $data)
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_LEADS . '?' . Url::build($data));

        return (array) (new JSON($response->body))->find('_embedded.items');
    }

    /**
     * Get leads pipelines
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getLeadsPipelines()
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_PIPELINES);

        return new JSON($response->body);
    }

    /**
     * Get contact list.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getContacts(array $data = [])
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_CONTACTS . '?' . Url::build($data));

        return new JSON($response->body);
    }

    /**
     * Get company list.
     *
     * @param   array $data
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getCompanies(array $data = [])
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_COMPANIES . '?' . Url::build($data));

        return new JSON($response->body);
    }

    /**
     * Find contact by mobile phone.
     *
     * @param   string $phone
     * @param   array $contacts
     *
     * @return  mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function findContactByMobilePhone($phone, array $contacts = [])
    {
        $contactId   = null;
        $findContact = false;
        $phone       = $this->clearMobilePhone($phone);

        if (count($contacts)) {
            foreach ($contacts as $contact) {
                if ($findContact) {
                    break;
                }

                $contact = new JSON($contact);
                foreach ((array)$contact->get('custom_fields') as $field) {
                    $field = new JSON($field);
                    if ($field->get('id', 0, 'int') === $this->getCustomFieldId(self::CONTACT_FIELD_PHONE_KEY)) {
                        $findPhone = $this->clearMobilePhone($field->find('values.0.value'));
                        if ($phone === $findPhone) {
                            $findContact = true;
                            $contactId = $contact->get('id');
                        }
                    }
                }
            }
        }

        return $contactId;
    }

    /**
     * Find open user leads.
     *
     * @param   array   $leads
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function findOpenUserLeads(array $leads)
    {
        $return = [];
        if (count($leads)) {
            foreach ($leads as $lead) {
                $lead = new JSON($lead);
                if (!in_array($lead->get('status_id', 0, 'int'), [
                        self::LEAD_FINISH_STATUS_ID, self::LEAD_FINISH_STATUS_2_ID
                    ]) &&
                    !key_exists($lead->get('id'), $return)
                ) {
                    $isOrderLead = false;

                    //  Check is order lead.
                    $customFields = (array) $lead->get('custom_fields');
                    if (count($customFields)) {
                        foreach ($customFields as $customFieldData) {
                            $customField = new JSON($customFieldData);
                            $fieldId = $customField->find('id', 0, 'int');
                            if ($fieldId === $this->getCustomFieldId(self::LEAD_FIELD_MOYSKLAD_ORDER_URL_KEY)) {
                                if (!empty($customField->find('values.0.value'))) {
                                    $isOrderLead = true;
                                    break;
                                }
                            } elseif ($fieldId === $this->getCustomFieldId(self::LEAD_FIELD_ORDER_ID_KEY)) {
                                if ($customField->find('values.0.value')) {
                                    $isOrderLead = true;
                                    break;
                                }
                            }
                        }
                    }

                    if (!$isOrderLead) {
                        $return[$lead->get('id')] = $lead;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Find all leads by mobile phone.
     *
     * @param   string $phone
     *
     * @return  array
     *
     * @since   2.0
     *
     * @todo    using $this->clearMobilePhone for clean phone number
     */
    public function findLeadsByPhone($phone)
    {
        if (!$this->hyper['helper']['string']->isValidPhone((string) $phone)) {
            return [];
        }

        $phone = str_replace([' ', '-', '(', ')'], '', $phone);

        return $this->getLeadByQuery(['query' => $phone]);
    }

    /**
     * Find all leads by email.
     *
     * @param   string $email
     *
     * @return  array
     *
     * @since   2.0
     */
    public function findLeadsByEmail($email)
    {
        if (!$this->hyper['helper']['string']->isValidEmail((string) $email)) {
            return [];
        }

        return $this->getLeadByQuery(['query' => $email]);
    }

    /**
     * Find contact by email.
     *
     * @param   string $email
     * @param   array $contacts
     *
     * @return  mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function findContactByEmail($email, array $contacts = [])
    {
        $contactId   = null;
        $findContact = false;

        if (count($contacts)) {
            foreach ($contacts as $contact) {
                if ($findContact) {
                    break;
                }

                $contact = new JSON($contact);
                foreach ((array)$contact->get('custom_fields') as $field) {
                    $field = new JSON($field);
                    if ($field->get('id', 0, 'int') === $this->getCustomFieldId(self::CONTACT_FIELD_EMAIL_KEY)) {
                        if ($email === $field->find('values.0.value')) {
                            $findContact = true;
                            $contactId = $contact->get('id');
                        }
                    }
                }
            }
        }

        return $contactId;
    }

    /**
     * Clear mobile phone.
     *
     * @param   string $phone
     * @return  bool|string
     *
     * @since   2.0
     *
     * @todo    Check phone length. Now it cuts first digit of country code
     */
    public function clearMobilePhone($phone)
    {
        return substr(preg_replace("/[^0-9]/", '', $phone), -10);
    }

    /**
     * Get contact by id.
     *
     * @param   int $id
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getContact($id)
    {
        $response = $this->_getHttpRequestGet(self::API_PATH_CONTACTS . '?' . Url::build(['id' => $id]));

        return new JSON($response->body);
    }

    /**
     * Find site status by amo crm pipeline status.
     *
     * @param   int  $pipelineStatus
     * @param   null $pipelineId
     * @return  array
     *
     * @since   2.0
     */
    public function findSiteStatusByPipelineStatus($pipelineStatus, $pipelineId)
    {
        $pipelineStatus = Filter::int($pipelineStatus);

        foreach ($this->_pipeStatusesMap as $_pipelineId => $statues) {
            foreach ($statues as $pipeStatusId => $siteStatusId) {
                if ($pipelineStatus === $pipeStatusId && (int) $_pipelineId === (int) $pipelineId) {
                    return [(int) $_pipelineId, $pipeStatusId, $siteStatusId];
                }
            }
        }

        return [];
    }

    /**
     * Get manager id by name.
     *
     * @param   Order $order
     *
     * @return  array|mixed|null
     *
     * @since   2.0
     */
    public function getManagerCrmIdByName(Order $order)
    {
        $worker = $order->getWorker();

        if ($worker->id) {
            return $worker->params->get('amo_responsible_user_id', 0, 'int');
        }

        return null;
    }

    /**
     * Get manager list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getManagerList()
    {
        return $this->hyper['helper']['worker']->getWorkers();
    }

    /**
     * Get payment CRM id.
     *
     * @param   Order $order
     *
     * @return  int|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCrmPaymentTypeId(Order $order)
    {
        $payment = $order->getPayment();
        if ($payment !== null && key_exists($payment->getType(), self::$_paymentTypes)) {
            return self::$_paymentTypes[$payment->getType()];
        }

        return null;
    }

    /**
     * Get order payment type by crm id.
     *
     * @param   int $crmId
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOrderPaymentTypeByCrmId($crmId)
    {
        $type = '';
        $crmId = (int) $crmId;
        foreach (self::$_paymentTypes as $key => $id) {
            if ($id === $crmId) {
                $type = $key;
                break;
            }
        }

        return $type;
    }

    /**
     * Updates order delivery data by lead customFields.
     * Not used
     *
     * @param   Order $order
     * @param   array $customFields
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function updateOrderDeliveryData(Order &$order, array $customFields = [])
    {
        $order->getDelivery()->bindAmoData($order, $customFields);
        return $this;
    }

    /**
     * Update order worker by AmoCRM responsible user id.
     *
     * @param   Order $order
     * @param   int $responsibleUserId
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function updateOrderWorkerByResponsibleUserId(Order &$order, int $responsibleUserId)
    {
        $currentWorkerId = $order->worker_id;
        /** @var \HYPERPC\Joomla\Model\Entity\Worker $worker */
        foreach ($this->getManagerList() as $worker) {
            $crmId = $worker->params->get('amo_responsible_user_id', 0, 'int');
            if ($crmId === $responsibleUserId) {
                $order->set('worker_id', $worker->id);

                // Set manager in MoySklad customer order if changed
                if ($currentWorkerId !== $worker->id) {
                    $orderUuid = $order->getUuid();
                    if (!empty($orderUuid)) {
                        $this->hyper['helper']['moyskladCustomerOrder']->updateManagerField($orderUuid, $worker->name);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Update order status by amo lead status.
     *
     * @param   Order       $order
     * @param   int         $amoStatusId
     * @param   int|null    $pipelineId
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function updateOrderStatusByAmoStatusId(Order &$order, $amoStatusId, $pipelineId)
    {
        $statusData = $this->findSiteStatusByPipelineStatus($amoStatusId, $pipelineId);
        if (count($statusData)) {
            list(, , $siteStatusId) = $statusData;

            if ($order->status !== $siteStatusId) {
                $history = (array) $order->status_history->getArrayCopy();

                $history[] = [
                    'statusId'  => $siteStatusId,
                    'timestamp' => time()
                ];

                $order->set('status', $siteStatusId);
                $order->set('status_history', new JSON($history));
            }
        }

        return $this;
    }

    /**
     * Set order status by amo lead status.
     *
     * @param   Order       $order
     * @param   int         $amoStatusId
     * @param   int|null    $pipelineId
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setOrderStatusByAmoStatusId(Order &$order, $amoStatusId, $pipelineId)
    {
        $statusData = $this->findSiteStatusByPipelineStatus($amoStatusId, $pipelineId);
        if (count($statusData)) {
            list(, , $siteStatusId) = $statusData;

            if ($order->status !== $siteStatusId) {
                $history[] = [
                    'statusId'  => $siteStatusId,
                    'timestamp' => time()
                ];

                $order->set('status', $siteStatusId);
                $order->set('status_history', new JSON($history));
            }
        }

        return $this;
    }

    /**
     * Update order data by AmoCRM custom fields.
     *
     * @param   Order   $order
     * @param   array   $customFields
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function updateOrderDataByCustomFields(Order &$order, $customFields = [])
    {
        foreach ($customFields as $customField) {
            $customField = new Data($customField);
            $fieldId     = $customField->get('id', 0, 'int');
            $fieldValue  = $customField->find('values.0.value');
            $fieldEnum   = $customField->find('values.0.enum', 0, 'int');

            if ($fieldId === $this->getCustomFieldId(self::LEAD_FIELD_PAYMENT_KEY)) {
                $newPaymentValue = $this->getOrderPaymentTypeByCrmId($fieldEnum);
                if (!empty($newPaymentValue)) {
                    $order->elements->set('payments', ['value' => $newPaymentValue]);
                    $order->set('payment_type', $newPaymentValue);
                }
            } elseif ($fieldId === $this->getCustomFieldId(self::LEAD_FIELD_DELIVERY_ADDRESS_KEY)) {
                $order->elements->set('address', ['value' => $fieldValue]);
            } elseif ($fieldId === $this->getCustomFieldId(self::LEAD_FIELD_BUYER_TYPE_KEY)) {
                $orderMethod = Order::BUYER_TYPE_INDIVIDUAL;

                $fieldEnum = $customField->find('values.enum');
                if (!$fieldEnum) {
                    $fieldEnum = $customField->find('values.0.enum');
                }

                if ($this->getEnumId(self::LEAD_FIELD_BUYER_TYPE_LEGAL_KEY) === (int) $fieldEnum) {
                    $orderMethod = Order::BUYER_TYPE_LEGAL;
                }

                if ($fieldEnum !== null) {
                    $order->elements->set('methods', ['value' => $orderMethod]);
                }
            }
        }

        return $this;
    }

    /**
     * Add lead note information by product configuration.
     *
     * @param   ProductMarker   $product
     * @param   array           $note
     * @param   bool            $includeExternal
     *
     * @return  $this
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function addLeadNoteTextByProductConfiguration(ProductMarker $product, array &$note, $includeExternal = false)
    {
        $partOrder = $this->hyper['params']->get('product_teaser_parts_order', 'a.product_folder_id ASC');
        $parts     = $product->getConfigParts(true, $partOrder, true, (bool) $product->saved_configuration);

        if (!$includeExternal) {
            foreach ($parts as $groupId => $group) {
                /** @var PartMarker|MoyskladService $part */
                foreach ($group as $key => $part) {
                    if ($part->isDetached()) {
                        unset($parts[$groupId][$key]);
                    }
                }
            }
        }

        if (count($parts)) {
            $groups = [];
            $parents = [];
            foreach ($parts as $groupId => $groupParts) {
                /** @var PartMarker|MoyskladService $part */
                foreach ($groupParts as $part) {
                    if (!isset($groups[$groupId])) {
                        $groups[$groupId] = $part->getFolder();
                    }

                    $group = $groups[$groupId];
                    if (!isset($parents[$group->parent_id])) {
                        $parents[$group->parent_id] = $this->hyper['helper'][(new \ReflectionClass($group))->getShortName()]->findById($group->parent_id);
                        $parent = $parents[$group->parent_id];
                        $note[] = '-----------------------------------';
                        $note[] = '-' . $parent->title;
                    }

                    $partName = $part->getConfiguratorName($product->id);
                    if ($part->quantity > 1) {
                        $partName = sprintf('%s x %s', $part->quantity, $partName);
                    }

                    if ($part instanceof PartMarker) {
                        if ($part->option instanceof OptionMarker) {
                            $partName .= sprintf(' (%s)', $part->option->getConfigurationName());
                            $part->setListPrice($part->option->getListPrice());
                            $part->setSalePrice($part->option->getSalePrice());
                        }
                    }

                    // Delete if not needed
                    //
                    // /** @var Order $order */
                    // $order = $this->hyper['helper']['order']->findById($product->get('order_id'));
                    // if ($part->isService() && $order->id) {
                    //     $serviceData = new JSON((array) $order->products->find($product->getKey() . '.service.' . $part->group_id));
                    //     if ($serviceData->get('id', 0, 'int') === $part->id) {
                    //         $part->price->set($serviceData->get('price'));
                    //     }
                    // }

                    $partPrice = $part->getListPrice();
                    $promoPrice = $part->getSalePrice();
                    $rateValue = 0;
                    if ($partPrice->compare($promoPrice, '>')) {
                        $partPriceValue = $partPrice->val();
                        $promoPriceValue = $promoPrice->val();
                        $rateValue = 100 - ($promoPriceValue / ($partPriceValue / 100));
                    }

                    if ($part->quantity > 1) {
                        $quantityPrice = $promoPrice->multiply($part->quantity, true);
                        $partName .= ' - ' . $part->quantity . ' x '  . $promoPrice->text() . ' = ' . $quantityPrice->text();
                    } elseif ($promoPrice->val() > 0 || $rateValue > 0) {
                        $partName .= ' - ' . $promoPrice->text();
                    }

                    if ($rateValue) {
                        $partName .= ' ' . sprintf(self::$_language->_('COM_HYPERPC_AMO_CRM_INCLUDING_DISCOUNT'), $rateValue . '%');
                    }

                    if ($part instanceof PartMarker && !$product->isFromStock() && $group->params->get('configurator_divide_by_availability', false, 'bool')) {
                        $availability = $part->option instanceof OptionMarker ? $part->option->getAvailability() : $part->getAvailability();
                        $partName .= ' - ' . self::$_language->_('COM_HYPERPC_AVAILABILITY_LABEL_' . strtoupper($availability));
                    }

                    $note[] = '--' . $group->title . ': ' . $partName;
                }
            }
        }

        return $this;
    }

    /**
     * Add lead note information by order elements.
     *
     * @param   Order $order
     * @param   array $note
     *
     * @return  $this
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function addLeadNoteTextByOrderElements(Order $order, array &$note)
    {
        $elements = $order->getElements();

        $note[] = sprintf(
            self::$_language->_('COM_HYPERPC_AMO_CRM_NOTE_TITLE_FIRST_SAVE_DATA'),
            self::$_language->_('COM_HYPERPC_ORDER_IS_COMPANY_' . $order->getBuyerOrderType())
        );

        if (count($elements)) {
            /** @var Element $element */
            foreach ($elements as $element) {
                $value = $element->getCrmValue();
                if ($value) {
                    if ($element instanceof \ElementOrderYandexDelivery) {
                        $note[] = $element->getAmoCrmNoteText();
                    } else {
                        $note[] = $element->getConfig('name') . ': ' . $element->getCrmValue();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Lead test data.
     *
     * @param   string $file
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getTestData($file = 'lead_update')
    {
        if (!File::getExt($file)) {
            $file .= '.php';
        }

        $data = [];
        $path = Path::clean(HP_REPOSITORY_PATH . '/tmp/amo_crm/' . $file);
        if (File::exists($path)) {
            /** @noinspection PhpIncludeInspection */
            $data = require_once $path;
        }

        return (array) $data;
    }

    /**
     * Get contact custom fields data.
     *
     * @param   string $email
     * @param   string $phone
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getContactCustomFields($email, $phone)
    {
        $customFields = [
            [
                'id'     => $this->getCustomFieldId(self::CONTACT_FIELD_EMAIL_KEY),
                'values' => [
                    [
                        'enum'  => 'WORK',
                        'value' => $email
                    ]
                ]
            ]
        ];

        if (strlen($phone) > self::MIN_PHONE_STR_LENGTH) {
            $customFields[] = [
                'id'     => $this->getCustomFieldId(self::CONTACT_FIELD_PHONE_KEY),
                'values' => [
                    [
                        'enum'  => 'MOB',
                        'value' => $phone
                    ]
                ]
            ];
        }

        return $customFields;
    }

    /**
     * Get lead tags.
     *
     * @param   int $leadId
     * @param   bool $forceGet get tags from lead anyway
     *
     * @return  string[]
     *
     * @since   2.0
     */
    public function getLeadTags($leadId, $forceGet = false)
    {
        static $result = [];

        $leadId = (int) $leadId;

        if (!$forceGet && key_exists($leadId, $result)) {
            return $result[$leadId];
        }

        $lead = $this->getLeadById($leadId);
        $leadTags = [];
        foreach ($lead->get('tags', [], 'arr') as $tag) {
            $tag = new JSON($tag);
            $leadTags[] = $tag->get('name');
        }

        $result[$leadId] = $leadTags;

        return $leadTags;
    }

    /**
     * Get crm site url
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getCrmSiteUrl()
    {
        static $url;

        if (isset($url)) {
            return $url;
        }

        $url = 'https://';

        $url .= static::$_settings->get('subdomain', 'hyperpc') . '.';
        switch (static::$_settings->get('platform', 'amocrm')) {
            case 'kommo':
                $url .= 'kommo.com';
                break;
            default:
                $url .= 'amocrm.ru';
                break;
        }

        return $url;
    }

    /**
     * Get custom fields.
     *
     * @param   string $apiPath
     *
     * @return  JSON
     *
     * @since   2.0
     */
    protected function _getCustomFields($apiPath)
    {
        $response = $this->_getHttpRequestGet($apiPath);

        $body = new JSON($response->body);

        $customFields = $body->find('_embedded.custom_fields', []);

        $pageCount = $body->get('_page_count', 1);
        for ($i = 1; $i < $pageCount; $i++) {
            $nextPageUrl = $body->find('_links.next.href');
            if (empty($nextPageUrl)) {
                continue;
            }

            $uri = Uri::getInstance($nextPageUrl);

            $response = $this->_getHttpRequestGet($uri->getPath() . '?' . $uri->getQuery());
            $body = new JSON($response->body);

            $customFields = array_merge($customFields, $body->find('_embedded.custom_fields', []));
        }

        return new JSON($customFields);
    }

    /**
     * Get http client.
     *
     * @return  Http
     *
     * @since   2.0
     */
    protected function _getHttp()
    {
        $http = HttpFactory::getHttp([], 'curl');

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];

        if ($this->_getAuthMethod() === self::AUTH_METHOD_HASH) {
            static $auth;
            if (!$auth) {
                $this->_cookieFile = Path::clean(JPATH_ROOT . '/tmp/cookie.txt');

                if (!File::exists($this->_cookieFile)) {
                    File::write($this->_cookieFile, null);
                }

                $http->setOption('transport.curl', [
                    CURLOPT_COOKIEFILE => $this->_cookieFile,
                    CURLOPT_COOKIEJAR  => $this->_cookieFile
                ]);

                $authPath = '/private/api/auth.php?type=json';
                $authUrl = $this->_getCrmSiteUrl() . $authPath;
                $auth = $http->post($authUrl, [
                    'USER_LOGIN' => static::$_settings->get('login', ''),
                    'USER_HASH'  => static::$_settings->get('hash', '')
                ]);
            }

            $http->setOption('transport.curl', [
                CURLOPT_COOKIEFILE => $this->_cookieFile,
                CURLOPT_COOKIEJAR  => $this->_cookieFile
            ]);
        } else {
            $accessToken = $this->getAccessToken();
            $headers['Authorization'] = 'Bearer ' . $accessToken;

            $http->setOption('userAgent', 'amoCRM-oAuth-client/1.0');
        }

        $http->setOption('headers', $headers);

        return $http;
    }

    /**
     * Get http request get.
     *
     * @param   string  $path
     *
     * @return  Response
     *
     * @since   2.0
     */
    protected function _getHttpRequestGet($path)
    {
        $url = $this->_getCrmSiteUrl() . '/' . ltrim(Path::clean($path, '/'), '/');

        return $this->_getHttp()->get($url);
    }

    /**
     * Get http request post.
     *
     * @param   string  $path
     * @param   array   $data
     *
     * @return  Response
     *
     * @since   2.0
     */
    protected function _getHttpRequestPost($path, array $data)
    {
        $url = $this->_getCrmSiteUrl() . '/' . ltrim(Path::clean($path, '/'), '/');

        return $this->_getHttp()->post($url, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Setup pipeline statuses map.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setPipelineStatusesMap()
    {
        $_map       = [];
        $statusList = $this->hyper['helper']['status']->getStatusList();

        /** @var Status $status */
        foreach ($statusList as $status) {
            $amoStatusId = Filter::int($status->params->get('amo_status_id'));
            if (!isset($_map[$status->pipeline_id][$amoStatusId])) {
                $_map[$status->pipeline_id][$amoStatusId] = $status->id;
            }
        }

        $this->_pipeStatusesMap = $_map;
    }
}
