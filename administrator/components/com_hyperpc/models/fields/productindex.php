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

use Cake\Utility\Xml;
use HYPERPC\Data\JSON;
use HYPERPC\ORM\Entity\Field;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use HYPERPC\Helper\FilterHelper;
use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

defined('_JEXEC') or die('Restricted access');

FormHelper::loadFieldClass('HPCategory');

/**
 * Class JFormFieldProductIndex
 *
 * @since   2.0
 */
class JFormFieldProductIndex extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.product_index';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'productIndex';

    /**
     * Hold field context
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $fieldContext;

    /**
     * Get category fields callback.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getCategoryFieldsCallback()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $groupId = $this->hyper['input']->get('group_id');
        $context = 'position';

        /** @var ProductFolderHelper $groupHelper */
        $groupHelper = $this->hyper['helper']['productFolder'];

        /** @var ProductFolder $group */
        $group = $groupHelper->findById($groupId);

        $output = new JSON([
            'result' => false,
            'output' => null
        ]);

        if (!$group->id) {
            $output->set('output', Text::_('COM_HYPERPC_ERROR_GROUP_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $fields = $this->hyper['helper']['fields']->getGroupFields($groupId, $context);

        if (count($fields) <= 0) {
            $output->set('output', Text::_('COM_HYPERPC_ERROR_GROUP_CUSTOM_FIELD_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $_returnListOptions = [];
        /** @var Field $field */
        foreach ($fields as $field) {
            $_returnListOptions[] = '<option value="' . $field->id . '">' . $field->label . '</option>';
        }

        $output
            ->set('result', true)
            ->set('output', implode('', $_returnListOptions));

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Get custom field list by field group.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getGroupFieldsCallback()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result' => false,
            'output' => null
        ]);

        $groupId = $this->hyper['input']->get('group_id');

        if ($groupId === 'none') {
            $this->hyper['cms']->close($output->write());
        }

        $fields = $this->_getPartCustomFieldsByGroupId($groupId);

        if (count($fields) <= 0) {
            $output->set('output', Text::_('COM_HYPERPC_ERROR_GROUP_CUSTOM_FIELD_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $_returnListOptions = [];
        /** @var Field $field */
        foreach ($fields as $field) {
            $_returnListOptions[] = '<option value="' . $field->id . '">' . $field->label . '</option>';
        }

        $output
            ->set('result', true)
            ->set('output', implode('', $_returnListOptions));

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Get part custom field groups.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getPartCustomFieldGroups()
    {
        $groups = $this->_getPartCustomFieldsGroups();

        $select = [
            '<select class="jsSelectFieldsGroup form-control">',
            '<option value="none">' . Text::_('COM_HYPERPC_SELECT_FIELD_GROUP_TITLE') . '</option>'
        ];

        foreach ($groups as $group) {
            $select[] = '<option value="' . $group->get('id') . '">' . $group->get('title') . '</option>';
        }

        $select[] = '</select>';

        return implode(PHP_EOL, [
            '<div>',
                '<div style="display: inline-block">',
                    Text::_('COM_HYPERPC_PART_CUSTOM_FIELD_GROUPS_TITLE'),
                '</div>',
                '<div>',
                    implode(PHP_EOL, $select),
                '</div>',
            '</div>'
        ]);
    }

    /**
     * Get part custom fields.
     *
     * @param   string|int  $groupId
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getPartCustomFieldsByGroupId($groupId)
    {
        $db = $this->hyper['db'];

        $query = $db
            ->getQuery(true)
            ->select([
                'a.*'
            ])
            ->from(
                $db->qn('#__fields', 'a')
            )
            ->where([
                $db->qn('a.product_folder_id') . ' = ' . $db->q($groupId),
                $db->qn('a.state')    . ' = ' . $db->q(HP_STATUS_PUBLISHED),
                $db->qn('a.context')  . ' = ' . $db->q(HP_OPTION . '.part')
            ])
            ->order($db->qn('a.ordering') . ' ASC');

        $_list = $db->setQuery($query)->loadAssocList('id');

        $class = Field::class;
        $list  = [];
        foreach ($_list as $id => $item) {
            $list[$id] = new $class($item);
        }

        return $list;
    }

    /**
     * Get part custom fields groups.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getPartCustomFieldsGroups()
    {
        $db = $this->hyper['db'];

        $query = $db
            ->getQuery(true)
            ->select([
                'a.id', 'a.title'
            ])
            ->from(
                $db->qn('#__fields_groups', 'a')
            )
            ->where([
                $db->qn('a.state')   . ' = ' . $db->q(HP_STATUS_PUBLISHED),
                $db->qn('a.context') . ' = ' . $db->q($this->fieldContext)
            ])
            ->order($db->qn('a.ordering') . ' ASC');

        $_list = $db->setQuery($query)->loadAssocList('id');

        $class = Field::class;
        $list  = [];
        foreach ($_list as $id => $item) {
            $list[$id] = new $class($item);
        }

        return $list;
    }

    /**
     * Get product category tree.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getPartGroupsTree()
    {
        /** @var FormField $group */
        if ($this->fieldContext === 'com_hyperpc.position') {
            $group = new JFormFieldHPFolders();
            $group->setup(Xml::build([
                'field' => [
                    '@type'         => 'HPFolders',
                    '@name'         => 'fiter_product_cat_ids',
                    '@description'  => 'JFIELD_FIELDS_CATEGORY_DESC',
                    '@label'        => 'COM_HYPERPC_PART_GROUPS_TITLE'
                ]
            ]), null);
        } else {
            $group = new JFormFieldHPGroups();
            $group->setup(Xml::build([
                'field' => [
                    '@type'         => 'HPGroups',
                    '@name'         => 'fiter_product_cat_ids',
                    '@description'  => 'JFIELD_FIELDS_CATEGORY_DESC',
                    '@label'        => 'COM_HYPERPC_PART_GROUPS_TITLE'
                ]
            ]), null);
        }

        return implode(PHP_EOL, [
            '<div>',
                '<div style="display: inline-block">',
                    $group->getLabel(),
                '</div>',
                '<div>',
                    $group->getInput(),
                '</div>',
            '</div>'
        ]);
    }

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
                'fieldType'     => $this->type,
                'confirmMsg'    => Text::_('COM_HYPERPC_ARE_YOU_SURE'),
                'fieldAddedMsg' => Text::_('COM_HYPERPC_PART_OPTION_ADDED_MSG'),
                'typePartGroup' => FilterHelper::FIELD_TYPE_PART_GROUP,
                'typeFieldCat'  => FilterHelper::FIELD_TYPE_FIELD_CATEGORY
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
        $this->fieldContext = (string) $this->element['context'];

        return array_merge(parent::getLayoutData(), [
            'groupTree'    => $this->_getPartGroupsTree(),
            'fieldGroups'  => $this->_getPartCustomFieldGroups(),
            'fieldContext' => $this->fieldContext
        ]);
    }
}
