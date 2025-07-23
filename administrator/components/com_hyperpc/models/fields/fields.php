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
use JBZoo\Utils\Str;
use JBZoo\Utils\Filter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldFields
 *
 * @since 2.0
 */
class JFormFieldFields extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Fields';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

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
    protected function getOptions()
    {
        $app = App::getInstance();
        $db  = $app['db'];

        $context  = ((string)$this->element['context'] !== '')   ? (string) $this->element['context'] : HP_OPTION . '.part';
        $assigned = ((string)$this->element['assigned'] !== '')  ? Filter::bool($this->element['assigned']) : false;
        $groupKey = ((string)$this->element['group_key'] !== '') ? $this->element['group_key'] : 'id';
        $valueKey = ((string)$this->element['value_key'] !== '') ? $this->element['value_key'] : 'id';

        $options = [[
            'value' => 1,
            'text'  => Text::_('COM_HYPERPC_SELECT_PART_FIELDS')
        ]];

        $context = Str::low($context);

        $select = ['f.id', 'f.title', 'f.name'];
        if ($assigned === true) {
            $select[] = 'c.category_id';
        }

        $query = $db
            ->getQuery(true)
            ->select($select)
            ->from($db->quoteName('#__fields', 'f'))
            ->where([
                $db->quoteName('f.context') . ' = ' . $db->quote($context),
                $db->quoteName('f.state')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
            ])
            ->order($db->quoteName('f.ordering') . ' ASC');

        $groupId = $app['input']->get($groupKey, 0, 'int');
        if ($assigned === true) {
            $query
                ->join(
                    'LEFT',
                    $db->quoteName('#__fields_categories', 'c') . ' ON (' . $db->quoteName('f.id') . ' = ' . $db->quoteName('c.field_id') . ')'
                )
                ->where([
                    $db->quoteName('c.category_id') . ' = ' . $db->quote($groupId)
                ]);
        }

        $items = $db->setQuery($query)->loadObjectList();

        foreach ((array) $items as $i => $item) {
            $options[$item->id]['value'] = $item->{$valueKey};
            $options[$item->id]['text']  = $item->title;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
