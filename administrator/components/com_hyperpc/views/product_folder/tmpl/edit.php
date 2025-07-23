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
 * @var HyperPcViewProduct_Folder   $this
 * @var JFormFieldText              $titleFiled
 */

$formAction = $this->hyper['helper']['route']->url([
    'view'   => 'product_folder',
    'layout' => 'edit',
    'id'     => $this->hyper['input']->get('id', 0)
]);

$titleFiled = $this->form->getField('title');
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="item-form" class="form-validate hp-wrapper-form">
    <?php if ($this->item->isRoot()) : ?>
        <div class="page-header">
            <h1><?= Text::_('COM_HYPERPC_ROOT_CATEGORY_TITLE') ?></h1>
            <input type="hidden" name="<?= $titleFiled->name ?>" value="<?= $this->form->getData()->get('title') ?>" />
        </div>
    <?php else : ?>
        <?= LayoutHelper::render('joomla.edit.title_alias', $this) ?>
    <?php endif; ?>
    <div class="form-horizontal main-card">
        <?= HtmlHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JCATEGORY')) ?>
        <div class="row">
            <div class="col-12 col-lg-9">
                <div class="control-label">
                    <?= $this->form->getLabel('description') ?>
                </div>
                <div class="controls">
                    <?= $this->form->getInput('description') ?>
                </div>
            </div>
            <?php if (!$this->item->isRoot()) : /** Begin show params only for parent categories */ ?>
                <div class="col-12 col-lg-3">
                    <?= LayoutHelper::render('joomla.edit.global', $this);?>
                </div>
            <?php endif; /** End show params only for parent categories */ ?>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'params', Text::_('COM_HYPERPC_TAB_PARAMS')) ?>
            <?php
            $fields = [
                'title' => 'translatable_params',
                'show_title' => 'params',
                'image' => 'params',
                'image_alt' => 'translatable_params',
                'show_sub_categories' => 'params',
                'show_elements' => 'params',
                'filter_enabled_stores' => 'params',
                'google_category_id' => 'params'
            ];
            foreach ($fields as $name => $group) {
                $field = $this->form->getField($name, $group);
                echo $field ? $field->renderField() : '';
            }
            ?>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'params_parts', Text::_('COM_HYPERPC_TAB_PARAMS_PARTS')) ?>
            <?php
            $fields = [
                'preorder' => 'params',
                'only_upgrade' => 'params',
                'show_inactive' => 'params',
                'part_filters_separator' => 'params',
                'use_parts_filter' => 'params',
                'parts_allow_filter_by_shops' => 'params',
                'parts_allow_filter_by_price' => 'params',
                'allowed_filters' => 'params',
                'filter_type' => 'params',
                'part_configurator_separator' => 'params',
                'configurator_filters' => 'params',
                'configurator_divide_by_availability' => 'params',
                'configurator_min_count' => 'params',
                'configurator_max_count' => 'params',
                'product_option_select_name' => 'translatable_params',
            ];
            foreach ($fields as $name => $group) {
                $field = $this->form->getField($name, $group);
                echo $field ? $field->renderField() : '';
            }
            ?>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'params_services', Text::_('COM_HYPERPC_TAB_PARAMS_SERVICES')) ?>
            <?php
            $fields = [
                'add_service_text' => 'translatable_params',
                'service_desc' => 'translatable_params'
            ];
            foreach ($fields as $name => $group) {
                $field = $this->form->getField($name, $group);
                echo $field ? $field->renderField() : '';
            }
            ?>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'params_other', Text::_('COM_HYPERPC_TAB_PARAMS_PARTS_SERVICES')) ?>
            <?php
            $fields = [
                'retail' => 'params',
                'group_cols' => 'params',
                'parts_order' => 'params',
                'part_fields' => 'params',
                'amo_product_custom_field' => 'params',
                'show_in_teaser' => 'params',
                'show_in_teaser_large' => 'params',
                'show_in_teaser_complectation' => 'params',
                'teaser_table_setting_separator' => 'params',
                'heading_in_teasers_table' => 'translatable_params',
                'teaser_table_field' => 'params',
                'configurator_group_setting_separator' => 'params',
                'configurator_layout' => 'params',
                'configurator_cols' => 'params',
                'configurator_enable_toggle' => 'params',
                'configurator_show_part_info' => 'params',
                'configurator_can_deselected' => 'params',
                'configurator_desc_heading' => 'translatable_params',
                'configurator_desc' => 'translatable_params',
                'mini_configurator_separator' => 'params',
                'mini_allowed_part_fields' => 'params'
            ];
            foreach ($fields as $name => $group) {
                $field = $this->form->getField($name, $group);
                echo $field ? $field->renderField() : '';
            }
            ?>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'params_products', Text::_('COM_HYPERPC_TAB_PARAMS_PRODUCTS')) ?>
            <?php
            $fields = [
                'configurator_type' => 'params',
                'product_type' => 'params',
                'general_review' => 'params',
                'complectation_fields' => 'params',
                'days_to_build_min' => 'params',
                'days_to_build_max' => 'params',
                'hyperbox_type' => 'params',
                'assembly_kit' => 'params',
                'products_cols' => 'params',
                'teasers_type' => 'params',
                'groups_in_teaser_table' => 'params',
                'short_desc' => 'translatable_params',
                'content_before_promo' => 'translatable_params',
                'content_after_items' => 'translatable_params',
                'configurator_separator' => 'params',
                'configurator_instock_default' => 'params',
                'configurator_complectations' => 'params',
                'folders_of_platform_components' => 'params'
            ];
            foreach ($fields as $name => $group) {
                $field = $this->form->getField($name, $group);
                echo $field ? $field->renderField() : '';
            }
            ?>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('COM_HYPERPC_TAB_METADATA')) ?>
            <?= LayoutHelper::render('joomla.edit.metadata', $this) ?>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_HYPERPC_FIELDSET_PUBLISHING')); ?>

        <div class="row form-horizontal-desktop">
            <div class="col-12 col-lg-6">
                <?= LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab'); ?>
        <?= HTMLHelper::_('uitab.endTabSet') ?>
    </div>

    <input type="hidden" name="task" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
