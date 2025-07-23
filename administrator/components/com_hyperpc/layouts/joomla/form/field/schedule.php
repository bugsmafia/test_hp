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
 *
 * @var         array $displayData
 * @var         JFormFieldSchedule $field
 * @var         \HYPERPC\Elements\Element $element
 */

use HYPERPC\Data\JSON;

defined('_JEXEC') or die('Restricted access');

$data  = new JSON($displayData);
$field = $data->get('field');
$value = new JSON((array) $data->get('value'));

$daysValue = (array) $value->find('days');
?>
<div class="form-group">
    <?php foreach ($field->getFromToDays() as $dk => $dData) :
        $day       = new JSON($dData);
        $fieldName = $data->get('name') . '[days][]';
        $checked   = in_array((string) $dk, $daysValue);
        ?>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" value="<?= $dk ?>" <?= ($checked) ? 'checked="checked"' : '' ?> name="<?= $fieldName ?>">
        <label class="form-check-label">
            <?= $day->get('label') ?>
        </label>
    </div>
    <?php endforeach; ?>
</div>
<div class="form-group">
    <label for="hp-schedule-from-time">
        Начало работы
    </label>
    <select id="hp-schedule-from-time" class="form-control form-select" name="<?= $fieldName = $data->get('name') . '[from_time]'; ?>">
        <?php foreach ($field->getTime() as $time) :
            $selected = ($value->get('from_time') === $time);
            ?>
            <option value="<?= $time ?>"<?= ($selected) ? 'selected="selected"' : '' ?>>
                <?= $time ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-group">
    <label for="hp-schedule-to-time">
        Завершение работы
    </label>
    <select id="hp-schedule-to-time" class="form-control form-select" name="<?= $fieldName = $data->get('name') . '[to_time]'; ?>">
        <?php foreach ($field->getTime() as $time) :
            $selected = ($value->get('to_time') === $time);
            ?>
            <option value="<?= $time ?>"<?= ($selected) ? 'selected="selected"' : '' ?>>
                <?= $time ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
