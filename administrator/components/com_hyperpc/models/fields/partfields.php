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
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Joomla\Model\Entity\Field;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldGroupFields
 *
 * @property    Form $form
 *
 * @since       2.0
 */
class JFormFieldPartFields extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'PartFields';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.part_fields';

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['helper']['assets']
            ->js('js:widget/admin/fields/part-fields.js')
            ->widget('#' . $this->id, 'HyperPC.FieldPartFields', [
                'formName'      => $this->name,
                'confirmMsg'    => Text::_('COM_HYPERPC_ARE_YOU_SURE'),
                'fieldAddedMsg' => Text::_('COM_HYPERPC_PART_OPTION_ADDED_MSG')
            ]);

        return parent::getInput();
    }

    /**
     * Method to get the fields.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getFields()
    {
        $data        = $this->form->getData();
        $groupKey    = ((string) $this->element['group_key'] !== '') ? (string) $this->element['group_key'] : 'id';
        $executeType = ((string) $this->element['execute-type'] !== '') ? (string) $this->element['execute-type'] : '';
        $context     = ((string) $this->element['context'] !== '') ? (string) $this->element['context'] : HP_OPTION . '.part';

        $id  = $data->get($groupKey);
        $app = App::getInstance();
        $db  = $app['db'];

        $conditions = [
            $db->quoteName('f.state')       . ' = ' . $db->quote(HP_STATUS_PUBLISHED),
            $db->quoteName('c.category_id') . ' = ' . $db->quote($id),
            $db->quoteName('f.context')     . ' = ' . $db->quote($context)
        ];

        if (!empty($executeType)) {
            $types = [];
            $executeType = explode(',', $executeType);

            foreach ($executeType as $value) {
                $value   = trim($value);
                $types[] = $db->quote($value);
            }

            $conditions[] = $db->quoteName('f.type') . ' NOT IN (' . implode(', ', $types) . ')';
        }

        $query = $db
            ->getQuery(true)
            ->select([
                'c.category_id', 'f.*'
            ])
            ->from(
                $db->quoteName('#__fields_categories', 'c')
            )
            ->join(
                'LEFT', $db->quoteName('#__fields', 'f') . ' ON c.field_id = f.id'
            )
            ->where($conditions)
            ->order($db->quoteName('f.ordering') . ' ASC');

        $allQueryFields = $db
            ->getQuery(true)
            ->select([
                'a.*', 'b.*'
            ])
            ->from(
                $db->quoteName('#__fields', 'a')
            )
            ->join(
                'LEFT', $db->quoteName('#__fields_categories', 'b') . ' ON b.field_id = a.id'
            )
            ->where([
                $db->quoteName('a.state')       . ' = ' . $db->quote(HP_STATUS_PUBLISHED),
                $db->quoteName('b.category_id') . ' IS NULL',
                $db->quoteName('a.context')     . ' = ' . $db->quote($context)
            ])
            ->order($db->quoteName('a.ordering') . ' ASC');

        return $app['helper']['object']->createList(
            array_merge(
                $db->setQuery($allQueryFields)->loadObjectList(),
                $db->setQuery($query)->loadObjectList()
            ),
            Field::class,
            'id'
        );
    }

    /**
     * Get field set title language text.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getFieldSetTitle()
    {
        $langConst = ((string)$this->element['field_set_title'] !== '') ? $this->element['field_set_title'] : 'COM_HYPERPC_PART_FIELDS_VIEW_ORDER_TITLE';
        return Text::_($langConst);
    }
}
