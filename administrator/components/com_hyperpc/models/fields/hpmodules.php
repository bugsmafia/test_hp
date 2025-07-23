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
 * Class JFormFieldHPModules
 *
 * @since   2.0
 */
class JFormFieldHPModules extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPModules';

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
     * @since   2.0
     */
    protected function getOptions()
    {
        $app     = App::getInstance();
        $modules = $app['helper']['module']->load();

        $list = ['' => Text::_('COM_HYPERPC_CHOOSE_MODULE')];
        foreach ($modules as $module) {
            $list[$module->id] = $module->title;
        }

        return $list;
    }
}
