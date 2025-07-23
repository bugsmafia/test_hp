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
    'group'        => 'credit',
    'type'         => 'sberbank',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'params'       => [
        'send_mail' => [
            'default' => 1,
            'type'    => 'radio',
            'label'   => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_SEND_MAIL_LABEL',
            'class'   => 'btn-group btn-group-yesno',
            'options' => [
                0 => Text::_('JNO'),
                1 => Text::_('JYES')
            ]
        ],
        'mail_subject' => [
            'type'   => 'text',
            'label'  => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_MAIL_SUBJECT_LABEL',
            'showon' => 'send_mail:1'
        ],
        'mail_recipient' => [
            'type'   => 'textarea',
            'label'  => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_MAIL_RECIPIENT_LABEL',
            'showon' => 'send_mail:1'
        ],
        'separator' => [
            'label' => '',
            'type'  => 'hpseparator',
            'title' => 'HYPER_ELEMENT_CREDIT_ELEMENT_PARAM_STATUS_SEPARATOR_LABEL'
        ],
        'status_deposited_0' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_STATUS_DEPOSITED_0_LABEL'
        ],
        'status_deposited_1' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_STATUS_DEPOSITED_1_LABEL'
        ],
        'status_reversed' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_STATUS_REVERSED_LABEL'
        ],
        'status_declined_by_timeout' => [
            'type'  => 'creditstatus',
            'label' => 'HYPER_ELEMENT_CREDIT_SBERBANK_PARAM_STATUS_DECLINED_BY_TIMEOUT_LABEL'
        ],
    ]
];
