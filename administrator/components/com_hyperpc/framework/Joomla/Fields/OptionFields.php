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
 * @author      Roman Evsyukov
 * @desc        This class overrides the Joomla! Form standard class.
 */

namespace HYPERPC\Joomla\Fields;

use Joomla\Event\Event;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * OptionFields class
 *
 * @since 2.0
 */
class OptionFields extends FieldsHelper
{

    /**
     * Returns the fields for the given context.
     * If the item is an object the returned fields do have an additional field
     * "value" which represents the value for the given item. If the item has an
     * assigned_cat_ids field, then additionally fields which belong to that
     * category will be returned.
     *
     * Should the value being prepared to be shown in an HTML context then
     * prepareValue must be set to true. No further escaping needs to be done.
     * The values of the fields can be overridden by an associative array where the keys
     * have to be a name and its corresponding value.
     *
     * @param string     $context T he context of the content passed to the helper
     * @param null       $item item
     * @param int|bool   $prepareValue (if int is display event): 1 - AfterTitle, 2 - BeforeDisplay, 3 - AfterDisplay, 0 - OFF
     * @param array|null $valuesToOverride The values to override
     * @param bool       $includeSubformFields Include subform
     *
     * @return  array
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public static function getFields($context, $item = null, $prepareValue = false, array $valuesToOverride = null,  bool $includeSubformFields = false)
    {
        $fields = parent::getFields($context, $item, $prepareValue, $valuesToOverride);
        if ($item instanceof PartMarker && $item->id) {
            $allowedOptions = (array) $item->params->get('option_fields', []);
            foreach ($fields as $key => $field) {
                if (!in_array($field->id, $allowedOptions)) {
                    unset($fields[$key]);
                }
            }
        }

        return $fields;
    }

    /**
     * Prepare form action.
     *
     * @param   string  $context  The context of the content passed to the helper
     * @param   \JForm|Form  $form     Form object.
     * @param   array        $data      Form data.
     *
     * @return  boolean
     *
     * @throws  \Exception
     *
     * @since   3.7.0
     */
    public static function prepareForm($context, \JForm $form, $data)
    {
        //  Extracting the component and section.
        $parts = self::extract($context);

        if (!$parts) {
            return true;
        }

        $fieldsItem = new \JObject();
        $context    = $parts[0] . '.' . $parts[1];

        if (isset($data['item']) && $data['item'] instanceof PartMarker) {
            $fieldsItem = $data['item'];
            unset($data['item']);
        }

        //  When no fields available return here.
        $fields = self::getFields($parts[0] . '.' . $parts[1], $fieldsItem);
        if (!$fields) {
            return true;
        }

        $component = $parts[0];
        $section   = $parts[1];

        //  Getting the fields.
        $fields = self::getFields($parts[0] . '.' . $parts[1], $fieldsItem);

        if (!$fields) {
            return true;
        }

        $fieldTypes = self::getFieldTypes();

        //  Creating the dom.
        $xml = new \DOMDocument('1.0', 'UTF-8');

        /** @var \DOMElement $fieldsNode */
        $fieldsNode = $xml->appendChild(new \DOMElement('form'))->appendChild(new \DOMElement('fields'));
        $fieldsNode->setAttribute('name', 'params.options');

        //  Organizing the fields according to their group.
        $fieldsPerGroup = [0 => []];

        foreach ($fields as $field) {

            //  Field type is not available.
            if (!array_key_exists($field->type, $fieldTypes)) {
                continue;
            }

            if (!array_key_exists($field->group_id, $fieldsPerGroup)) {
                $fieldsPerGroup[$field->group_id] = [];
            }

            //  Add the lookup path for the field.
            if ($path = $fieldTypes[$field->type]['path']) {
                FormHelper::addFieldPath($path);
            }

            //  Add the lookup path for the rule.
            if ($path = $fieldTypes[$field->type]['rules']) {
                FormHelper::addRulePath($path);
            }

            $fieldsPerGroup[$field->group_id][] = $field;
        }

        //  On the front, sometimes the admin fields path is not included.
        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/tables');

        $model = BaseDatabaseModel::getInstance('Groups', 'FieldsModel', ['ignore_request' => true]);
        $model->setState('filter.context', $context);

        /**
         * $model->getItems() would only return existing groups, but we also
         * have the 'default' group with id 0 which is not in the database,
         * so we create it virtually here.
         */
        $defaultGroup               = new \stdClass;
        $defaultGroup->id           = 0;
        $defaultGroup->title        = '';
        $defaultGroup->description  = '';
        $iterateGroups              = array_merge([$defaultGroup], $model->getItems());

        //  Looping through the groups.
        foreach ($iterateGroups as $group) {
            if (empty($fieldsPerGroup[$group->id])) {
                continue;
            }

            /** @var \DOMElement $fieldset */
            $fieldset = $fieldsNode->appendChild(new \DOMElement('fieldset'));

            //  Defining the field set.
            $fieldset->setAttribute('name', 'fields-' . $group->id);
            $fieldset->setAttribute('addfieldpath', '/administrator/components/' . $component . '/models/fields');
            $fieldset->setAttribute('addrulepath', '/administrator/components/' . $component . '/models/rules');

            $label       = $group->title;
            $description = $group->description;

            if (!$label) {
                $key = strtoupper($component . '_FIELDS_' . $section . '_LABEL');
                if (!Factory::getLanguage()->hasKey($key)) {
                    $key = 'JGLOBAL_FIELDS';
                }

                $label = $key;
            }

            if (!$description) {
                $key = strtoupper($component . '_FIELDS_' . $section . '_DESC');
                if (Factory::getLanguage()->hasKey($key)) {
                    $description = $key;
                }
            }

            $fieldset->setAttribute('label', $label);
            $fieldset->setAttribute('description', strip_tags($description));

            //  Looping through the fields for that context.
            foreach ($fieldsPerGroup[$group->id] as $field) {
                try {
                    $dispatcher = Factory::getApplication()->getDispatcher();
                    $event      = new Event('onCustomFieldsPrepareDom', [
                        $field, $fieldset, $form
                    ]);
                    $dispatcher->dispatch('onCustomFieldsPrepareDom', $event);
                }
                catch (\Exception $e) {
                    Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }
            }

            //   When the field set is empty, then remove it.
            if (!$fieldset->hasChildNodes()) {
                $fieldsNode->removeChild($fieldset);
            }
        }

        //  Loading the XML fields string into the form.
        $form->load($xml->saveXML());

        return true;
    }
}
