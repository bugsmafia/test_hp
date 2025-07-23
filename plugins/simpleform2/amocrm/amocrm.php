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

use HYPERPC\App;
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Joomla\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\CrmHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\IpHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use HYPERPC\Joomla\Model\Entity\Worker;

/**
 * Class plgSimpleform2AmoCRM
 *
 * @since   2.0
 */
class plgSimpleform2AmoCRM extends CMSPlugin
{

    const COUNTER_FILE_NAME = 'counter.json';

    /**
     * Hold Application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Hold CrmHelper object.
     *
     * @var     CrmHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Counter file.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_counterFile;

    /**
     * Hold AmoCRM lead id.
     *
     * @var     mixed
     *
     * @since   2.0
     */
    protected $_amoLeadId;

    /**
     * Hold result.
     *
     * @var     array
     *
     * @since   1.0
     */
    protected static $_formInstances = [];

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  2.0
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor
     *
     * @param   object  &$subject  The object to observe
     * @param   array   $config    An optional associative array of configuration settings.
     *                             Recognized key values include 'name', 'group', 'params', 'language'
     *                             (this list is not meant to be comprehensive).
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(&$subject, $config = [])
    {
        if (!class_exists('HYPERPC\\App')) {
            $bootstrap = JPATH_ADMINISTRATOR . '/components/com_hyperpc/bootstrap.php';
            if (file_exists($bootstrap)) {
                /** @noinspection PhpIncludeInspection */
                require_once $bootstrap;
            }
        }

        if (!$this->hyper) {
            $this->hyper = App::getInstance();
        }

        $this->_helper      = $this->hyper['helper']['crm'];
        $this->_counterFile = Path::clean(JPATH_ROOT . '/hyperpc/simpleform2/' . self::COUNTER_FILE_NAME);

        if (!is_file($this->_counterFile)) {
            $fileContent = '{}';
            File::write($this->_counterFile, $fileContent);
        }

        parent::__construct($subject, $config);
    }

    /**
     * Event simple form before send email.
     *
     * @param   $mail
     * @param   simpleForm2 $form
     * @param   \Joomla\Registry\Registry $moduleParams
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function onProcessBeforeSendEmail(&$mail, $form, $moduleParams)
    {
        $formData     = new JSON([]);
        $noteElements = new JSON([]);
        $formElements = $form->getElements();

        if (count($formElements)) {
            /** @var simpleForm2Element $element */
            foreach ($formElements as $element) {
                $params = new JSON($element->getParams());
                $value  = $element->inputValue;
                if (is_array($element->inputValue)) {
                    $value = implode(', ', $element->inputValue);
                }

                $elelmentName = $params->get('name', '');

                $formData->set($elelmentName, $value);
                $noteElements->set($params->get('label', $elelmentName), $value);
            }
        }

        $hash = md5($formData->write());
        if (!key_exists($hash, self::$_formInstances)) {

            if (is_string($formData->get('page-url')) && strpos($formData->get('page-url'), Uri::root()) === false) {
                $noteElements->set('state', 'banned by page-url');
                goto saveToBase;
            }

            if (is_string($formData->get('phone')) && strpos($formData->get('phone'), '+') !== 0) {
                $noteElements->set('state', 'banned by phone value');
                goto saveToBase;
            }

            $uri = Uri::getInstance();
            $localDomains = (array) $this->hyper['config']->get('local_domain');

            if (!in_array($uri->getHost(), $localDomains)) {
                /** @var \JBZoo\Data\Data $module */
                $module  = $this->hyper['helper']['module']->findById($form->moduleID);
                $counter = new JSON(file_get_contents($this->_counterFile));

                list ($pipelineId, $pipelineStatusId) = explode(':', $moduleParams->get('pipeline'), 2);

                $pipelineId       = (int) $pipelineId;
                $pipelineStatusId = (int) $pipelineStatusId;

                $tagsList = $this->_parseTagsFromString($moduleParams->get('amo_tags', ''));

                $workerId   = (int) $moduleParams->get('worker_id', 0);
                $hasCounter = (bool) $moduleParams->get('counter', false);
                $leadName   = (!empty($moduleParams->get('lead_name'))) ? $moduleParams->get('lead_name') : $module->get('title');
                $tagsList   = array_merge($tagsList, $this->_getDafaultTags());

                if ($module->get('id')) {
                    $moduleID = (int) $module->get('id');
                    $oldValue = $counter->get($moduleID, 1, 'int');

                    if ($this->hyper['app']->input->get('campaign')) {
                        $tagsList[] = $this->hyper['app']->input->get('campaign');
                    }

                    $contactsBody = $this->_helper->getContacts(['query' => $formData->get('email')]);
                    $contacts     = (array) $contactsBody->find('_embedded.items');
                    $contactId    = $this->_helper->findContactByEmail($formData->get('email'), $contacts);

                    if ($contactId === null) {
                        $contactCustomFields = [
                            [
                                'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_EMAIL_KEY),
                                'values' => [
                                    [
                                        'enum'  => 'WORK',
                                        'value' => $formData->get('email')
                                    ]
                                ]
                            ]
                        ];

                        if ($formData->get('phone') && $this->hyper['helper']['string']->isValidPhone((string) $formData->get('phone'))) {
                            $contactCustomFields[] = [
                                'id' => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_PHONE_KEY),
                                'values' => [
                                    [
                                        'enum'  => 'MOB',
                                        'value' => $formData->get('phone')
                                    ]
                                ]
                            ];
                        }

                        $newContactBody = $this->_helper->addContact([
                            [
                                'created_at'    => time(),
                                'name'          => $formData->get('name'),
                                'custom_fields' => $contactCustomFields
                            ]
                        ]);

                        $contactId = $newContactBody->find('_embedded.items.0.id');
                    }

                    if ($pipelineId > 0 && $pipelineStatusId > 0) {
                        $leadData = [
                            'created_at'    => time(),
                            'name'          => $leadName,
                            'contacts_id'   => $contactId,
                            'pipeline_id'   => $pipelineId,
                            'status_id'     => $pipelineStatusId,
                            'custom_fields' => [
                                [
                                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ROISTAT_KEY),
                                    'values' => [
                                        [
                                            'value' => $this->hyper['input']->cookie->get('roistat_visit', '')
                                        ]
                                    ]
                                ],
                                [
                                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_GOOGLE_CLIENT_ID_KEY),
                                    'values' => [
                                        [
                                            'value' => $this->hyper['helper']['google']->getCID()
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        $ymuid = $this->hyper['input']->cookie->get('_ym_uid', '');
                        $ymCounterId = $this->hyper['params']->get('ym_counter_id', '');
                        if (!empty($ymuid) && !empty($ymCounterId)) {
                            $leadData['custom_fields'][] = [
                                'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_COUNTER_KEY),
                                'values' => [[
                                    'value' => $ymCounterId
                                ]]
                            ];

                            $leadData['custom_fields'][] = [
                                'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_UID_KEY),
                                'values' => [[
                                    'value' => $ymuid
                                ]]
                            ];
                        }

                        foreach ($formData->getArrayCopy() as $name => $value) {
                            if (preg_match('/^amo-field-(\d+)$/', $name, $matches)) {
                                $crmFieldId = (int) $matches[1];
                                if (preg_match('/:/', $value)) {
                                    list ($enumKey, $enumValue) = explode(':', $value);
                                    $enumKey = (int) trim($enumKey);

                                    if ($enumKey) {
                                        $leadData['custom_fields'][] = [
                                            'id'     => $crmFieldId,
                                            'values' => [
                                                [
                                                    'value' => $enumKey
                                                ]
                                            ]
                                        ];
                                    }
                                } elseif (strlen($value)) {
                                    $leadData['custom_fields'][] = [
                                        'id'     => $crmFieldId,
                                        'values' => [
                                            [
                                                'value' => is_numeric($value) ? (int) $value : $value
                                            ]
                                        ]
                                    ];
                                }
                            }
                        }

                        if (!$workerId) {
                            $worker = $this->hyper['helper']['worker']->getDefaultWorker();
                        } else {
                            $worker = $this->hyper['helper']['worker']->findById($workerId);
                        }

                        /** @var Worker $worker */

                        $responsibleUserId = (int) $worker->params->get('amo_responsible_user_id', 0);
                        if ($responsibleUserId) {
                            $leadData['responsible_user_id'] = $responsibleUserId;
                        }

                        if ($hasCounter) {
                            $oldValue++;
                            $counter->set($moduleID, $oldValue);
                            $fileContent = $counter->write();
                            File::write($this->_counterFile, $fileContent);
                            $leadData['name'] = sprintf($leadName, $this->hyper['helper']['order']->getName($counter->get($moduleID)));
                        }

                        $leadData['tags'] = implode(',', $tagsList);

                        $leadResult = $this->_helper->addLead([$leadData]);
                        $leadId = $this->_amoLeadId = $leadResult->find('_embedded.items.0.id');

                        if ($leadId) {
                            if ($worker->id && !$workerId) {
                                /** @var HyperPcTableWorkers $table */
                                $table = $this->hyper['helper']['worker']->getTable();
                                $worker->set('last_form_turn', Factory::getDate('now +3 hour'));
                                $table->save($worker);
                            }

                            $noteText = [$module->get('title')];
                            foreach ($noteElements->getArrayCopy() as $label => $text) {
                                if (is_string($text) && !empty($text)) {
                                    if (preg_match('/\d{5}:/', $text)) {
                                        list (, $formValue) = explode(':', $text);
                                        $noteText[] =  $label . ': ' . strip_tags(trim($formValue));
                                    } else {
                                        $noteText[] =  $label . ': ' . strip_tags($text);
                                    }
                                }
                            }

                            $this->_helper->addNote($leadId, implode(PHP_EOL, $noteText));
                        }
                    }
                }
            }
        }

        saveToBase:

        $this->_saveToDataBase($form, $noteElements, $moduleParams);
    }

    /**
     * Get default tags
     *
     * @return  string[]
     *
     * @since   2.0
     */
    protected function _getDafaultTags()
    {
        $tagsStr = $this->params->get('default_tags', '');
        return $this->_parseTagsFromString($tagsStr);
    }
    
    /**
     * Get tags array from string
     *
     * @param   string $tagsStr
     *
     * @return  string[]
     *
     * @since   2.0
     */
    protected function _parseTagsFromString($tagsStr)
    {
        if (empty($tagsStr)) {
            return [];
        }

        $tags = explode(',', $tagsStr);
        $tags = array_map(function ($tag) {
            return StringHelper::trim($tag);
        }, $tags);

        return array_filter($tags, function ($tag) {
            return !empty($tag);
        });
    }

    /**
     * Save form data to database.
     *
     * @param         $form
     * @param   JSON  $formData
     * @param         $moduleParams
     *
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _saveToDataBase($form, JSON $formData, $moduleParams)
    {
        $app     = Factory::getApplication();
        $table   = Table::getInstance('Form_Records');
        $subject = $moduleParams->get('sfMailSubj', Text::_('MOD_SIMPLEFORM2_MAIL_SUBJECT_DEFAULT'));

        if (strpos($subject, '{subject}') !== false) {
            $subject = str_replace('{subject}', $app->input->getString('subject', Text::_('MOD_SIMPLEFORM2_MAIL_SUBJECT_DEFAULT')), $subject);
        }

        $user = Factory::getUser();
        $now  = Factory::getDate(null);

        $params = [
            'amo_lead_id' => $this->_amoLeadId,
            'ip' => IpHelper::getIp()
        ];

        if (is_int($this->_amoLeadId)) {
            $this->hyper['helper']['dealMap']->addCrmLeadId($this->_amoLeadId);
        }

        $tableData = [
            'subject'   => $subject,
            'created'   => $now->toSql(),
            'module_id' => $form->moduleID,
            'elements'  => $formData->write(),
            'user_id'   => (int) $user->get('id'),
            'context'   => $this->hyper->getContext(),
            'params'    => (new JSON($params))->write(),
            'recipient' => $moduleParams->get('sfMailTo', ''),
        ];

        $table->save($tableData);
    }
}
