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
use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldElementLayout
 *
 * @since 2.0
 */
class JFormFieldElementLayout extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'ElementLayout';

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
        $options = [[
            'value' => 'default',
            'text'  => Text::_('COM_HYPERPC_ELEMENTS_DEFAULT_LAYOUT')
        ]];

        $app   = App::getInstance();
        $path  = (isset($this->element['path'])) ? (string) $this->element['path'] : '';

        if ($app['path']->isVirtual($path)) {
            $path = $app['path']->get($path);
        }

        $files = (array) Folder::files($path);

        foreach ($files as $file) {
            $layout = FS::filename($file);

            if (in_array($layout, ['default', 'admin']) || Str::pos($layout, '_') === 0) {
                continue;
            }

            $options[$layout]['value'] = $layout;
            $options[$layout]['text']  = $layout;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
