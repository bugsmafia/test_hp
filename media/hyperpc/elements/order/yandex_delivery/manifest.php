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

return [
    'core'         => true,
    'version'      => '1.0',
    'group'        => 'order',
    'type'         => 'yandex_delivery',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params' => [
        'default_method' => [
            'type'    => 'list',
            'options' => [
                'pickup'   => 'HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP',
                'shipping' => 'HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHIPPING'
            ]
        ],
        'show_delivery_options' => [
            'default' => 1,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'label'   => 'HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHOW_OPTIONS',
            'options' => [
                0 => Text::_('JNO'),
                1 => Text::_('JYES')
            ]
        ],
    ]
];
