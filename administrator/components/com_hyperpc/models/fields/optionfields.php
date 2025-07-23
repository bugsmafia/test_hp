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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldOptionFields
 *
 * @property    \HYPERPC\Joomla\Form\Form $form
 *
 * @since       2.0
 */
class JFormFieldOptionFields extends FormField
{

    const FIELD_GROUP_NOTEBOOK = 4;

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'OptionFields';

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
            ->widget('.hp-part-field-fields', 'HyperPC.FieldPartFields', [
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
        $app = App::getInstance();
        $db  = $app['db'];

        $conditions = [
            $db->quoteName('f.state')    . ' = 1',
            $db->quoteName('f.group_id') . ' = ' . self::FIELD_GROUP_NOTEBOOK,
            $db->quoteName('f.context')  . ' = ' . $db->quote(HP_OPTION . '.option')
        ];

        $query = $db
            ->getQuery(true)
            ->select(['f.*'])
            ->from($db->quoteName('#__fields', 'f'))
            ->where($conditions)
            ->order($db->quoteName('f.ordering') . ' ASC');

        return $app['helper']['object']->createList(
            $db->setQuery($query)->loadObjectList(),
            'HYPERPC\Joomla\Model\Entity\Field',
            'id'
        );
    }

    /**
     * Allow to override renderer include paths in child fields
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutPaths()
    {
        $renderer = new FileLayout('default');
        return array_merge($renderer->getDefaultIncludePaths(), [$this->hyper['path']->get('admin:layouts')]);
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
