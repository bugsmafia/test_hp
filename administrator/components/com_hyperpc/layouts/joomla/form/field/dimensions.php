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
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;

/**
 * @var array $displayData
 */

$data      = new Data($displayData);
$fieldName = $data->get('name');
$value     = new Data($data->get('value'));
?>
<div class="hp-field-dimensions">
    <div class="control-group">
        <?= $data->get('image_output') ?>
    </div>
    <div class="control-group">
        <div class="input-group">
            <span class="input-group-text"><?= JText::_('COM_HYPERPC_IMAGE_ALT') ?></span>
            <input class="form-control" type="text" name="<?= $fieldName . '[image_alt]' ?>" value="<?= $value->get('image_alt') ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="input-group">
            <span class="input-group-text"><?= JText::_('COM_HYPERPC_HEIGHT') ?></span>
            <input class="form-control" type="text" name="<?= $fieldName . '[height]' ?>" value="<?= $value->get('height') ?>" />
            <span class="input-group-text"><?= JText::_('COM_HYEPRPC_WEIGHT_CM') ?></span>
        </div>
    </div>
    <div class="control-group">
        <div class="input-group">
            <span class="input-group-text"><?= JText::_('COM_HYPERPC_WIDTH') ?></span>
            <input class="form-control" type="text" name="<?= $fieldName . '[width]' ?>" value="<?= $value->get('width') ?>" />
            <span class="input-group-text"><?= JText::_('COM_HYEPRPC_WEIGHT_CM') ?></span>
        </div>
    </div>
    <div class="control-group">
        <div class="input-group">
            <span class="input-group-text"><?= JText::_('COM_HYPERPC_DEPTH') ?></span>
            <input class="form-control" type="text" name="<?= $fieldName . '[depth]' ?>" value="<?= $value->get('depth') ?>" />
            <span class="input-group-text"><?= JText::_('COM_HYEPRPC_WEIGHT_CM') ?></span>
        </div>
    </div>
    <div class="control-group last-element">
        <div class="input-group">
            <span class="input-group-text"><?= JText::_('COM_HYPERPC_WEIGHT') ?></span>
            <input class="form-control" type="text" name="<?= $fieldName . '[weight]' ?>" value="<?= $value->get('weight') ?>" />
            <span class="input-group-text"><?= JText::_('COM_HYEPRPC_WEIGHT_KG') ?></span>
        </div>
    </div>
</div>