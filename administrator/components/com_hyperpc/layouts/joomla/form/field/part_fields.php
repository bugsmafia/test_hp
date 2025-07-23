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
 * @var         Field $field
 * @var         array $displayData
 */

use JBZoo\Data\Data;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\Field;

defined('_JEXEC') or die('Restricted access');

$data    = new Data($displayData);
$value   = (array) $data->get('value', []);
$fields  = $data->get('field')->getFields();
$fieldId = $data->get('field')->id;
?>
<style>
    #hp-saved-list {
        padding: 2px 2px 0 0;
    }

    .hp-part-connected-sortable li {
        border: 1px solid #e5e5e5;
        padding: 2px 8px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        margin-bottom: 2px;
    }
</style>
<div id="<?= $fieldId ?>" class="row hp-part-field-fields">
    <div class="col-12 col-lg-8">
        <fieldset>
            <legend><?= $data->get('field')->getFieldSetTitle() ?></legend>
            <ul id="hp-saved-list" class="hp-part-connected-sortable unstyled">
                <?php if (count($value)) : ?>
                    <?php foreach ((array) $value as $fieldId) :
                        if (!isset($fields[$fieldId])){
                            continue;
                        }

                        $field = $fields[$fieldId];
                        ?>
                        <li class="hp-part-field-<?= $field->id ?>">
                            <a href="#" class="hp-icon hp-icon-sort jsFieldSort"></a>
                            <?= $field->label ?>
                            <a href="#" class="jsRemoveField pull-right">
                                <i class="hp-icon hp-icon-remove"></i>
                            </a>
                            <input type="hidden" name="<?= $data->get('name') ?>" value="<?= $field->id ?>"/>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </fieldset>
    </div>
    <div class="col-12 col-lg-4">
        <fieldset>
            <legend><?= Text::_('COM_HYPERPC_PART_FIELDS_ALLOWED_TITLE') ?></legend>
            <select class="jsPartFieldList w-100 form-select">
                <?php if (count($fields)) : ?>
                    <option value="0">
                        <?= Text::_('COM_HYPERPC_PART_FIELDS_SELECT_FIELD') ?>
                    </option>
                    <?php foreach ($fields as $field) : ?>
                        <option value="<?= $field->id ?>">
                            <?= $field->label ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </fieldset>
    </div>
</div>
