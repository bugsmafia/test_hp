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

use Dompdf\Dompdf;
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Model\Entity\Requisite;
use Joomla\CMS\Access\Exception\NotAllowed;
use HYPERPC\Elements\ElementConfiguratorActions;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ElementConfigurationActionsCommercialProposal
 *
 * @since       2.0
 */
class ElementConfigurationActionsCommercialProposal extends ElementConfiguratorActions
{
    protected const PAPER_SIZES = [
        'default' => ['a4', 'portrait'],
        '2025' => [[0, 0, 468, 684], 'portrait']
    ];

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

        $this
            ->registerAction('getFormHtml')
            ->registerAction('preprocessBuildPdf')
            ->registerAction('buildPdf')
            ->registerAction('buildPdfPreview');
    }

    /**
     * Action build pdf.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function actionBuildPdf()
    {
        if (!$this->isPermitted()) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $configuration = $this->getConfiguration();
        if ($configuration->id) {
            $layout = $this->getConfig('layout', 'default');

            $errorMessage = [];
            if (!$configuration->getAmoLeadId()) {
                $errorMessage[] = Text::_(
                    'HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_ERROR_NOT_FOUND_AMO_CRM_LEAD_ID'
                );
            }

            if (count($errorMessage)) {
                $layout = 'error';
            }

            if ($layout = $this->getLayout($layout)) {
                $this->hyper['cms']->setHeader('Content-Type', 'application/pdf');

                $domPdf = $this->_getDomPdf();

                $layoutValues = [
                    'amoLead'      => new JSON(),
                    'amoContact'   => new JSON(),
                    'requisite'    => new Requisite(),
                    'htmlBlocks'   => $this->getHtmlBlocks(),
                    'errorMessage' => implode(PHP_EOL, $errorMessage)
                ];

                /** @var CrmHelper */
                $crmHelper = $this->hyper['helper']['crm'];
                $lead = $crmHelper->getLeadById($configuration->getAmoLeadId());
                $leadId = $lead->get('id');
                if ($leadId) {
                    $layoutValues['amoLead']    = $lead;
                    $layoutValues['amoContact'] = $crmHelper->getUser(
                        $lead->get('responsible_user_id')
                    );

                    if ($this->hyper['input']->get('origin') === $this->getIdentifier() . '.form') {
                        $this->_updateLead($lead, $configuration);
                    }
                }

                $domPdf->loadHtml($this->_renderLayout($layout, $layoutValues));
                $domPdf->render();

                $domPdf->stream($this->getConfig('name'), [
                    'Attachment' => 0
                ]);
            }
        }

        $this->hyper['cms']->close();
    }

    /**
     * Generate pdf preview.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function actionBuildPdfPreview()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/pdf');

        $layout = $this->getConfig('layout', 'default');
        $previewLayout = $this->getLayout('preview_' . $layout);

        $domPdf = $this->_getDomPdf();
        $domPdf->loadHtml($this->_renderLayout($previewLayout, [
            'htmlBlocks' => $this->getHtmlBlocks()
        ]));

        $domPdf->render();

        $domPdf->stream($this->getConfig('name'), [
            'Attachment' => 0
        ]);

        $this->hyper['cms']->close();
    }

    /**
     * Get action button url.
     *
     * @param   bool  $isFull
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getActionButtonUrl($isFull = false)
    {
        return $this->_getPdfBuildUrl($isFull);
    }

    /**
     * get the text depending on the price
     *
     * @param   ProductMarker $product
     *
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getGradationText(ProductMarker $product)
    {
        $text      = '';
        $price     = $product->getConfigPrice()->val();
        $gradation = $this->getConfig('price_gradation');

        foreach ($gradation as $gradationItem) {
            if ((int) $price > (int) $gradationItem['commercial_proposal_price']) {
                $lang = substr($this->hyper->getDefaultLanguageCode(), 0, 2);
                $text = $gradationItem['commercial_proposal_text'][$lang] ?? '';

                break;
            }
        }

        return $text;
    }

    /**
     * Render build pdf form.
     *
     * @return  string|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getFormHtml()
    {
        $form = $this->_getClientForm();
        $this->_bindClientFormData($form);

        return $this->render([
            'layout' => 'form',
            'form'   => $form
        ]);
    }

    /**
     * Get html blocks from articles categories.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getHtmlBlocks()
    {
        $contentCategoryId = $this->getConfig('content_category_id', 0, 'int');

        if ($contentCategoryId === null) {
            return [];
        }

        $db = $this->hyper['db'];
        $query = $db->getQuery(true)
            ->select('a.introtext')
            ->from($db->qn('#__content', 'a'))
            ->where([
                $db->qn('a.state') . ' = ' . HP_STATUS_PUBLISHED,
                $db->qn('a.catid') . ' = ' . $db->q($contentCategoryId)
            ])
            ->order($db->qn('a.ordering') . ' ASC');

        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Get lead issue field value
     *
     * @param   JSON    $lead
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getIssue(JSON $lead): string
    {
        return $this->_getLeadCustomFieldValue($lead, CrmHelper::LEAD_FIELD_ISSUE_KEY);
    }

    /**
     * Get lead purchase purpose field value
     *
     * @param   JSON    $lead
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPurchasePurpose(JSON $lead): string
    {
        return $this->_getLeadCustomFieldValue($lead, CrmHelper::LEAD_FIELD_PURCHASE_PURPOSE_KEY);
    }

    /**
     * Checks if pdf build is permitted for web client.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isPermitted()
    {
        $canDo = $this->canDo();

        if (!$canDo) {
            $b2bfamilyIP = '95.163.208.133';
            $canDo = IpHelper::getIp() === $b2bfamilyIP;
        }

        return $canDo;
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
            ->widget('body', 'HyperPC.SiteConfigurationActionsCPPdf', []);
    }

    /**
     * Preprocess build bdf
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function preprocessBuildPdf()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new Registry([
            'result' => false
        ]);

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close($output->toString());
        };

        $configuration = $this->getConfiguration();
        if (!$configuration->id) {
            $output->set('message', Text::_('COM_HYPERPC_CONFIGURATION_NOT_EXIST'));
            $this->hyper['cms']->close($output->toString());
        }

        $jform = new Registry($this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));
        $clientName = $jform->get('client_name', '');
        $amoLeadUrl = $jform->get('amo_lead_url', '');

        $leadId = $this->hyper['helper']['crm']->getLeadIdFromUrl($amoLeadUrl);
        if (!$leadId) {
            $output->set('message', Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_ERROR_AMO_LEAD_NOT_FOUND'));
            $this->hyper['cms']->close($output->toString());
        }

        $lead = $this->hyper['helper']['crm']->getLeadById($leadId);
        if (empty($lead->get('id'))) {
            $output->set('message', Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_ERROR_AMO_LEAD_NOT_FOUND'));
            $this->hyper['cms']->close($output->toString());
        }

        if ($configuration->getAmoLeadId() !== $leadId || $configuration->params->get('username') !== $clientName) {
            $configuration->params
                ->set('amo_lead_id', $leadId)
                ->set('username', $clientName);
            $this->hyper['helper']['configuration']->getTable()->save($configuration->getArray());
        }

        $output->set('result', true);
        $output->set('url', $this->_getPdfBuildUrl(false, [
            'origin' => $this->getIdentifier() . '.form'
        ]));

        $this->hyper['cms']->close($output->toString());
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
        $this->loadAssets();

        $configuration = $this->getConfiguration();

        $formUrl = $this->hyper['route']->build([
            'tmpl'       => 'raw',
            'action'     => 'get_form_html',
            'task'       => 'elements.call',
            'group'      => $this->getGroup(),
            'identifier' => $this->getIdentifier(),
            'id'         => $configuration->id
        ]);

        $attrs = $this->hyper['helper']['html']->buildAttrs([
            'href'  => $formUrl,
            'class' => 'jsShowCpForm'
        ]);

        return implode('', [
            '<a ' . $attrs . '>',
                $this->getAccountActionTile(),
            '</a>'
        ]);
    }

    /**
     * Bind configuration data to the form
     *
     * @param   Form $form
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _bindClientFormData(Form $form)
    {
        $configuration = $this->getConfiguration();
        if (!$configuration->id) {
            return [];
        }

        $username = $configuration->params->get('username', '', 'strip');
        $leadId = $configuration->params->get('amo_lead_id', 0, 'int');
        $form->bind([
            'client_name' => $username,
            'amo_lead_url' => $leadId ? $this->hyper['helper']['crm']->getLeadUrl($leadId) : ''
        ]);
    }

    /**
     * Get build pdf form.
     *
     * @return  Form
     *
     * @since   2.0
     */
    public function _getClientForm()
    {
        Form::addFormPath($this->getPath('forms'));
        return Form::getInstance($this->getIdentifier() . '.form', 'form', [
            'control' => JOOMLA_FORM_CONTROL
        ]);
    }

    /**
     * Get DomPDF object.
     *
     * @return  Dompdf
     *
     * @since   2.0
     */
    protected function _getDomPdf()
    {
        $domPdf = new Dompdf();

        $domPdf->getOptions()->setChroot([
            realpath(JPATH_ROOT . '/media/hyperpc'),
            realpath(JPATH_ROOT . '/administrator/components/com_hyperpc/framework/Printer/'),
            realpath(JPATH_ROOT . '/images'),
            realpath(JPATH_ROOT . '/cache')
        ]);

        $this->_setPaper($domPdf);

        return $domPdf;
    }

    /**
     * Get lead custom field by field id.
     *
     * @param   JSON     $lead
     * @param   int      $fieldId
     *
     * @return  JSON
     *
     * @since   2.0
     */
    protected function _getLeadCustomField(JSON $lead, int $fieldId): JSON
    {
        foreach ($lead->get('custom_fields', [], 'arr') as $customField) {
            if (array_key_exists('id', $customField) && $customField['id'] === $fieldId) {
                return new JSON($customField);
            }
        }

        return new JSON();
    }

    /**
     * Get lead custom field value by field key
     *
     * @param   JSON     $lead
     * @param   string   $fieldKey use CrmHelper class constants as key
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getLeadCustomFieldValue(JSON $lead, string $fieldKey): string
    {
        /** @var CrmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $fieldId = $crmHelper->getCustomFieldId($fieldKey);
        if (empty($fieldId)) {
            return '';
        }

        $field = $this->_getLeadCustomField($lead, $fieldId);

        return (string) $field->find('values.0.value', '');
    }

    /**
     * Get url for build pdf.
     *
     * @param   bool $isFuul
     * @param   array $additionalProps additional params for request
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since 2.0
     */
    protected function _getPdfBuildUrl($isFull = false, $additionalProps = [])
    {
        $actionUrl = [
            'option'     => HP_OPTION,
            'task'       => 'elements.call',
            'group'      => $this->getGroup(),
            'identifier' => $this->getIdentifier(),
            'action'     => 'buildPdf',
            'id'         => $this->getConfiguration()->id
        ];

        $actionUrl = array_merge($actionUrl, $additionalProps);

        if (!$isFull) {
            return $this->hyper['route']->build($actionUrl);
        }

        return Uri::root() . 'index.php?' . Uri::buildQuery($actionUrl);
    }

    /**
     * Update CRM lead
     *
     * @param   JSON $lead
     * @param   SaveConfiguration $configuration
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since 2.0
     */
    protected function _updateLead(JSON $lead, SaveConfiguration $configuration)
    {
        /** @var CrmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $leadId = $lead->get('id');

        $pdfBuildUrl = $this->_getPdfBuildUrl(true);

        $customFields = (array) $lead->get('custom_fields', []);
        $fieldFound = false;
        foreach ($customFields as &$customField) {
            if ($customField['id'] === $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_COMMERCIAL_PROPOSAL_URL_KEY)) {
                $value = $customField['values'][0]['value'];
                if ($value !== $pdfBuildUrl) {
                    $customField['values'][0]['value'] = $pdfBuildUrl;

                    $customFields = $this->_setCreatedState($customFields, true);

                    $lead->set('custom_fields', $customFields);
                    $fieldFound = true;
                    $crmHelper->updateLead([[
                        'updated_at'    => time(),
                        'id'            => $leadId,
                        'custom_fields' => $customFields,
                        'tags'          => false
                    ]]);
                }

                break;
            }
        }

        if (!$fieldFound) {
            $customFields[] = [
                'id'     => $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_COMMERCIAL_PROPOSAL_URL_KEY),
                'values' => [['value' => $pdfBuildUrl]]
            ];

            $customFields = $this->_setCreatedState($customFields, true);

            $crmHelper->updateLead([[
                'updated_at'    => time(),
                'id'            => $leadId,
                'custom_fields' => $customFields,
                'tags'          => false
            ]]);
        }

        $product = $configuration->getProduct();

        $language = $crmHelper->getCrmLanguage();
        $language->load('el_' . $this->getGroup() . '_' .  $this->getType(), $this->getPath());

        $note = [
            $language->_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_AMO_CRM_NOTE_TITLE'),
            $pdfBuildUrl,
            $product->getName(),
            $language->_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_COMMERCIAL_PROPOSAL_AMO_CRM_NOTE_DESCRIPTION'),
        ];

        $crmHelper
            ->addLeadNoteTextByProductConfiguration($product, $note)
            ->addNote($leadId, implode(PHP_EOL, $note));
    }

    /**
     * Set commercial proposal created state to the lead custom fields
     *
     * @param   array $customFields
     * @param   bool $state
     *
     * @return  array
     *
     * @since 2.0
     */
    protected function _setCreatedState(array $customFields, bool $state): array
    {
        /** @var CrmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $filedId = $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_COMMERCIAL_PROPOSAL_CREATED_KEY);
        if (!$filedId) {
            return $customFields;
        }

        $fieldFound = false;
        foreach ($customFields as &$customField) {
            if ($customField['id'] === $filedId) {
                $customField['values'][0]['value'] = (int) $state;
                $fieldFound = true;
                break;
            }
        }

        if (!$fieldFound) {
            $customFields[] = [
                'id'     => $filedId,
                'values' => [['value' => (int) $state]]
            ];
        }

        return $customFields;
    }

    /**
     * Set paper size to the dompdf object
     *
     * @param   Dompdf $dompdf
     *
     * @return  void
     */
    protected function _setPaper(Dompdf &$dompdf): void
    {
        $layout = $layout = $this->getConfig('layout', 'default');
        $paper = static::PAPER_SIZES[$layout] ?? static::PAPER_SIZES['default'];

        $dompdf->setPaper(...$paper);
    }
}
