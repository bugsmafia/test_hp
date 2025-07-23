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
    'type'         => 'amo_crm',
    'group'        => 'order_hook',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params' => [
        'debug' => [
            'default' => 1,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ],
        'send_only_manager' => [
            'default' => 0,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ],
        'name_credit' => [
            'type' => 'text'
        ],
        'name_order' => [
            'type' => 'text'
        ],
        'pipeline_ds' => [
            'label' => ' ',
            'type'  => 'hpseparator',
            'title' => 'HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_PARAM_PIPELINE_DS_LABEL'
        ],
        'pipeline_default' => [
            'type' => 'pipelines'
        ],
        'pipeline_accessories' => [
            'type' => 'pipelines'
        ],
        'pipeline_accessories_upgrade' => [
            'type' => 'pipelines'
        ],
        'pipeline_credit' => [
            'type' => 'pipelines'
        ],
        'pipeline_credit_upgrade' => [
            'type' => 'pipelines'
        ]
    ]
];
