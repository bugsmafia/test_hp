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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;

$displayData = new JSON($displayData);

/** @var JFormFieldCompatibility $field */
$field = $displayData->get('field');
$value = $field->getValue();

$leftFieldClasses = 'control-group jsCompatibilityChooseField';
if (!$value->find('left.group_id')) {
    $leftFieldClasses .= ' hide';
}

$rightFieldClasses = 'control-group jsCompatibilityChooseField';
if (!$value->find('left.group_id')) {
    $rightFieldClasses .= ' hide';
}
?>
<div id="<?= $displayData->get('id') ?>" class="row">
    <div class="col-12 col-lg-6 hp-form-control left">
        <h4><?= Text::_('COM_HYPERPC_CHOOSE_GROUP') ?></h4>
        <div class="control-group">
            <?= $field->renderCatalogGroupList('left.group_id') ?>
        </div>
        <div class="<?= $leftFieldClasses ?>">
            <h4><?= Text::_('COM_HYPERPC_CHOOSE_FIELD') ?></h4>
            <?= $field->renderGroupFieldList('left.field_id') ?>
        </div>
    </div>
    <div class="col-12 col-lg-6 hp-form-control right">
        <h4><?= Text::_('COM_HYPERPC_CHOOSE_GROUP') ?></h4>
        <div class="control-group">
            <?= $field->renderCatalogGroupList('right.group_id') ?>
        </div>
        <div class="<?= $rightFieldClasses ?>">
            <h4><?= Text::_('COM_HYPERPC_CHOOSE_FIELD') ?></h4>
            <?= $field->renderGroupFieldList('right.field_id') ?>
        </div>
    </div>
</div>
