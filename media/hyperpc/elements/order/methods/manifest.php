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
    'core'         => true,
    'version'      => '1.0',
    'group'        => 'order',
    'type'         => 'methods',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'methods'      => [
        0 => 'INDIVIDUAL',
        1 => 'LEGAL',
        2 => 'ENTREPRENEUR'
    ],
    'addFieldPath' => function (ElementOrderMethods $element) {
        return $element->getPath('fields');
    },
    'params' => [
        'default' => [
            'type' => 'methods'
        ]
    ]
];
