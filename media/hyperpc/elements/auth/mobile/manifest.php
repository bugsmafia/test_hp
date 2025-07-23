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
    'type'         => 'auth',
    'group'        => 'mobile',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params'       => [
        'field_phone' => [
            'option-value'  => 'id',
            'type'          => 'userfields'
        ],
        'target_sms_ds' => [
            'label' => ' ',
            'type'  => 'hpseparator',
            'title' => 'HYPER_ELEMENT_AUTH_MOBILE_SEPARATOR_TARGET_SMS'
        ],
        'target_login' => [
            'type' => 'text'
        ],
        'target_pwd' => [
            'type' => 'text'
        ],
        'target_sender' => [
            'type' => 'text'
        ],
        'target_message' => [
            'type'      => 'multilanguagetextarea',
            'filter'    => 'JComponentHelper::filterText'
        ]
    ]
];
