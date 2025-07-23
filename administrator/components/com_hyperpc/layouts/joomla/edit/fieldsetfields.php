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
 * @var         JLayoutFile $this
 * @var         JViewLegacy $displayData
 */

defined('_JEXEC') or die('Restricted access');

$app  = JFactory::getApplication();
$form = $displayData->getForm();

$name     = $displayData->get('fieldset');
$fieldSet = $form->getFieldset($name);

$allowedFields = $this->getOptions()->get('fields', []);

if (empty($fieldSet)) {
    return;
}

$ignoreFields = $displayData->get('ignore_fields') ?: [];
$extraFields  = $displayData->get('extra_fields') ?: [];

if ($displayData->get('show_options', 1)) {
    $html = [];
    /** @var JFormField $field */
    foreach ($fieldSet as $field) {
        if (in_array($field->id, $allowedFields)) {
            $html[] = $field->renderField();
        }
    }

    echo implode('', $html);
} else {
    $html   = [];
    $html[] = '<div style="display:none;">';
    foreach ($fieldSet as $field) {
        $html[] = $field->input;
    }
    $html[] = '</div>';

    echo implode('', $html);
}
