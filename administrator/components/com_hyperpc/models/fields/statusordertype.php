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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * JFormFieldStatusOrderType class.
 *
 * @since   2.0
 */
class JFormFieldStatusOrderType extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'StatusOrderType';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        return [
            [
                'value' => HP_STATUS_UNPUBLISHED,
                'text'  => Text::_('COM_HYPERPC_1C_SELECT_ORDER_STATUS_TYPE_TITLE')
            ],
            [
                'value' => 1,
                'text'  => Text::_('COM_HYPERPC_1C_SELECT_ORDER_STATUS_TYPE_CREDIT')
            ],
            [
                'value' => 2,
                'text'  => Text::_('COM_HYPERPC_1C_SELECT_ORDER_STATUS_TYPE_PROIZVODSTVO')
            ],
        ];
    }
}
