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
 * @var         Registry                    $data
 * @var         PartMarker                  $part
 * @var         JFormFieldRelatedComps      $field
 * @var         array                       $fields
 * @var         ProductFolder               $category
 * @var         array                       $displayData
 */

use HYPERPC\App;
use JBZoo\Utils\Arr;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

$products   = $displayData['products'];
$field      = $displayData['field'];
$categories = array_chunk($displayData['categories'], 4);

$app       = App::getInstance();
$valueIn   = $field->getCurrentValue($field::CONTROL_TYPE_PARTS);
$valueDef  = $field->getCurrentValue($field::CONTROL_TYPE_DEFAULT);
$valueMini = $field->getCurrentValue($field::CONTROL_TYPE_MINI);
$data      = $field->getForm()->getData();
$optionId  = null;

if ($partId = (int) $data->get('part_id')) {
    $optionId = (int) $data->get('id');
} else {
    $partId = (int) $data->get('id');
}

$partHelper = $field->getPartHelper();

/** @var PartMarker $part */
$part = $field->getPart($partId);

$options = [];
if (!$part->isService()) {
    $options = $part->getOptions();
}

$notProcessComps = [];

$groupId  = (int) $part->getFolderId();
$partsUrl = $field->getGroupModalUrl($groupId);
?>
<script>
    jQuery(function($) {
        $('.jsRelatedCompsCheckAll').on('click', function () {
            var checkbox = $(this)
                .closest('.hp-part-related-comps')
                .find('.jsInConfiguration, .jsMiniConfiguration');

            if ($(this).prop('checked')) {
                checkbox.each(function () {
                    if ($(this).attr('disabled') !== 'disabled') {
                        $(this).prop('checked', true);
                    }
                });

            } else {
                checkbox.each(function () {
                    if ($(this).attr('disabled') !== 'disabled') {
                        $(this).prop('checked', false);
                    }
                });
            }
        });

        $('.jsSelectAllCategory').on('click', function () {
            let well      = $(this).closest('.jsCategoryCard'),
                isChecked = $(this).prop('checked');

            well.find('.jsHpChoose').each(function () {
                $(this).prop('checked', isChecked);
            });
        });
    });
</script>
<div class="hp-part-related-comps">
    <?php if (empty($products)) : ?>
        <?= Text::_('COM_HYPERPC_ERROR_PRODUCTS_NOT_FOUND') ?>
    <?php else : ?>
        <input type="hidden" name="jform[reload_default_part]" value="<?= $part->id ?>" />
        <div class="row">
            <div class="col-12">
                <div class="btn-group">
                    <label class="btn btn-outline-primary">
                        <input class="jsRelatedCompsCheckAll" type="checkbox" />
                        <?= Text::_('COM_HYPERPC_FIELD_RELATED_PRODUCTS_SELECT_ALL') ?>
                    </label>
                    <span class="btn btn-success jsSendRelatedData" data-field-type="<?= $displayData['fieldType'] ?>">
                        <?= Text::_('COM_HYPERPC_FIELD_RELATED_PRODUCTS_INSTALL_ITEM') ?>
                    </span>
                    <?php if ($optionId === null) : ?>
                    <a data-type="iframe" data-src="<?= $partsUrl ?>" class="btn btn-success jsSetByExample">
                        <?= Text::_('COM_HYPERPC_FIELD_RELATED_PRODUCTS_INSTALL_ITEM_BY_EXAMPLE') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <hr />
        <?php foreach ($categories as $row) : ?>
            <div class="row">
                <?php foreach ($row as $category) : ?>
                    <div class="col-12 col-lg-3">
                        <div class="jsCategoryCard card card-body bg-light">
                            <h4><?= $category->title ?></h4>
                            <?php if (isset($products[$category->id])) : ?>
                            <label>
                                <input type="checkbox" name="" class="jsSelectAllCategory"/>
                                <?= Text::_('COM_HYPERPC_FIELD_RELATED_PRODUCTS_SELECT_ALL') ?>
                            </label>
                            <table>
                                <thead>
                                <tr>
                                    <th>IN</th>
                                    <th>|</th>
                                    <th>D</th>
                                    <th>|</th>
                                    <th>M</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <?php foreach ((array) $products[$category->id] as $product) :
                                    $defaultOption  = null;
                                    $defaultOptions = (array) $product->configuration->get('option');

                                    $partConfig = $product->configuration->get('parts');
                                    $optionsConfig = $product->configuration->get('options');
                                    $optionsMiniConfig = $product->configuration->get('options_mini');
                                    $partMiniConfig = $product->configuration->get('parts_mini');
                                    $partOptionsConfig = $product->configuration->get('part_options');

                                    foreach ($defaultOptions as $optionPartId => $defaultOptionId) {
                                        if ((int) $optionPartId === $part->id && array_key_exists($defaultOptionId, $options)) {
                                            $defaultOption = $options[$defaultOptionId];
                                        }
                                    }

                                    $checkBoxInAttrs = [
                                        'type'  => 'checkbox',
                                        'value' => $product->id,
                                        'class' => 'jsInConfiguration jsHpChoose',
                                        'id'    => 'product-' . $product->id
                                    ];

                                    $checkBoxDefAttrs = [
                                        'type'     => 'checkbox',
                                        'disabled' => 'disabled',
                                        'value'    => $product->id,
                                        'id'       => 'product-' . $product->id . '-def'
                                    ];

                                    $checkBoxMiniAttrs = [
                                        'type'     => 'checkbox',
                                        'value'    => $product->id,
                                        'class'    => 'jsMiniConfiguration jsHpChoose',
                                        'id'       => 'product-' . $product->id . '-mini'
                                    ];

                                    if (in_array($product->id, $valueIn)) {
                                        $checkBoxInAttrs['checked'] = 'checked';
                                    }

                                    if (in_array($product->id, $valueMini)) {
                                        $checkBoxMiniAttrs['checked'] = 'checked';
                                    }

                                    if (in_array($product->id, $valueDef)) {
                                        if (!isset($optionId) || (!empty($defaultOption) && $defaultOption->id == $optionId)) {
                                            $checkBoxDefAttrs['checked']   = 'checked';
                                            $checkBoxInAttrs['disabled']   = 'disabled';
                                            $checkBoxMiniAttrs['disabled'] = 'disabled';
                                            $notProcessComps[] = $product->id;
                                        }
                                    }

                                    if (isset($optionId)) {
                                        if (!isset($optionsConfig[$optionId])) {
                                            $checkBoxInAttrs['checked'] = '';
                                        }

                                        if (!isset($optionsMiniConfig[$optionId])) {
                                            $checkBoxMiniAttrs['checked'] = '';
                                        }

                                        if (!in_array(strval($partId), $partConfig)) {
                                            $checkBoxInAttrs['disabled']   = 'disabled';
                                        }

                                        if (!in_array(strval($partId), $partMiniConfig)) {
                                            $checkBoxMiniAttrs['disabled']   = 'disabled';
                                        }
                                    }

                                    $checkBoxInAttrs['name']   = $field->getControlName($field::CONTROL_TYPE_PARTS);
                                    $checkBoxDefAttrs['name']  = $field->getControlName($field::CONTROL_TYPE_DEFAULT);
                                    $checkBoxMiniAttrs['name'] = $field->getControlName($field::CONTROL_TYPE_MINI);

                                    $checkBoxInAttrs   = $app['helper']['html']->buildAttrs($checkBoxInAttrs);
                                    $checkBoxDefAttrs  = $app['helper']['html']->buildAttrs($checkBoxDefAttrs);
                                    $checkBoxMiniAttrs = $app['helper']['html']->buildAttrs($checkBoxMiniAttrs);
                                    ?>
                                    <tr class="jsProductRow" data-product="<?= $product->id ?>">
                                        <td valign="top">
                                            <input <?= $checkBoxInAttrs ?> />
                                        </td>
                                        <td valign="top">|</td>
                                        <td valign="top">
                                            <input <?= $checkBoxDefAttrs ?> />
                                        </td>
                                        <td valign="top">|</td>
                                        <td valign="top">
                                            <input <?= $checkBoxMiniAttrs ?> />
                                        </td>
                                        <td>
                                            <a href="<?= $product->getEditUrl() ?>#tabConfigurator" title="<?= $product->name ?>" target="_blank">
                                                <?= $product->name ?>
                                            </a>
                                            <?php if ($defaultOption instanceof OptionMarker) : ?>
                                                <br />
                                                <em style="color: #b10000;">
                                                    <?= $part->getConfiguratorName() ?> (<?= $defaultOption->getConfigurationName() ?>)
                                                </em>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <input type="hidden" name="jform[not_processed_comps]" value="<?= implode(',', $notProcessComps) ?>" />
    <?php endif; ?>
</div>
