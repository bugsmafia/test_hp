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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Object\Compare\CategoryTree\CategoryData;
use HYPERPC\Object\Compare\CategoryTree\CategoryTree;
use HYPERPC\Object\Compare\CategoryTree\RootCategoryData;
use HYPERPC\Object\Compare\CategoryTree\CategoryProductData;

/**
 * @var CategoryTree $tree
 */
?>

<div class="jsCompareSidebar uk-offcanvas" uk-offcanvas="flip: true; overlay: true">
    <div class="uk-offcanvas-bar tm-compare-offcanvas-bar uk-width-large">
        <button class="uk-offcanvas-close uk-close-large" type="button" uk-close></button>
        <div class="uk-margin-medium-top">
            <?php
            /** @var RootCategoryData $rootCategory */
            foreach ($tree->items() as $rootCategory) : ?>
                <div>
                    <div>
                        <div class="uk-text-bold tm-text-medium uk-text-muted uk-margin-medium-top">
                            <?= $rootCategory->name ?>
                        </div>
                        <ul class="hp-compare-categories-list uk-margin-small-top">
                            <?php
                            /** @var CategoryData $category */
                            foreach ($rootCategory->categories->items() as $category) :
                                $categoryProducts = $category->products;
                                ?>
                                <li class="jsCompareSidebarCategory" uk-toggle="target: .jsCompareSidebarStep2">
                                    <div>
                                        <div class="uk-text-emphasis uk-text-bold">
                                            <?= $category->name ?>
                                        </div>
                                        <div>
                                            <?= $category->price ?>
                                        </div>
                                        <ul class="jsCompareProductsList hp-compare-products-list" data-group="products" hidden>
                                            <?php
                                            /** @var CategoryProductData $product */
                                            foreach ($category->products->items() as $product) :
                                                $stockId = $product->stockId;
                                                $optionId = $product->optionId;
                                                ?>
                                                <li>
                                                    <div class="jsCompareAdd uk-position-relative<?= $product->isInCompare ? ' inCompare' : '' ?>"
                                                        data-id="<?= $product->id ?>"
                                                        data-type="<?= $product->type ?>"
                                                        data-itemkey="<?= $product->itemKey ?>"
                                                        <?= $stockId ? ' data-stock-id="' . $stockId . '"' : '' ?>
                                                        <?= $optionId ? ' data-option-id="' . $optionId . '"' : '' ?>
                                                        >
                                                        <div class="uk-grid uk-grid-small">
                                                            <div class="uk-width-1-4">
                                                                <div>
                                                                    <div class="uk-display-inline-block uk-background-cover" uk-img style="background-image: url(<?= $product->image ?>)">
                                                                        <canvas width="100" height="100"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="uk-width-expand">
                                                                <div class="uk-text-emphasis tm-text-medium">
                                                                    <?= $product->name ?>
                                                                </div>
                                                                <div>
                                                                    <?= Text::_('COM_HYPERPC_PRICE') ?>
                                                                    <?= $product->price ?>
                                                                </div>
                                                                <div class="uk-margin-small-top uk-text-small uk-text-muted">
                                                                    <?= $product->description ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="jsCompareSidebarStep2 uk-offcanvas" uk-offcanvas="flip: true; overlay: true;">
    <div class="uk-offcanvas-bar tm-compare-offcanvas-bar uk-width-large">
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <div>
                <button class="uk-button tm-button-icon" type="button" uk-toggle="target: .jsCompareSidebar">
                    <span class="uk-icon uk-text-bottom" uk-icon="icon: arrow-left; ratio: 1.5"></span>
                    <span style="font-size: 1rem">
                        <?= Text::_('COM_HYPERPC_BACK') ?>
                    </span>
                </button>
            </div>
            <div><button class="uk-offcanvas-close uk-close-large tm-position-static" type="button" uk-close></button></div>
        </div>
        <div class="jsCompareSidebarStep2Content uk-margin-medium-top"></div>
    </div>
</div>
