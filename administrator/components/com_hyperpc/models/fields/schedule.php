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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\FormField;

/**
 * Class JFormFieldSchedule
 *
 * @since 2.0
 */
class JFormFieldSchedule extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Schedule';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.schedule';

    /**
     * Get time list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getTime()
    {
        return [
            '00:00',
            '00:30',
            '01:00',
            '01:30',
            '02:00',
            '02:30',
            '03:30',
            '04:00',
            '04:30',
            '05:00',
            '05:30',
            '06:00',
            '06:30',
            '07:00',
            '07:30',
            '08:00',
            '08:30',
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:00',
            '12:30',
            '13:00',
            '13:30',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
            '17:30',
            '18:00',
            '18:30',
            '19:00',
            '19:30',
            '20:00',
            '20:30',
            '21:00',
            '21:30',
            '22:00',
            '22:30',
            '23:00',
            '23:30'
        ];
    }

    /**
     * Get day list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFromToDays()
    {

        $days = [
            1 => [
                'label' => Text::_('MON')
            ],
            2 => [
                'label' => Text::_('TUE')
            ],
            3 => [
                'label' => Text::_('WED')
            ],
            4 => [
                'label' => Text::_('THU')
            ],
            5 => [
                'label' => Text::_('FRI')
            ],
            6 => [
                'label' => Text::_('SAT')
            ],
            7 => [
                'label' => Text::_('SUN')
            ]
        ];

        return $days;
    }
}
