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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Form\FormFieldList;

/**
 * Class JFormFieldCrmCustomFieldAbstract
 *
 * @since 2.0
 */
abstract class JFormFieldCrmCustomFieldAbstract extends FormFieldList
{

    protected const CUSTOM_FIELD_DATATYPE_TEXT            = 'text';
    protected const CUSTOM_FIELD_DATATYPE_ENUM            = 'enum';
    protected const CUSTOM_FIELD_DATATYPE_CHECKBOX        = 'checkbox';
    protected const CUSTOM_FIELD_DATATYPE_TRACKING_DATA   = 'tracking_data';
    protected const CUSTOM_FIELD_DATATYPE_MULTITEXT       = 'multitext';
    protected const CUSTOM_FIELD_DATATYPE_DATE            = 'date';

    /**
     * @var     array
     *
     * @since   2.0
     */
    protected static $crmCustomFields = [];

    /**
     * Custom field data type key
     *
     * @var     string
     *
     * @since   2.0
     */
    protected string $dataType = '';

    /**
     * Crm helper instance
     *
     * @var CrmHelper
     *
     * @since   2.0
     */
    protected $crmHelper;

    /**
     * Plugin params
     *
     * @var     Registry
     *
     * @since   2.0
     */
    protected $pluginParams;

    /**
     * Get custom fields list
     *
     * @return  array
     *
     * @since   2.0
     */
    abstract protected function getCustomFieldsList(): array;

    /**
     * Method to instantiate the form field object.
     *
     * @param   Form  $form  The form to attach to the form field object.
     *
     * @since   2.0
     */
    public function __construct($form = null)
    {
        parent::__construct($form);

        $plugin = PluginHelper::getPlugin('system', 'crm');
        if (empty($plugin)) {
            $this->hyper['cms']->enqueueMessage(Text::_('PLG_SYSTEM_CRM_ERROR_PLUGIN_DISABLED'), 'error');
        }

        $this->pluginParams = new Registry($plugin->params ?? []);

        try {
            $this->crmHelper = $this->hyper['helper']['crm'];
        } catch (\Throwable $th) {
        }
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $options = [];
        $customFields = $this->getCustomFieldsList();

        $dataTypeMap = [
            self::CUSTOM_FIELD_DATATYPE_TEXT => ['text', 'textarea', 'numeric', 'price', 'streetaddress', 'monetary', 'url'],
            self::CUSTOM_FIELD_DATATYPE_ENUM => ['radiobutton', 'select', 'multiselect', 'category'],
            self::CUSTOM_FIELD_DATATYPE_CHECKBOX => ['checkbox'],
            self::CUSTOM_FIELD_DATATYPE_TRACKING_DATA => ['tracking_data'],
            self::CUSTOM_FIELD_DATATYPE_MULTITEXT => ['multitext'],
            self::CUSTOM_FIELD_DATATYPE_DATE => ['date', 'date_time', 'birthday']
            /** @todo map for smart_address, legal_entity, items, linked_entity, chained_list and file field types */
        ];

        $dataType = $this->dataType;
        if (empty($customFields) && $this->value !== '0') { // plugin disabled
            $options = [
                [
                    'value' => $this->value,
                    'text'  => $this->value
                ]
            ];
        } elseif (key_exists($dataType, $dataTypeMap)) {
            $options = [
                [
                    'value' => 0,
                    'text'  => Text::_('PLG_SYSTEM_CRM_CUSTOM_FIELD_NOT_SELECTED')
                ]
            ];

            $customfields = array_filter($customFields, function ($customField) use ($dataTypeMap, $dataType) {
                return in_array($customField['type'], $dataTypeMap[$dataType]);
            });

            /** @todo add field group dividers */

            foreach ($customfields as $customField) {
                $field = new Registry($customField);
                $options[$field->get('id')]['value'] = $field->get('id');
                $options[$field->get('id')]['text']  = $field->get('name');
            }
        } else {
            $options = [
                [
                    'value' => 0,
                    'text'  => Text::_('PLG_SYSTEM_CRM_CUSTOM_FIELD_DATATYPE_NOT_SET')
                ]
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
