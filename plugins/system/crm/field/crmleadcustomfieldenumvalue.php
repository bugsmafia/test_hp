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

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('CrmLeadCustomFieldAbstract');

/**
 * Class JFormFieldCrmLeadCustomFieldEnumValue
 *
 * @since 2.0
 */
final class JFormFieldCrmLeadCustomFieldEnumValue extends JFormFieldCrmLeadCustomFieldAbstract
{

    /**
     * Custom field data type key
     *
     * @var     string
     *
     * @since   2.0
     */
    protected string $dataType = self::CUSTOM_FIELD_DATATYPE_ENUM;

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'CrmLeadCustomFieldEnumValue';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $relatedField = (string) $this->element['related'] ?? '';

        if (empty($relatedField)) {
            return [
                [
                    'value' => 0,
                    'text'  => Text::_('PLG_SYSTEM_CRM_CUSTOM_FIELD_RELATED_NOT_SET')
                ]
            ];
        }

        $relatedFieldId = (int) $this->pluginParams->get($relatedField, 0);
        if (empty($relatedFieldId)) {
            return [
                [
                    'value' => 0,
                    'text'  => Text::_('PLG_SYSTEM_CRM_CUSTOM_FIELD_RELATED_ID_NOT_SET')
                ]
            ];
        }

        $options = [
            [
                'value' => 0,
                'text'  => Text::_('PLG_SYSTEM_CRM_CUSTOM_FIELD_NOT_SELECTED')
            ]
        ];
        $customFields = $this->getCustomFieldsList();

        $enumTypes = ['radiobutton', 'select', 'multiselect', 'category'];

        if (empty($customFields) && $this->value !== '0') { // plugin disabled
            $options = [
                [
                    'value' => $this->value,
                    'text'  => $this->value
                ]
            ];
        } else {
            $customfield = null;
            foreach ($customFields as $field) {
                if (in_array($field['type'], $enumTypes) && $field['id'] === $relatedFieldId) {
                    $customfield = $field;
                    break;
                }
            }

            if (!empty($customfield)) {
                $enum = $customfield['enums'] ?? [];
                foreach ($enum as $item) {
                    $options[] = [
                        'value' => $item['id'],
                        'text'  => $item['value']
                    ];
                }
            }
        }

        return $options;
    }
}
