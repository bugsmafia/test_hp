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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

return [
    'core'         => true,
    'version'      => '1.0',
    'group'        => 'data',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'type'         => 'configuration_actions',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params' => [
        'fields_ds' => [
            'label' => ' ',
            'type'  => 'hpseparator',
            'title' => 'COM_HYPERPC_AMO_CRM_CUSTOM_FIELD_SEPARATOR_LABEL'
        ],
        'amo_kakaja_bol' => [
            'type'    => 'text',
            'default' => 410291
        ],
        'amo_cel_pokupki' => [
            'type'    => 'text',
            'default' => 695920
        ],
        'amo_sroshnost_pokupki' => [
            'type'    => 'text',
            'default' => 72967
        ]
    ]
];
