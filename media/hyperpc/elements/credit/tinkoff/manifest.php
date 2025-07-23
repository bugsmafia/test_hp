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
    'group'        => 'credit',
    'type'         => 'tinkoff',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params'       => [
        'shop_id' => [
            'type' => 'text'
        ],
        'showcase_id' => [
            'type' => 'text'
        ],
        'promo_code' => [
            'type' => 'text'
        ],
        'password' => [
            'type' => 'text'
        ],
        'status_inprogress' => [
            'type'  => 'creditstatus'
        ],
        'status_signed' => [
            'type'  => 'creditstatus'
        ],
        'status_issued' => [
            'type'  => 'creditstatus'
        ],
        'status_canceled' => [
            'type'  => 'creditstatus'
        ],
        'status_new' => [
            'type'  => 'creditstatus'
        ],
        'status_rejected' => [
            'type'  => 'creditstatus'
        ],
        'status_approved' => [
            'type'  => 'creditstatus'
        ]
    ]
];
