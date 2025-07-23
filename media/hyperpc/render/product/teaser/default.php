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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\FpsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Review;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper    $this
 * @var         array           $groups
 * @var         array           $options
 * @var         array           $products
 * @var         bool            $showFps
 * @var         string          $teaserType
 * @var         bool            $showDesc
 * @var         bool            $showFullConfig
 * @var         bool            $showCompare
 * @var         bool            $linkToPage
 * @var         bool            $showConfBtn
 * @var         string          $game
 */

/** @var FpsHelper */
$fpsHelper = $this->hyper['helper']['fps'];

if (!isset($showFps)) {
    $showFps = false;
}

if (!isset($showDesc)) {
    $showDesc = true;
}

if (!isset($showFullConfig)) {
    $showFullConfig = false;
}

if (!isset($showCompare)) {
    $showCompare = true;
}

if (!isset($linkToPage)) {
    $linkToPage = true;
}

if (!isset($showConfBtn)) {
    $showConfBtn = true;
}

if (!isset($game)) {
    $game = '';
}

$view = $this->hyper['input']->getCmd('view');

$isMobile = $this->hyper['detect']->isMobile();
?>
<?php
/** @var ProductMarker|Entity $product */
foreach ($products as $product) :
    $category = $product->getFolder();
    if (!$category->id) {
        continue;
    }

    $productRender = $product->getRender();
    $productRender->setEntity($product);

    /** @var MoyskladProductHelper */
    $productHelper = $product->getHelper();

    $configurationId = '';
    $itemKey         = $product->getItemKey();
    $price           = $product->getConfigPrice(true);

    $fromStock = $product->isFromStock();
    if ($fromStock) {
        $configurationId = $product->getStockConfigurationId();
        $storeId = $product->getStockStoreId();
        $linkToPage = true;
    }

    $productTeaserType = 'default';
    $viewUrl = '/' . ltrim($product->getViewUrl(), '/');
    if (!isset($teaserType)) {
        if ($category->params->get('teasers_type', 'default') === 'lumen') {
            $productTeaserType = 'lumen';
            $viewUrl = $product->getConfigUrl();
        }
    } else {
        $productTeaserType = $teaserType;
        if ($teaserType === 'lumen') {
            $viewUrl = $product->getConfigUrl();
        }
    }

    $reviewsData = new Review($product, 'teaser');

    $showFpsInProduct = $showFps && $fpsHelper->showFps($category->id);
    ?>

    <div class="hp-product-teaser hp-product-teaser--default">
        <?= $this->hyper['helper']['microdata']->getEntityMicrodata($product); ?>
        <div class="uk-card uk-card-small uk-transition-toggle">
            <div class="uk-card-media-top uk-position-relative">

                <?= $productRender->image(true, 'product/image', $linkToPage) ?>

                <?php if ($showCompare) : ?>
                    <?= $this->hyper['helper']['render']->render('product/common/compare_button', [
                        'product' => $product,
                    ]);
                    ?>
                <?php endif; ?>

                <?php if ($showFpsInProduct) :
                    $productFps = $fpsHelper->getFps($product);
                    $averageFps = $fpsHelper->calculateAverageFps($productFps, $game);
                    ?>
                    <?php if ($averageFps > 0) :
                        $fpsTopLimit = $fpsHelper->getFpsTopLimit();
                        $lineWidth = min(100, $averageFps / ($fpsTopLimit * 0.01));
                        ?>
                        <div class="hp-product-teaser-fps uk-text-small" uk-scrollspy="hidden: false; cls: uk-active; repeat: true">
                            <div class="uk-flex uk-flex-between uk-flex-bottom">
                                <span class="uk-text-emphasis">
                                    AVG. FPS - <?= $averageFps ?>
                                </span>
                                <a href="#fps-<?= $itemKey ?>" uk-toggle class="hp-product-teaser-fps__button uk-link-muted<?= $isMobile ? '' : ' uk-transition-fade' ?>">
                                    <?= Text::_('COM_HYPERPC_FPS_SHOW') ?>
                                </a>
                            </div>
                            <div class="hp-product-teaser-fps__bar">
                                <div class="hp-product-teaser-fps__bar-fill" style="width: <?= $lineWidth ?>%;"></div>
                            </div>
                        </div>
                        <div id="fps-<?= $itemKey ?>" class="uk-modal uk-modal-container" uk-modal>
                            <div class="uk-modal-dialog uk-margin-auto-vertical ">
                                <div class="uk-modal-body uk-padding-remove-bottom">
                                    <button class="uk-modal-close-default uk-close-large uk-close uk-icon" type="button" uk-close></button>
                                    <div class="uk-h3 uk-text-center uk-margin-top uk-margin-bottom">
                                        <?= Text::_('COM_HYPERPC_FPS_TITLE_PRODUCT') ?>
                                    </div>
                                </div>
                                <div uk-overflow-auto>
                                    <div class="uk-container uk-container-small uk-margin-medium-bottom">
                                        <?php
                                        echo $this->render('/product/common/fps/fps_table', [
                                            'productFps' => $productFps,
                                            'active'     => $game
                                        ]);
                                        ?>
    
                                        <?= $this->render('/product/common/fps/disclaimer') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
            <div class="uk-card-body">
                <div class="hp-product-teaser__header">
                    <div class="hp-product-teaser__name uk-margin-small-bottom">
                        <?php
                        $linkAttrs = [
                            'href'  => $viewUrl,
                            'class' => 'uk-link-reset' . ($productTeaserType === 'lumen' ? ' jsGoToConfigurator' : '')
                        ];
                        $gtmOnProductclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $product]);
                        ?>
                        <?php if ($linkToPage) : ?>
                        <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?><?= $gtmOnProductclick ?>>
                            <span>
                                <?= $product->name ?>
                            </span>
                        </a>
                        <?php else : ?>
                            <span>
                                <?= $product->name ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($this->hyper['params']->get('show_reviews', 0)) : ?>
                        <div class="hp-product-teaser__rating uk-flex uk-flex-middle uk-margin-small">
                            <span class="jsRatingStars uk-margin-small-right" data-score="<?= $reviewsData->totalRating ?>"></span>
                            <span class="uk-text-muted uk-text-small uk-text-lowercase">
                                <?php if ($reviewsData->getReviewsCount() > 0) : ?>
                                    (<a href="<?= $viewUrl ?>#product-reviews" class="uk-link-reset"><?= $reviewsData->getTotalSlant() ?></a>)
                                <?php else : ?>
                                    (<?=Text::_('COM_HYPERPC_REVIEW_NO_REVIEWS') ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="uk-grid uk-grid-small uk-flex-between" uk-margin uk-height-match="target: .hp-priceline">
                        <div>
                            <div class="hp-priceline">
                                <?= $this->hyper['helper']['render']->render('common/price/item-price', [
                                    'price'      => $price,
                                    'entity'     => $product,
                                    'htmlPrices' => false
                                ]); ?>
                            </div>

                            <div class="uk-text-small uk-margin-small-top">
                                <?php if ($product->isPreOrdered()) : ?>
                                    <span class="uk-text-warning">
                                        <?= Text::_('COM_HYPERPC_PRODUCT_PREORDER_TEXT_TEASER') ?>
                                    </span>
                                <?php elseif ($product->isInStock()) : ?>
                                    <span class="uk-text-success">
                                        <?= Text::_('COM_HYPERPC_PRODUCT_INSTOCK_TEXT') ?>
                                    </span>
                                <?php elseif ($product->isOutOfStock()) : ?>
                                    <span class="uk-text-warning">
                                        <?= Text::_('COM_HYPERPC_PRODUCT_OUTOFSTOCK_TEXT') ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div>
                            <div class="uk-flex uk-flex-middle hp-priceline">
                                <?php if ($product->isOutOfStock()) : ?>
                                    <?= $productRender->getCartBtn('teaser_button', ['buy']) ?>
                                <?php elseif ($productTeaserType === 'lumen') : ?>
                                    <?= $productRender->getCartBtn('teaser_button', ['choose']) ?>
                                <?php elseif ($fromStock) : ?>
                                    <?= $productRender->getCartBtn('teaser_button', ['buy_in_stock']) ?>
                                <?php else : ?>
                                    <?= $productRender->getCartBtn('teaser_button', ['buy']) ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($product->isInStock() || $product->isPreOrdered()) : ?>
                                <div class="uk-text-center uk-text-small uk-margin-small-top">
                                    <?= $this->hyper['helper']['render']->render('product/common/buy_now/button', [
                                        'configurationId' => $configurationId,
                                        'itemName'        => $product->name,
                                        'price'           => $price->text()
                                    ]); ?>
                                </div>
                            <?php endif; ?>

                        </div>

                    </div>

                    <hr class="uk-margin-small">

                    <?php if ($showDesc) :
                        $productDescription = trim(strip_tags($product->description));
                        ?>
                        <?php if (!empty($productDescription)) : ?>
                            <div class="hp-product-teaser__description">
                                <?= HTMLHelper::_('content.prepare', strip_tags($product->description)) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>

                <?php if ($productTeaserType !== 'lumen') : ?>
                    <div class="uk-grid uk-grid-small uk-flex-between uk-margin-top" uk-margin>
                        <?php
                        $productBtn = [];

                        if ($showConfBtn && ($product->isInStock() || $product->isPreOrdered())) {
                            $productBtn[] = 'configurator';
                        }

                        if ($product->params->get('show_read_more', 1)) {
                            $productBtn[] = 'details';
                        }

                        echo $productRender->getCartBtn('teaser_button', $productBtn);

                        if ($fromStock && $view === 'products_in_stock') {
                            echo $this->hyper['helper']['render']->render('product/common/show_online/button', [
                                'configurationId' => $configurationId,
                                'itemName'        => $product->name,
                                'storeId'         => $storeId,
                                'price'           => $price->text()
                            ]);
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <hr>
                <?php if ($fromStock) :
                    $specsLinkAttrs = [
                        'href'  => '#' . $itemKey,
                        'class' => 'jsSpecificationButton uk-link-muted tm-link-dashed',
                        'data'  => [
                            'title'   => $product->name,
                            'itemKey' => $itemKey
                        ]
                    ];
                    ?>
                    <div>
                        <a <?= $this->hyper['helper']['html']->buildAttrs($specsLinkAttrs) ?>>
                            <?= Text::sprintf('COM_HYPERPC_CART_CONFIGURATION_NUMBER', $configurationId) ?>
                        </a>
                    </div>
                <?php endif; ?>
                <ul class="uk-list uk-list-divider uk-text-small">
                    <?php
                    echo $this->hyper['helper']['render']->render('product/teaser/_item_configurator_list', [
                        'groups'  => $groups,
                        'parts'   => $productHelper->getTeaserParts($product, 'default', false, true),
                        'product' => $product,
                        'options' => $options
                    ], 'renderer');
                    ?>
                </ul>
                <?php
                if ($fromStock && $view !== 'products_in_stock') :
                    $configurationHtml = $productRender->configuration();

                    echo $this->hyper['helper']['uikit']->modal($itemKey, implode(PHP_EOL, [
                        '<div class="uk-container-small uk-margin-auto">',
                            '<div class="uk-h2">' . $product->getName() . '</div>',
                            $configurationHtml,
                        '</div>'
                    ]));
                    ?>

                <?php endif; ?>
           </div>
        </div>
    </div>

<?php endforeach;
