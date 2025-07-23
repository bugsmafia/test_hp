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

use JBZoo\Data\Data;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 */

$data       = new Data($displayData);
$maxEditors = \JFormFieldTabs::MAX_HIDDEN_EDITORS;
$value      = is_array($data->get('value', [])) ? $data->get('value', []) : [];
$fieldId    = str_replace([']', '['], '_', $data->get('name'));

/** @var Editor $editor */
$editor = $data->get('field')->getEditor();
?>
<div class="jsTabs">
    <div class="field-tabs">
        <?php if (count($value) > 0) :
            $i = 0;
            ?>
            <?php foreach ($value as $val) :
                $val = new Data($val);
                $i++;

                $subFieldName = $data->get('name') . '[' . $i . ']';
                $subFieldId   = $fieldId . '__review' . $i . '__description'
                ?>
                <div class="field-content jsIsSaved">
                    <div class="row">
                        <div class="col-12 pb-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="control-group">
                                        <label class="control-label hasTooltip"
                                               title="<?= Text::_('COM_HYPERPC_PART_OPTION_NAME_TITLE') ?>">
                                            <?= Text::_('COM_HYPERPC_PART_OPTION_NAME') ?>
                                        </label>
                                        <div class="controls">
                                            <input type="text" name="<?= $subFieldName . '[name]' ?>" value="<?= $val->get('name') ?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="control-group">
                                        <label class="control-label hasTooltip"
                                               title="<?= Text::_('COM_HYPERPC_PART_OPTION_SORTING_DESC') ?>">
                                            <?= Text::_('COM_HYPERPC_PART_OPTION_SORTING_LABEL') ?>
                                        </label>
                                        <div class="controls">
                                            <input type="text" name="<?= $subFieldName . '[sorting]' ?>" value="<?= $val->get('sorting', $i) ?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label hasTooltip" for="option-price"
                                       title="<?= Text::_('COM_HYPERPC_PART_OPTION_DESCRIPTION_TITLE') ?>">
                                    <?= Text::_('COM_HYPERPC_PART_OPTION_DESCRIPTION') ?>
                                </label>
                                <div class="controls">
                                    <?= $editor->display($subFieldName . '[description]', $val->get('description'), '100%', '300px', 80, 15, true, $subFieldId) ?>
                                </div>
                            </div>
                            <div class="control-group last">
                                <div class="controls">
                                    <div class="field-tabs-nav">
                                        <a href="#" class="btn jsRemove">
                                            <span class="icon-cancel"></span>
                                            <?= Text::_('COM_HYPERPC_ADD_REMOVE_TAB') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr />
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php for ($index = count($value) + 1; $index < count($value) + $maxEditors; $index++) : ?>
            <?php
                $subFieldName = $data->get('name') . '[' . $index . ']';
                $subFieldId   = $fieldId . '__review' . $index . '__description'
            ?>
            <div class="field-content hidden">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="control-group">
                                    <label class="control-label hasTooltip" for="tab-name"
                                           title="<?= Text::_('COM_HYPERPC_PART_OPTION_NAME_TITLE') ?>">
                                        <?= Text::_('COM_HYPERPC_PART_OPTION_NAME') ?>
                                    </label>
                                    <div class="controls">
                                        <input type="text" name="<?= $subFieldName . '[name]' ?>" id="tab-name" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="control-group">
                                    <label class="control-label hasTooltip" for="tab-name"
                                           title="<?= Text::_('COM_HYPERPC_PART_OPTION_SORTING_DESC') ?>">
                                        <?= Text::_('COM_HYPERPC_PART_OPTION_SORTING_LABEL') ?>
                                    </label>
                                    <div class="controls">
                                        <input type="text" name="<?= $subFieldName . '[sorting]' ?>" value="<?= $index ?>" id="tab-sorting" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label hasTooltip"
                                   title="<?= Text::_('COM_HYPERPC_PART_OPTION_DESCRIPTION_TITLE') ?>">
                                <?= Text::_('COM_HYPERPC_PART_OPTION_DESCRIPTION') ?>
                            </label>
                            <div class="controls">
                                <?= $editor->display($subFieldName . '[description]', '', '100%', '300px', 80, 15, true, $subFieldId) ?>
                            </div>
                        </div>
                        <div class="control-group last">
                            <div class="controls">
                                <div class="field-tabs-nav">
                                    <a href="#" class="btn jsRemove">
                                        <span class="icon-cancel"></span>
                                        <?= Text::_('COM_HYPERPC_ADD_REMOVE_TAB') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
            </div>
        <?php endfor; ?>
        <div class="field-tabs-nav">
            <a href="#" class="btn jsAddNew">
                <span class="icon-plus"></span>
                <?= Text::_('COM_HYPERPC_ADD_NEW_TAB') ?>
            </a>
        </div>
    </div>
</div>
