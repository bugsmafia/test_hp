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
    'core'         => true,
    'version'      => '1.0',
    'group'        => 'credit',
    'type'         => 'finboxcredit',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Artem Vyshnevskiy',
    'authorEmail'  => 'avyshnevskiy@hyperpc.ru',
    'params'       => [
        'api_login_debug' => [
            'type' => 'text',
            'showon' => 'debug:1',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_API_LOGIN_DEBUG_LABEL'
        ],
        'api_login_prod' => [
            'type' => 'text',
            'showon' => 'debug:0',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_API_LOGIN_PROD_LABEL'
        ],
        'api_password_debug' => [
            'type' => 'text',
            'showon' => 'debug:1',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_API_PASSWORD_DEBUG_LABEL'
        ],
        'api_password_prod' => [
            'type' => 'text',
            'showon' => 'debug:0',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_API_PASSWORD_PROD_LABEL'
        ],
        'point_id_debug' => [
            'type' => 'text',
            'showon' => 'debug:1',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_POINT_ID_DEBUG_LABEL'
        ],
        'point_id_prod' => [
            'type' => 'text',
            'showon' => 'debug:0',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_POINT_ID_PROD_LABEL'
        ],
        'status_end' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_STATUS_END_LABEL'
        ],
        'status_decline' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_STATUS_DECLINE_LABEL'
        ],
        'status_cancel' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_FINBOXCREDIT_PARAM_STATUS_CANCEL_LABEL'
        ],
    ]
];
