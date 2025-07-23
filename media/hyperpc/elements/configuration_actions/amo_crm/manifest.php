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
    'type'         => 'amo_crm',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'group'        => 'configuration_actions',
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
        'pipeline_ds' => [
            'label' => ' ',
            'type'  => 'hpseparator',
            'title' => 'COM_HYPERPC_AMO_CRM_PIPELINE_DS_LABEL'
        ],
        'pipeline_default' => [
            'type' => 'pipelines'
        ],
        'amo_crm_contact_id' => [
            'type' => 'userfields'
        ]
    ]
];
