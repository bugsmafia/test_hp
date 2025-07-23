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
    'core'         => true,
    'version'      => '1.0',
    'type'         => 'auth',
    'group'        => 'email',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params'       => [
        'create_new_profile' => [
            'default' => 0,
            'type'    => 'radio',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => Text::_('JNO'),
                1 => Text::_('JYES')
            ],
        ],
        'mail_auth_sbj' => [
            'type'      => 'multilanguagetext',
            'label'     => 'COM_HYPERPC_PARAM_USER_MAIL_AUTH_SBJ_LABEL'
        ],
        'mail_auth_msg' => [
            'type'      => 'multilanguageeditor',
            'filter'    => 'JComponentHelper::filterText',
            'label'     => 'COM_HYPERPC_PARAM_USER_MAIL_AUTH_MSG_LABEL'
        ],
        'mail_change_old_ds' => [
            'label' => ' ',
            'type'  => 'hpseparator',
            'title' => 'HYPER_ELEMENT_AUTH_EMAIL_PARAM_MAIL_CHANGE_OLD_DC_TITLE'
        ],
        'mail_change_old_sbj' => [
            'type' => 'multilanguagetext'
        ],
        'mail_change_old_msg' => [
            'type'      => 'multilanguageeditor',
            'filter'    => 'JComponentHelper::filterText'
        ]
    ]
];
