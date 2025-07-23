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

use Joomla\CMS\Language\Text;

return [
    'version'      => '1.0',
    'group'        => 'core',
    'type'         => 'checkbox',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params'       => [
        'description' => [
            'type'        => 'multilanguagetextarea',
            'label'       => 'COM_HYPERPC_ELEMENT_DESCRIPTION_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_DESCRIPTION_DESC',
            'hint'        => 'COM_HYPERPC_ELEMENT_DESCRIPTION_HINT'
        ],
        'required' => [
            'default' => 1,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'label'   => Text::_('COM_HYPERPC_CART_ELEMENT_REQUIRED'),
            'options' => [
                0 => Text::_('JNO'),
                1 => Text::_('JYES')
            ],
        ],
        'selected' => [
            'default' => 1,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => Text::_('JNO'),
                1 => Text::_('JYES')
            ],
        ]
    ]
];
