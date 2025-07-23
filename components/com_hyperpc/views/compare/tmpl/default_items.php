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
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var     JSON    $items
 * @var     JSON    $itemData
 * @var     JSON    $arrayItem
 */

$arrayItems = $items->getArrayCopy();
$countItems = count($arrayItems);
?>
<div class="hp-compare-parts">
    <?php if ($countItems > 1) : ?>
        <ul uk-tab="swiping: false">
            <?php foreach ($items as $groupId => $data) : ?>
                <li>
                    <a href="#<?= $data->get('groupAlias') ?>">
                        <?= $data->get('groupName') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <ul class="<?= $countItems > 1 ? 'uk-switcher uk-margin' : 'uk-list' ?>">
        <?php foreach ($arrayItems as $arrayItem) :
            $countGroupItem = count($arrayItem->get('items'));
            ?>
            <li>
                <?php if ($arrayItem->get('hasEqualRow')) : ?>
                    <div class="uk-margin" uk-margin>
                        <span class="jsCompareShowAll tm-button-filter uk-button uk-button-small uk-button-default">
                            <?= Text::_('COM_HYPERPC_COMPARE_SHOW_ALL') ?>
                        </span>
                        <span class="jsCompareHideEqual tm-button-filter uk-active uk-disabled uk-button uk-button-small uk-button-default">
                            <?= Text::_('COM_HYPERPC_COMPARE_HIDE_EQUAL') ?>
                        </span>
                    </div>
                <?php endif; ?>
                <div class="uk-overflow-auto">
                    <table class="uk-table uk-table-divider hp-table-compare" data-count="<?= $countGroupItem ?>"
                           data-group="<?= $arrayItem->get('groupAlias') ?>"
                           style="min-width: <?= 225 * $countGroupItem ?>px;">
                        <tr>
                            <?php
                            $showButtonsLine = false;
                            foreach ($arrayItem->get('items') as $itemKey => $itemData) :
                                $itemPrice = $itemData->find('buy.price');
                                if ($itemPrice) {
                                    $showButtonsLine = true;
                                }
                                ?>
                                <td data-part-id="<?= $itemKey ?>">
                                    <?php if (in_array($arrayItem->get('groupAlias'), ['products', 'moyskladProducts'])) :
                                        $width = $this->hyper['params']->get('product_img_teaser_width', 450);
                                        $height = $this->hyper['params']->get('product_img_teaser_height', 450);
                                        ?>
                                        <a href="<?= $itemData->get('url') ?>" class="uk-display-inline-block uk-background-cover" target="_blank" style="background-image: url(<?= $itemData->get('image') ?>)">
                                            <canvas width="<?= $width ?>" height="<?= $height ?>"></canvas>
                                        </a>
                                    <?php else : ?>
                                        <a href="<?= $itemData->get('url') ?>" class="uk-link-reset" target="_blank">
                                            <img src="<?= $itemData->get('image') ?>" />
                                        </a>
                                    <?php endif; ?>

                                    <div class="uk-margin-top">
                                        <?php if ($itemData->get('availability')) :
                                            $textClass = ' uk-text-';
                                            switch ($itemData->get('availability')) {
                                                case Stockable::AVAILABILITY_INSTOCK:
                                                    $textClass .= 'success';
                                                    break;
                                                case Stockable::AVAILABILITY_PREORDER:
                                                case Stockable::AVAILABILITY_OUTOFSTOCK:
                                                    $textClass .= 'warning';
                                                    break;
                                                case Stockable::AVAILABILITY_DISCONTINUED:
                                                    $textClass .= 'danger';
                                                    break;
                                                default:
                                                    $textClass .= 'muted';
                                                    break;
                                            }
                                            ?>
                                            <div class="<?= $textClass ?>">
                                                <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . strtoupper($itemData->get('availability'))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="uk-flex uk-flex-middle">
                                        <div class="uk-text-emphasis uk-text-medium uk-text-truncate">
                                            <a href="<?= $itemData->get('url') ?>" class="uk-link-reset" target="_blank">
                                                <?= $itemData->get('name') ?>
                                            </a>
                                        </div>
                                        <?php
                                        $removeAttrs = [
                                            'class' => 'jsRemoveCompareItem uk-flex-none uk-margin-small-left hp-remove-compare-item uk-text-danger',
                                            'data'  => [
                                                'id'   => $itemKey,
                                                'type' => $itemData->get('type'),
                                            ],
                                            'uk-icon' => 'icon: trash;'
                                        ];

                                        if ($itemData->get('in-stock')) {
                                            $removeAttrs['data']['in-stock'] = $itemData->get('in-stock');
                                        }
                                        ?>
                                        <a <?= $this->hyper['helper']['html']->buildAttrs($removeAttrs) ?>></a>
                                    </div>
                                    <?php if ($itemPrice) : ?>
                                        <?= Text::sprintf('COM_HYPERPC_PRODUCT_PRICE', $itemPrice) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php if ($showButtonsLine) : ?>
                            <tr>
                                <?php foreach ($arrayItem->get('items') as $itemKey => $itemData) : ?>
                                    <td data-part-id="<?= $itemKey ?>">
                                        <?= $itemData->find('buy.buttons') ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                        <?php
                        foreach ($arrayItem->get('properties') as $property) :
                            if ($property->show === false) {
                                continue;
                            }

                            if ($property->type === 'hpseparator') : ?>
                                <tr class="hp-table-compare__group-head<?= $property->isEqual ? ' jsEqualValues uk-hidden' : '' ?>">
                                    <td colspan="<?= $countGroupItem ?>" class="uk-text-emphasis">
                                        <?= $property->get('label'); ?>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <tr class="hp-table-compare__property-name<?= $property->isEqual ? ' jsEqualValues uk-hidden' : '' ?>">
                                    <td colspan="<?= $countGroupItem ?>" class="uk-text-uppercase uk-text-small uk-text-muted">
                                        <?= $property->label; ?>
                                    </td>
                                </tr>
                                <tr class="hp-table-compare__values<?= $property->isEqual ? ' jsEqualValues uk-hidden' : '' ?>">
                                    <?php foreach ($property->values as $partId => $value) : ?>
                                        <td data-part-id="<?= $partId ?>">
                                            <?php
                                            if ($value !== null && trim($value) !== '') {
                                                echo $value;
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endif ?>

                        <?php endforeach; ?>
                    </table>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
