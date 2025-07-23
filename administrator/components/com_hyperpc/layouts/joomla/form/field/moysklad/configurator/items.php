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
use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use JFormFieldMoyskladConfigurator as FieldConfigurator;

/**
 * @var         RenderHelper            $this
 * @var         array                   $items
 * @var         JSON                    $value
 * @var         FieldConfigurator       $field
 * @var         ProductFolder           $folder
 * @var         MoyskladProduct         $product
 * @var         string                  $fieldName
 * @var         array                   $optionList
 * @var         bool                    $isPropertyChecked
 * @var         bool                    $multiplyIsChecked
 * @var         string                  $activeContentClass
 * @var         int                     $groupMaxQuantity
 */

$defaultParts = $product->configuration ? $product->configuration->get('default', []) : [];
$quantityList = $product->configuration ? new Data($product->configuration->get('quantity', [])) : new Data([]);
$listAttribs   = ['class' => 'jsConfiguratorDefaultQuantity', 'style' => 'width: 50px;'];

?>
<div id="<?= $folder->alias ?>-<?= $folder->id ?>" class="tab-pane<?= $activeContentClass ?>">
    <table id="table-<?= $folder->alias ?>" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <td class="center w-1">
                    <input type="checkbox" class="jsPartCheckAll form-check-input" />
                </td>
                <td class="small w-1">mini</td>
                <td class="p-0 w-1"></td>
                <td class="small">
                    <?= Text::_('COM_HYPERPC_PART') ?>
                </td>
                <td class="small">
                    <?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_COUNT') ?>
                </td>
                <td class="small">
                    <a href="#" class="jsResetDefault" data-group-id="<?= $folder->id ?>">
                        <?= Text::_('JCLEAR') ?>
                    </a>
                </td>
                <td class="small text-end w-1">
                    <?= Text::_('COM_HYPERPC_HEADING_PRICE') ?>
                </td>
            </tr>
        </thead>
        <tbody>
            <?php
            $partCounts = new Data($value->get('quantity', []));
            foreach ((array) $items as $item) :
                if (!$item->isPublished() && !$item->isArchived()) {
                    continue;
                }

                $isChecked        = \key_exists($item->id, $value->get('parts', []));
                $isMiniChecked    = \key_exists($item->id, $value->get('parts_mini', []));
                $isEnableQuantity = $item->params->get('enable_quantity', 0, 'bool');
                $countValue       = $partCounts->get($item->id, 1, 'int');

                $variants = $this->hyper['helper']['moyskladVariant']->getPartVariants($item->id, $optionList);

                $hasCheckedOptions = $isChecked && \key_exists($item->id, (array) $value->get('option', []));

                $checkBoxAttrs = [
                    'data-type'   => 'part',
                    'class'       => 'jsPartCheck form-check-input',
                    'data-id'     => $item->id,
                    'value'       => $item->id,
                    'type'        => 'checkbox',
                    'checked'     => $isChecked,
                    'id'          => 'part-' . $item->id,
                    'name'        => $fieldName . '[parts][' . $item->id . ']'
                ];

                $checkBoxMiniAttrs = [
                    'data-type'   => 'part',
                    'class'       => 'jsPartMini form-check-input',
                    'data-id'     => $item->id,
                    'value'       => $item->id,
                    'type'        => 'checkbox',
                    'checked'     => $isMiniChecked,
                    'name'        => $fieldName . '[parts_mini][' . $item->id . ']'
                ];

                $isDefault = \in_array($item->id, $value->get('default', []));
                if ($isDefault) {
                    $checkBoxAttrs['readonly'] = 'readonly';
                    $checkBoxMiniAttrs['readonly'] = 'readonly';
                }

                if ($hasCheckedOptions) {
                    $checkBoxAttrs['readonly'] = $checkBoxMiniAttrs['readonly'] = 'readonly';
                }

                if ($isPropertyChecked) {
                    $checkBoxMiniAttrs['readonly'] = 'readonly';
                    unset($checkBoxMiniAttrs['checked']);
                }

                $radioAttrs = [
                    'data-type'  => 'part',
                    'value'      => $item->id,
                    'data-id'    => $item->id,
                    'checked'    => $isDefault,
                    'class'      => 'hasTooltip jsCheckDefault form-check-input',
                    'title'      => Text::_('JTOOLBAR_DEFAULT'),
                    'type'       => $multiplyIsChecked ? 'checkbox' : 'radio',
                    'name'       => $fieldName . '[default][' . $folder->id . '][]'
                ];

                $radioAttrs         = $this->hyper['helper']['html']->buildAttrs($radioAttrs);
                $checkBoxAttrs      = $this->hyper['helper']['html']->buildAttrs($checkBoxAttrs);
                $checkBoxMiniAttrs  = $this->hyper['helper']['html']->buildAttrs($checkBoxMiniAttrs);

                $isArchived = $item->isArchived();
                $archiveClass = '';
                if ($isArchived) {
                    $archiveClass = ' hp-row-archive';
                    if ($isDefault) {
                        $item->hyper['cms']->enqueueMessage(
                            Text::sprintf(
                                'COM_HYPERPC_CONFIGURATOR_POSITION_IS_ARCHIVED',
                                $item->name
                            ),
                            'warning'
                        );
                    }
                }
                ?>
                <tr class="jsPartRow<?= $archiveClass ?>" data-parent="<?= $folder->parent_id ?>"
                    data-group-id="<?= $folder->id ?>"
                    data-id="<?= $item->id ?>">
                    <td>
                        <input <?= $checkBoxAttrs ?> />
                    </td>
                    <td>
                        <input <?= $checkBoxMiniAttrs ?> />
                    </td>
                    <td class="p-0">
                        <a href="<?= $item->getEditUrl() ?>" target="_blank" class="hasTooltip"
                           title="<?= Text::sprintf('COM_HYPERPC_PART_EDIT_LINK', $item->name) ?>">
                        </a>
                    </td>
                    <td>
                        <label for="part-<?= $item->id ?>" class="row-name small">
                            <?php if ($isArchived) : ?>
                                <span class="badge bg-dark">A</span>
                            <?php endif; ?>
                            <?= $item->name ?>
                        </label>
                    </td>
                    <td>
                        <?php
                        if ($isEnableQuantity) {
                            $defPartQuantity = $quantityList->get($item->id, 1);
                            $quantityOptionsEls = $this->hyper['helper']['configurator']->groupQuantityOptions($product, $folder);

                            if (\in_array((string) $item->id, $defaultParts) && !\in_array($defPartQuantity, $quantityOptionsEls)) {
                                $quantityOptionsEls[$defPartQuantity] = (int) $defPartQuantity;

                                ksort($quantityOptionsEls);
                            }

                            if (!empty($quantityOptionsEls) && (\count($quantityOptionsEls) > 1 || \array_key_first($quantityOptionsEls) !== 1)) {
                                echo HTMLHelper::_(
                                    'select.genericlist',
                                    $quantityOptionsEls,
                                    $fieldName . '[quantity][' . $item->id . ']',
                                    $listAttribs,
                                    null,
                                    null,
                                    $countValue,
                                    null
                                );
                            }
                        }
                        ?>
                    </td>
                    <td class="text-center w-1">
                        <?php if (!\count($variants)) : ?>
                            <label class="w-100 text-center">
                                <input <?= $radioAttrs ?> />
                            </label>
                        <?php endif; ?>
                    </td>
                    <td class="text-end w-1 text-nowrap small">
                        <?php if (!\count($variants)) : ?>
                            <label for="part<?= $item->id ?>">
                                <?= $item->list_price->html() ?>
                            </label>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                if (\count($variants) > 0) {
                    echo $field->partial('part_options', [
                        'part'               => $item,
                        'folder'             => $folder,
                        'value'              => $value,
                        'variants'           => $variants,
                        'fieldName'          => $fieldName,
                        'isPropertyChecked'  => $isPropertyChecked,
                        'isMultiply'         => $multiplyIsChecked
                    ]);
                }
                ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
