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

return [
    'core'         => true,
    'version'      => '1.0',
    'type'         => 'add_user',
    'group'        => 'order_hook',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params' => [
        'alias_mobile' => [
            'type' => 'userfields' /** @deprecated */
        ],
        'update_phone' => [
            'default' => 1,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ],
    ]
];
