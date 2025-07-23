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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @var         \HYPERPC\Joomla\View\ViewLegacy $displayData
 */

defined('_JEXEC') or die('Restricted access');

$form   = $displayData->getForm();
$fields = $displayData->get('publishingData') ?: [
    'publish_up',
    'publish_down',
    ['created', 'created_time'],
    ['created_by', 'created_user_id'],
    'created_by_alias',
    ['modified', 'modified_time'],
    ['modified_by', 'modified_user_id'],
    'version',
    'hits',
    'id'
];

$hiddenFields = $displayData->get('hidden_fields') ?: [];

foreach ((array) $fields as $field) {
    foreach ((array) $field as $f) {
        if ($form->getField($f)) {
            if (in_array($f, $hiddenFields)) {
                $form->setFieldAttribute($f, 'type', 'hidden');
            }

            echo $form->renderField($f);
            break;
        }
    }
}
