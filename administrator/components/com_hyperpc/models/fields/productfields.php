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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use HYPERPC\Helper\FilterHelper;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

FormHelper::loadFieldClass('HPCategory');

/**
 * Class JFormFieldProductFields
 *
 * @since   2.0
 */
class JFormFieldProductFields extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.productfields';


    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'productfields';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.0
     */
    protected function getInput()
    {
        $this->hyper['helper']['assets']
            ->js('js:widget/admin/fields/product-index.js')
            ->widget('.jsProductIndex', 'HyperPC.FieldProductIndex', [
                'formName' => $this->name,
                'fieldType' => $this->type,
                'confirmMsg' => Text::_('COM_HYPERPC_ARE_YOU_SURE'),
                'fieldAddedMsg' => Text::_('COM_HYPERPC_PART_OPTION_ADDED_MSG'),
                'typePartGroup' => FilterHelper::FIELD_TYPE_PART_GROUP,
                'typeFieldCat' => FilterHelper::FIELD_TYPE_FIELD_CATEGORY
            ]);

        return parent::getInput();
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        return array_merge(parent::getLayoutData(), [
            'fieldsTree'   => $this->_getFields(),
            'fieldName'    => $this->fieldname,
        ]);
    }

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFields()
    {
        $app = App::getInstance();

        $filterHelper = $this->hyper['helper']['moyskladFilter'];

        $groupHelper  = $this->hyper['helper']['productFolder'];

        $fieldIndex = (array) $app['params']->get($filterHelper::PRODUCT_INDEX_FIELD, []);

        $select = [
            '<select class="jsSelectFilterField form-control" style="width: 100%">',
            '<option value="none">' . Text::_('COM_HYPERPC_FIELD_ALLOWED_FILTERS_FIELDS') . '</option>'
        ];

        $groups = $groupHelper->getList();
        foreach ($fieldIndex as $i => $item) {
            if (array_key_exists($item['group_id'], $groups)) {
                $item['groupName'] = $groups[$item['group_id']]->title;
            }

            $select[] = '<option value="' . implode(':', $item) . '">' . $item['title']  . '</option>';
        }

        $select[] = '</select>';

        return implode(PHP_EOL, [
            '<div>',
                '<div style="display: inline-block">',
                    Text::_('COM_HYPERPC_INDEX_FIELDS_LABEL'),
                '</div>',
                '<div>',
                    implode(PHP_EOL, $select),
                '</div>',
            '</div>'
        ]);
    }
}
