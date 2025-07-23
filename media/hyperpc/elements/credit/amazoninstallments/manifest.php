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

use Joomla\CMS\Language\Text;

return [
    'core'         => true,
    'version'      => '1.0',
    'group'        => 'credit',
    'type'         => 'amazoninstallments',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Artem Vyshnevskiy',
    'authorEmail'  => 'artem_v@hyperpc.ru',
    'params'       => [
        'render_on_statuses' => [
            'type'      => 'orderstatus',
            'multiple'  => true,
            'layout'    => 'joomla.form.field.list-fancy-select'
        ],
        'access_code' => [
            'type' => 'text'
        ],
        'merchant_identifier' => [
            'type' => 'text'
        ],
        'sha_type' => [
            'default'       => 'sha256',
            'type'          => 'list',
            'options' => [
                'sha128' => 'SHA-128',
                'sha256' => 'SHA-256',
                'sha512' => 'SHA-512'
            ]
        ],
        'sha_request_phrase' => [
            'type' => 'text'
        ],
        'sha_response_phrase' => [
            'type' => 'text'
        ],
        'status_success' => [
            'type'  => 'creditstatus'
        ],
    ]
];
