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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldWidget
 *
 * @since   2.0
 */
class JFormFieldHPWidget extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPWidget';

    /**
     * Method to get the field input markup.
     *
     * @return  null|string
     *
     * @since   2.0
     */
    protected function getInput()
    {
        /** @noinspection PhpIncludeInspection */
        if (!$app = @include(JPATH_ADMINISTRATOR . '/components/com_widgetkit/widgetkit-app.php')) {
            return null;
        }

        $app->trigger('init.admin', [$app]);

        $value = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');

        $return = [
            '<script src="' . $this->hyper['path']->url('js:widget/fields/widget.js') .'"></script>',
            '<button type="button" class="btn btn-small widgetkit-widget">
                <span>' . Text::_('COM_HYPERPC_SELECT_WIDGET') . '</span>
            </button>',
            '<input type="hidden" name="' . $this->name . '" value="' . $value .'">'
        ];

        return implode(PHP_EOL, $return);
    }
}
