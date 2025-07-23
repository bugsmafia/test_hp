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

$form      = $displayData->getForm();
$input     = $displayData->app['input'];
$component = $input->getCmd('option', 'com_content');

$saveHistory = JComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('globalFields') ?: [
    ['parent', 'parent_id'],
    ['published', 'state', 'enabled'],
    ['category', 'catid'],
    'featured',
    'sticky',
    'access',
    'language',
    'tags',
    'note',
    'version_note'
];

$hiddenFields = $displayData->get('hidden_fields') ?: [];

if (!$saveHistory) {
    $hiddenFields[] = 'version_note';
}

$html = [];
$html[] = '<fieldset class="form-vertical">';

foreach ($fields as $field) {
    foreach ((array)$field as $f) {
        if ($form->getField($f)) {
            if (in_array($f, $hiddenFields)) {
                $form->setFieldAttribute($f, 'type', 'hidden');
            }

            $html[] = $form->renderField($f);
            break;
        }
    }
}

$html[] = '</fieldset>';

echo implode('', $html);
