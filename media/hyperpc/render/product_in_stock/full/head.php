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

use JBZoo\Image\Image;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Review;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var PartMarker      $part
 * @var OptionMarker    $option
 * @var ProductMarker   $product
 * @var Money           $price
 * @var Review          $reviewsData
 */

$isTablet = $this->hyper['detect']->isTablet();
$isMobile = $this->hyper['detect']->isMobile() && !$isTablet ? true : false;

$image = $product->getConfigurationImage(0, 800);
?>

<div class="hp-product-head">
    <div>
        <div class="uk-grid uk-flex-middle uk-flex-right uk-grid-collapse">

            <div class="uk-width-expand uk-visible@l">
                <?php if ($image instanceof Image) : ?>
                    <?php if ($image->isSquare()) : ?>
                        <canvas width="960" height="800" class="uk-background-image@l uk-background-contain" style="background-image:url(<?= Uri::getInstance($image->getUrl())->getPath() ?>);"></canvas>
                    <?php else : ?>
                        <div class="hp-part hp-part-image">
                            <img src="<?= Uri::getInstance($image->getUrl())->getPath() ?>" alt="<?= $product->getName() ?>" width="<?= $image->getWidth() ?>" height="<?= $image->getHeight() ?>" class="hp-part-image__image">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="hp-product-head__text uk-width-1-1 uk-width-2-5@l uk-position-z-index">
                <div>
                    <h1 class="hp-product-head__title uk-heading-primary">
                        <?= $product->getPageName() ?>
                    </h1>

                    <div class="uk-margin uk-grid" uk-margin>
                        <?php if ($this->hyper['params']->get('show_reviews', 0)) : ?>
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

                    <?php if ($image instanceof Image) : ?>
                        <?php if ($image->isSquare()) : ?>
                            <div class="hp-product-head__image uk-hidden@l">
                                <img src="<?= Uri::getInstance($image->getUrl())->getPath() ?>" alt="<?= $product->getName() ?>" width="<?= $image->getWidth() ?>" height="<?= $image->getHeight() ?>">
                            </div>
                        <?php else : ?>
                            <div class="hp-part hp-part-image uk-hidden@l">
                                <img src="<?= Uri::getInstance($image->getUrl())->getPath() ?>" alt="<?= $product->getName() ?>" width="<?= $image->getWidth() ?>" height="<?= $image->getHeight() ?>" class="hp-part-image__image">
                            </div>
                        <?php endif; ?>
                        
                    <?php endif; ?>

                    <hr>

                    <div class="uk-flex uk-flex-column">

                        <div class="hp-product-head__purchase">
                            <div class="uk-grid uk-grid-small uk-flex-middle uk-flex-center uk-flex-left@s uk-text-nowrap" uk-margin>
                                <?= $this->hyper['helper']['render']->render('product/full/purchase', [
                                    'product'    => $product,
                                    'properties' => [],
                                    'price'      => $price,
                                    'layout'     => 'head'
                                ]);
                                ?>

                                <div>
                                    <?= $this->hyper['helper']['render']->render('product/common/buy_now/button', [
                                        'itemName' => $product->name,
                                        'price'    => $price->text()
                                    ]); ?>
                                </div>
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
