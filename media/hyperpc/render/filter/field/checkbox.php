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

defined('_JEXEC') or die('Restricted access');

if (!isset($value)) {
    $value = '';
}

if (!isset($count)) {
    $count = '';
}

if (!isset($title)) {
    $title = '';
}

if (!isset($isChecked)) {
    $isChecked = false;
}

$labelClass = [
    'jsFilterButton hp-group-filter__option uk-flex'
];

if ($isChecked) {
    $labelClass[] = 'uk-active';
}

if ($count === 0) {
    if (!$isChecked) {
        $labelClass[] = 'uk-disabled';
    }
}
?>
<label class="<?= implode(' ', $labelClass) ?>">
    <span class="uk-flex-none" style="margin-inline-end: 5px;">
        <input type="checkbox" class="uk-checkbox" value="<?= $value ?>"<?= $isChecked ? ' checked' : '' ?>>
    </span>
    <span>
        <?= $title ?>
        <span class="jsFilterButtonCount uk-text-small uk-text-muted">
            <?= ($count > 0) ? $count : null ?>
        </span>
    </span>
</label>
