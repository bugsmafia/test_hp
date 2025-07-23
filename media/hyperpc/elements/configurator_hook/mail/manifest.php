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

use HYPERPC\Elements\Element;

return [
    'core'        => true,
    'version'     => '1.0',
    'type'        => 'mail',
    'group'       => 'configurator_hook',
    'authorUrl'   => 'https://hyperpc.ru',
    'author'      => 'Sergey Kalistratov',
    'authorEmail' => 'kalistratov.s.m@gmail.com',
    'params'      => [
        'subject' => [
            'type' => 'multilanguagetext'
        ],
        'pdf_separator' => [
            'label' => ' ',
            'type'  => 'hpseparator',
            'title' => 'HYPER_ELEMENT_CONFIGURATOR_HOOK_MAIL_PARAM_PDF_SEPARATOR_TITLE'
        ],
        'pdf_attach' => [
            'default' => 0,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ],
        'pdf_layout' => function (Element $element) {
            return [
                'type'  => 'elementlayout',
                'label' => 'COM_HYPERPC_ELEMENTS_LAYOUT',
                'path'  => $element->hyper['path']->get('printer:Configurator/tmpl')
            ];
        }
    ]
];
