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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewMoysklad_Part $this
 */

$formAction = $this->hyper['route']->build([
    'id'                => '%id',
    'view'              => '%view',
    'layout'            => '%layout',
    'product_folder_id' => '%product_folder_id'
]);

?>
<div class="hp-wrapper-form">
    <form action="<?= $formAction ?>" method="post" name="adminForm" id="item-form" class="form-validate ">
        <?= LayoutHelper::render('joomla.edit.part.title_alias', $this) ?>

        <div class="main-card">
            <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_FIELD_DESCRIPTION_LABEL')) ?>
                <div class="row">
                    <div class="col-12 col-lg-9">
                        <?= $this->form->renderField('short_desc', 'translatable_params'); ?>
                        <?= $this->form->renderField('full_width', 'params'); ?>
                        <?= $this->form->renderField('description') ?>

                        <hr />

                        <?= $this->form->renderField('advantages', 'translatable_params'); ?>

                        <hr />

                        <h2><?= Text::_('COM_HYPERPC_TAB_PART_REVIEW') ?></h2>
                        <div class="control-group">
                            <?= $this->form->getInput('review') ?>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <?= LayoutHelper::render('joomla.edit.global', $this) ?>

                        <?php
                        $caseGroups   = $this->hyper['params']->get('product_customization_folders', []);
                        $caseGroups[] = $this->hyper['params']->get('cases_folder', 0);

                        foreach ($this->form->getGroup('images') as $field) {
                            if ($field->fieldname === 'image_assembled') {
                                if (!in_array((string) $this->item->product_folder_id, $caseGroups) && !$this->item->hasOptions()) {
                                    continue;
                                }
                            }

                            echo $field->renderField();
                        }
                        ?>
                    </div>
                </div>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'reload_content', Text::_('COM_HYPERPC_PART_CONTENT_RELOAD_FOR_PRODUCT')) ?>
                <div class="form-vertical">
                    <?= $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => ['reload_content_product_ids']]); ?>
                </div>

                <?php
                $fields = [
                    'reload_content_name' => 'translatable_params',
                    'reload_image' => 'params',
                    'reload_content_short_desc' => 'translatable_params',
                    'reload_content_desc' => 'translatable_params'
                ];

                foreach ($fields as $name => $group) {
                    echo $this->form->renderField($name, $group);
                }
                ?>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('COM_HYPERPC_TAB_PART_OPTIONS')); ?>
                <?= $this->form->getField('option_tml', 'params')->renderField(); ?>
                <hr />
                <?= $this->form->getInput('variants') ?>
                <hr />
                <?= $this->form->getInput('option_fields', 'params') ?>
            <?= HTMLHelper::_('uitab.endTab'); ?>

            <?php if (!in_array((string) $this->item->product_folder_id, (array) $this->hyper['params']->get('notebook_groups'))) : ?>
                <?= HTMLHelper::_('uitab.addTab', 'myTab', 'related_comps', Text::_('COM_HYPERPC_TAB_RELATED_COMPS')); ?>
                    <div class="form-vertical">
                        <?= $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => ['related_comps']]); ?>
                    </div>
                <?= HTMLHelper::_('uitab.endTab'); ?>
            <?php endif; ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'configurator', Text::_('COM_CATEGORIES_FIELD_CONFIGURATOR_LABEL')); ?>
                <?php
                $removeFromConfig = in_array((string) $this->item->product_folder_id, $this->hyper['params']->get('external_parts', [])) ? Text::_('JYES') : Text::_('JNO');
                $removeFromConfigParam = $this->item->params->get('remove_from_configuration');
                ?>
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_params_remove_from_configuration-lbl" for="jform_params_remove_from_configuration">
                            <?= Text::_('COM_HYPERPC_PART_CONFIGURATOR_REMOVE_FROM_CONFIG_LABEL') ?>
                        </label>
                    </div>
                    <div class="controls">
                        <?php
                        echo HTMLHelper::_(
                            'select.genericlist',
                            [
                                -1 => Text::_('COM_HYPERPC_FROM_GLOBAL_PARAMS') . ' (' . $removeFromConfig . ')',
                                0 => Text::_('JNO'),
                                1 => Text::_('JYES')
                            ],
                            $name = 'jform[params][remove_from_configuration]',
                            $attribs = ['class' => 'form-select'],
                            null,
                            null,
                            $selected = isset($removeFromConfigParam) ? $removeFromConfigParam : '-1',
                            null
                        );
                        ?>
                        <div>
                            <small class="form-text">
                                <?= Text::_('COM_HYPERPC_PART_CONFIGURATOR_REMOVE_FROM_CONFIG_DESCRIPTION') ?>
                            </small>
                        </div>
                    </div>
                </div>

                <?php
                $fields = [
                    'enable_quantity' => 'params',
                    'configurator_title' => 'translatable_params',
                    'increase_days_to_build' => 'params'
                ];
                foreach ($fields as $name => $group) {
                    echo $this->form->renderField($name, $group);
                }
                ?>

                <hr />

                <?php
                $casesFolder = $this->hyper['params']->get('cases_folder', 0, 'int');
                if ($this->item->product_folder_id === $casesFolder) {
                    $unpackingFields = ['unpack_from_processingplan'];

                    if ($this->item->optionsCount(true) > 0) {
                        $unpackingFields[] = 'unpacking_variant_alert';
                    } else {
                        $unpackingFields[] = 'unpacked_case_position';
                        $unpackingFields[] = 'unpacked_customization_position';
                    }

                    echo $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => $unpackingFields]);
                }
                ?>
            <?= HTMLHelper::_('uitab.endTab'); ?>

            <?php foreach ($this->form->getFieldsets('com_fields') as $name => $data) : ?>
                <?= HTMLHelper::_('uitab.addTab', 'myTab', $name, Text::_($data->label)); ?>
                    <?= $this->form->renderFieldset($data->name) ?>
                <?= HTMLHelper::_('uitab.endTab'); ?>
            <?php endforeach; ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('COM_HYPERPC_TAB_METADATA')); ?>
                <?= $this->form->renderField('title', 'translatable_params') ?>
                <?= LayoutHelper::render('joomla.edit.part.metadata', $this) ?>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_HYPERPC_FIELDSET_PUBLISHING')); ?>
                <?= LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            <?= HTMLHelper::_('uitab.endTab'); ?>

            <?= HTMLHelper::_('uitab.endTabSet') ?>
        </div>

        <input type="hidden" name="task" />

        <div class="jsSessionToken">
            <?= HTMLHelper::_('form.token') ?>
        </div>
    </form>
</div>
