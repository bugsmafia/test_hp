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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldUserFields
 *
 * @since   2.0
 */
class JFormFieldUserFields extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $type = 'UserFields';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app = App::getInstance();
        $db  = $app['db'];

        $query = $db
            ->getQuery(true)
            ->select([
                'id', 'name as alias', 'label', 'title'
            ])
            ->from('#__fields')
            ->where('context = ' . $db->quote('com_users.user'))
            ->where('type IN (' . $db->quote('text') . ', ' . $db->quote('media') . ', ' . $db->quote('url') . ')');

        $fields = $db->setQuery($query)->loadObjectList();

        $options = [[
            'value' => null,
            'text'  => Text::_('COM_HYPERPC_FIELD_SELECT_USER_FIELD')
        ]];

        $optionValue = (isset($this->element['option-value'])) ? (string) $this->element['option-value'] : 'alias';

        foreach ((array) $fields as $data) {
            $data = new JSON((array) $data);
            $options[$data->get($optionValue)]['value'] = $data->get($optionValue);
            $options[$data->get($optionValue)]['text']  = $data->get('title');
        }

        return array_merge(parent::getOptions(), $options);
    }
}
