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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldModules
 *
 * @since   2.0
 */
class JFormFieldModules extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Modules';

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
     * @return array
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $module = ((string) $this->element['module']) ? (string) $this->element['module'] : null;

        $app = App::getInstance();
        $db  = $app['db'];

        $options = [[
            'value' => 0,
            'text'  => Text::_('COM_HYPERPC_SELECT_MODULE')
        ]];

        if (!empty($module)) {
            /** @var \JDatabaseQueryMysqli $query */
            $query = $db->getQuery(true)
                ->select(['m.id', 'm.title'])
                ->from($db->quoteName('#__modules', 'm'))
                ->where([
                    $db->quoteName('m.client_id')   . ' = ' . $db->quote(0),
                    $db->quoteName('m.module')      . ' = ' . $db->quote($module),
                    $db->quoteName('m.published')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
                ]);

            $modules = $db->setQuery($query)->loadObjectList();
            foreach ($modules as $item) {
                $options[$item->id]['value'] = $item->id;
                $options[$item->id]['text']  = $item->title;
            }
        }

        return $options;
    }
}
