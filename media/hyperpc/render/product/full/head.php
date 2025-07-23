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
 * @author      Roman Evsyukov
 */

use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Review;
use HYPERPC\Render\Product as ProductRender;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Render\MoyskladProduct as MoyskladProductRender;

/**
 * @var         RenderHelper  $this
 * @var         PartMarker    $part
 * @var         OptionMarker  $option
 * @var         Money         $price
 * @var         array         $properties
 * @var         Review        $reviewsData
 * @var         ProductMarker $product
 */

$isTablet = $this->hyper['detect']->isTablet();
$isMobile = $this->hyper['detect']->isMobile() && !$isTablet ? true : false;

$imagesList = $product->getImages();
$imagePath  = array_shift($imagesList);

/** @var ProductRender|MoyskladProductRender */
$productRener = $product->getRender();
?>

<div class="hp-product-head">
    <div class="uk-background-image@l uk-background-contain" style="background-image:url(<?= $imagePath ?>);">

        <div class="uk-grid uk-flex-middle uk-flex-right uk-grid-collapse">

            <div class="uk-width-expand uk-visible@l">
                <canvas width="960" height="800"></canvas>
            </div>

            <div class="hp-product-head__text uk-width-1-1 uk-width-2-5@l">
                <div>
                    <h1 class="hp-product-head__title uk-heading-primary">
                        <?= $product->getPageName() ?>
                    </h1>

                    <div class="uk-margin uk-grid" uk-margin>
                        <?php if ($this->hyper['params']->get('show_reviews', 0) && isset($reviewsData)) : ?>
                            <div class="hp-product-head__rating uk-flex uk-flex-middle">
                                <span class="jsRatingStars uk-margin-small-right" data-score="<?= $reviewsData->getTotalRating() ?>"></span>
                                <span class="uk-text-muted uk-text-lowercase">
                                    <?php if ($reviewsData->getReviewsCount() > 0) : ?>
                                        (<a href="#product-reviews" class="uk-link-reset" uk-scroll><?= $reviewsData->getTotalSlant() ?></a>)
                                    <?php else : ?>
                                        (<?= Text::_('COM_HYPERPC_REVIEW_NO_REVIEWS') ?>)
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div>
                            <?= $this->hyper['helper']['render']->render('product/common/compare_button', [
                                'product' => $product
                            ]); ?>
                        </div>
                    </div>

                    <?php if (!$isMobile) : ?>
                        <?php if (!empty($product->params->get('logo'))) : ?>
                            <div class="hp-product-head__logos uk-position-top uk-position-z-index tm-position@l uk-margin-bottom">
                                <div class="uk-container">
                                    <div class="uk-grid uk-grid-medium" uk-margin>
                                        <?= $productRener->logos() ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <p class="hp-product-head__lead">
                            <?= HTMLHelper::_('content.prepare', strip_tags($product->description)) ?>
                        </p>
                    <?php endif; ?>

                    <div class="hp-product-head__image uk-hidden@l">
                        <?= $productRener->image(false) ?>
                    </div>

                    <hr>

                    <div class="uk-flex uk-flex-column">
                        <?= $this->hyper['helper']['render']->render('product/full/options', [
                            'product'    => $product,
                            'properties' => $properties
                        ]); ?>

                        <div class="hp-product-head__purchase">
                            <div class="uk-grid uk-grid-small uk-flex-middle uk-flex-center uk-flex-left@s uk-text-nowrap" uk-margin>
                                <?= $this->hyper['helper']['render']->render('product/full/purchase', [
                                    'product'    => $product,
                                    'properties' => $properties,
                                    'price'      => $price,
                                    'layout'     => 'head'
                                ]); ?>

                                <?php if ($product->isInStock() || $product->isPreOrdered()) : ?>
                                    <div>
                                        <?= $this->hyper['helper']['render']->render('product/common/buy_now/button', [
                                            'itemName' => $product->name,
                                            'price'    => $price->text()
                                        ]); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <?= $this->hyper['helper']['render']->render('product/full/conditions', [
                        'product' => $product,
                        'price'   => $price->text()
                    ]); ?>
                </div>
            </div>
        </div>

    </div>
</div>
