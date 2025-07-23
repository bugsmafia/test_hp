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
 * @author      Artem Vyshnevskiy
 */

return [
    'core'          => true,
    'version'       => '1.0',
    'type'          => 'tabby',
    'group'         => 'payment',
    'authorUrl'     => 'https://hyperpc.ru',
    'author'        => 'Artem Vyshnevskiy',
    'authorEmail'   => 'artem_v@hyperpc.ru',
    'params'        => [
        'is_test' => [
            'default' => 0,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ],
        'api_public_key' => [
            'type' => 'text',
        ],
        'api_secret_key' => [
            'type' => 'text',
        ],
        'webhook_id' => [
            'type' => 'text',
            'readonly' => 'readonly'
        ],
    ]
];
