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

return [
    'core'        => true,
    'version'     => '1.0',
    'type'        => 'amo_crm',
    'group'       => 'configurator_hook',
    'authorUrl'   => 'https://hyperpc.ru',
    'author'      => 'Sergey Kalistratov',
    'authorEmail' => 'kalistratov.s.m@gmail.com',
    'params'      => [
        'send_data' => [
            'default' => 1,
            'type'    => 'list',
            'options' => [
                -1 => 'JNO',
                0  => 'HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_ONLY_IF_NEED_CONSULTING',
                1  => 'JYES',
            ]
        ],
        'disallow_groups' => [
            'type'      => 'usergrouplist',
            'multiple'  => true,
            'label'     => 'HYPER_ELEMENT_CONFIGURATOR_HOOK_AMO_CRM_PARAM_DISALLOW_GROUP_LABEL'
        ],
        'pipeline_default' => [
            'type' => 'pipelines'
        ],
        'tags' => [
            'type' => 'textarea'
        ]
    ]
];
