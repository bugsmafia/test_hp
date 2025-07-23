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
    'type'         => 'commercial_proposal',
    'group'        => 'configuration_actions',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Sergey Kalistratov',
    'authorEmail'  => 'kalistratov.s.m@gmail.com',
    'addFieldPath' => function (\ElementConfigurationActionsCommercialProposal $element) {
        return [
            $element->getPath('fields')
        ];
    },
    'params' => [
        'layout' => [
            'default' => 1,
            'type'    => 'list',
            'label'   => 'JGLOBAL_FIELD_LAYOUT_LABEL',
            'options' => [
                'default' => 'JDEFAULT',
                '2025'    => '2025'
            ]
        ],
        'content_category_id' => [
            'addfieldprefix'    => 'Joomla\Component\Categories\Administrator\Field',
            'type'              => 'modal_category',
            'extension'         => 'com_content',
            'required'          => true,
            'select'            => true,
            'new'               => false,
            'edit'              => true,
            'clear'             => true,
            'description'       => 'JGLOBAL_CHOOSE_CATEGORY_DESC',
            'label'             => 'JGLOBAL_CHOOSE_CATEGORY_LABEL'
        ],
        'preview' => [
            'class' => 'btn btn-outline-info',
            'title' => Text::_('COM_HYPERPC_FIELD_PREVIEW_LABEL'),
            'type'  => 'preview',
            'task'  => 'pdf'
        ],
        'price_gradation' => [
            'type'        => 'subform',
            'description' => Text::_('COM_HYPERPC_FIELD_PRICE_GRADATION_DESC'),
            'multiple'    => true,
            'formsource'  => "administrator/components/com_hyperpc/models/forms/subforms/commercial_proposal_price_gradation.xml"
        ]
    ]
];
