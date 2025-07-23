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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var HyperPcViewMoysklad_Product $this
 */

HTMLHelper::_('bootstrap.tab'); /** @todo move to configuration field */

$formAction = $this->hyper['route']->build([
    'id'                => '%id',
    'view'              => '%view',
    'layout'            => '%layout',
    'product_folder_id' => '%product_folder_id'
]);

$imageFields = $this->form->getGroup('images');

$paramImageKeys = [
    'jform_images_image_y_market',
    'jform_images_image_teaser',
    'jform_images_image_full',
    'jform_images_image_og'
];
?>
<div class="hp-wrapper-form">
    <form action="<?= $formAction ?>" method="post" name="adminForm" id="item-form" class="form-validate">
        <?= LayoutHelper::render('joomla.edit.product.title_alias', $this) ?>

        <?= $this->form->renderField('title', 'translatable_params'); ?>

        <div class="main-card">
            <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_HYPERPC_FIELD_DESCRIPTION_LABEL')) ?>
                <div class="control-group">
                    <?= $this->form->getInput('logo', 'params'); ?>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-9">
                        <?= $this->form->renderField('description') ?>
                        <?= $this->form->renderField('capability', 'translatable_params'); ?>
                    </div>
                    <div class="col-12 col-lg-3">
                        <?= LayoutHelper::render('joomla.edit.global', $this) ?>
                        <?php foreach ($imageFields as $key => $field) : ?>
                            <?= in_array($key, $paramImageKeys) ? $field->renderField() : ''; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'tabPhotos', Text::_('COM_HYPERPC_PHOTOS')) ?>
                <div class="row">
                    <div class="col-12 col-xl-6">
                        <?php foreach ($imageFields as $key => $field) : ?>
                            <?= !in_array($key, $paramImageKeys) ? $field->renderField() : ''; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'tabOther', Text::_('COM_HYPERPC_TAB_OTHER')) ?>
                <div class="row">
                    <div class="col-12 col-xl-6">
                        <?= $this->hyper['helper']['layout']->renderFieldset($this, ['fields' => [
                            'hyperbox_type',
                            'assembly_kit',
                            'dimensions'
                        ]]); ?>
                    </div>
                </div>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'tabConfigurator', Text::_('COM_HYPERPC_CONFIGURATOR')) ?>
                <?= $this->form->getInput('configuration') ?>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'tabMetadata', Text::_('COM_HYPERPC_TAB_METADATA')) ?>
                <?= LayoutHelper::render('joomla.edit.metadata', $this) ?>
            <?= HTMLHelper::_('uitab.endTab') ?>

            <?= HTMLHelper::_('uitab.addTab', 'myTab', 'tabPublishing', Text::_('COM_HYPERPC_FIELDSET_PUBLISHING')); ?>
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
