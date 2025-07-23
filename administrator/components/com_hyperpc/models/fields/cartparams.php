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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldCartParams
 *
 * @since 2.0
 */
class JFormFieldCartParams extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'CartParams';

    /**
     * Get field label.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getLabel()
    {
        return Text::_('COM_HYPERPC_CART_PARAMS_LABEL');
    }

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
        HTMLHelper::_('jquery.ui', ['core', 'sortable']);

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/cart-params.js')
            ->widget('.hp-field-basket-params', 'HyperPC.FieldCartParams', [
                'formFieldName'           => $this->name,
                'errorMessage'            => Text::_('COM_HYPERPC_ALERT_ERROR'),
                'confirmMessage'          => Text::_('COM_HYPERPC_ARE_YOU_SURE'),
                'aliasMessage'            => Text::_('COM_HYPERPC_ALERT_ALIAS_MESSAGE'),
                'placeholderTextMultiple' => Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'),
                'aliasMessageError'       => Text::_('COM_HYPERPC_ALERT_ALIAS_MESSAGE_ERROR')
            ]);

        return $this->hyper['helper']['render']->render('joomla/form/field/cart/params.php', [
            'fieldData' => $this
        ], 'layouts');
    }
}
