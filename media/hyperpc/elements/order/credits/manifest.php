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

use HYPERPC\Elements\Manager;

return [
    'core'         => true,
    'version'      => '1.0',
    'disable'      => true,
    'group'        => 'order',
    'type'         => 'credits',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params' => [
        'description' => [
            'type'        => 'multilanguagetextarea',
            'label'       => 'COM_HYPERPC_ELEMENT_DESCRIPTION_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_DESCRIPTION_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_DESCRIPTION_HINT'
        ],
        'default' => [
            'type'  => 'elements',
            'group' => Manager::ELEMENT_TYPE_CREDIT
        ]
    ]
];
