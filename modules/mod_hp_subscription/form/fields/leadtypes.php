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
 * Class JFormFieldLeadTypes
 *
 * @since 2.0
 */
class JFormFieldLeadTypes extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'LeadTypes';

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
        return array_merge(parent::getOptions(), [
            [
                'value' => 1,
                'text'  => Text::_('MOD_HP_SUBSCRIPTION_TYPE_SUBSCRIPTION')
            ],
            [
                'value' => 2,
                'text'  => Text::_('MOD_HP_SUBSCRIPTION_TYPE_PICK_CONFIGURATION')
            ],
            [
                'value' => 3,
                'text'  => Text::_('MOD_HP_SUBSCRIPTION_TYPE_CONFIGURATOR')
            ],
            [
                'value' => 4,
                'text'  => Text::_('MOD_HP_SUBSCRIPTION_TYPE_NEW_AND_STOCK')
            ],
            [
                'value' => 5,
                'text'  => Text::_('MOD_HP_SUBSCRIPTION_TYPE_COM_CONTENT')
            ]
        ]);
    }
}
