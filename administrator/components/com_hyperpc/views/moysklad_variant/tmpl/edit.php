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
 * @var HyperPcViewMoysklad_Variant $this
 */

$formAction = $this->hyper['route']->build([
    'id'                => '%id',
    'view'              => '%view',
    'layout'            => '%layout',
    'part_id'           => '%part_id',
    'product_folder_id' => '%product_folder_id'
]);

$parentPart = $this->item->getPart();
?>
<div class="hp-wrapper-form">
    <form action="<?= $formAction ?>" method="post" name="adminForm" id="item-form" class="form-validate">
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
                            if (!in_array($this->hyper['input']->get('product_folder_id', 1), $caseGroups)) {
                                continue;
                            }
                        }

                        echo $field->renderField();
                    }
                    ?>
                </div>
            </div>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'related_comps', Text::_('COM_HYPERPC_TAB_RELATED_COMPS')); ?>
            <div class="form-vertical">
                <?= $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => ['related_comps']]); ?>
            </div>
            <?= HTMLHelper::_('uitab.endTab'); ?>

            <?php $this->ignore_fieldsets = array('translatable_params'); ?>
            <?= LayoutHelper::render('joomla.edit.fields', $this) ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('COM_HYPERPC_TAB_METADATA')) ?>
            <?= LayoutHelper::render('joomla.edit.part.metadata', $this) ?>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'params', Text::_('COM_HYPERPC_TAB_PARAMS')) ?>

            <?= $this->form->renderField('configurator_title', 'translatable_params'); ?>

            <?php
            $paramsField = ['mini_image'];
            $groupId = $this->hyper['input']->get('product_folder_id', 0, 'int');

            $customizationGroups = $this->hyper['params']->get('product_customization_folders', []);
            if (in_array($groupId, $customizationGroups)) {
                $paramsField[] = 'related_case';
            }

            echo $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => $paramsField]);

            if ($parentPart->params->get('unpack_from_processingplan', false, 'bool')) {
                echo '<hr>';
                echo $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => [
                    'unpacking_variant_alert',
                    'unpacked_case_position',
                    'unpacked_customization_position'
                ]]);
            }
            ?>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.endTabSet') ?>
        </div>

        <input type="hidden" name="task" />
        <input type="hidden" name="option_id" value="<?= $this->hyper['input']->get('id', 0) ?>" />

        <div class="jsSessionToken">
            <?= HTMLHelper::_('form.token') ?>
        </div>
    </form>
</div>
