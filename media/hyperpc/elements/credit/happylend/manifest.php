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
    'group'        => 'credit',
    'type'         => 'happylend',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params'       => [
        'api_key' => [
            'type' => 'text'
        ],
        'separator' => [
            'label' => '',
            'type'  => 'hpseparator',
            'title' => 'HYPER_ELEMENT_CREDIT_ELEMENT_PARAM_STATUS_SEPARATOR_LABEL'
        ],
        'status_a12' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_A12_LABEL'
        ],
        'status_r16' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_R16_LABEL'
        ],
        'status_r17' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_R17_LABEL'
        ],
        'status_a13' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_A13_LABEL'
        ],
        'status_a11' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_A11_LABEL'
        ],
        'status_r1' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_R1_LABEL'
        ],
        'status_l13' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_L13_LABEL'
        ],
        'status_r18' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_R18_LABEL'
        ],
        'status_c0' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_C0_LABEL'
        ],
        'status_p2' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_HAPPYLEND_PARAM_STATUS_P2_LABEL'
        ]
    ]
];
