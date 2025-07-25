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
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\MicrodataHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;
 
/**
 * @var RenderHelper        $this
 * @var CategoryMarker[]    $groups
 * @var ProductMarker[]     $products
 * @var ?bool               $showFps
 * @var ?string             $game
 * @var ?string             $instock
 */

if (!isset($showFps)) {
    $showFps = true;
}

if (!isset($game)) {
    $game = '';
}

if (!isset($instock)) {
    $instock = 'default';
}

$this->hyper['wa']->useScript('product.teaser');

?>
<?php
foreach ($products as $product) :
    $category = $product->getFolder();
    if (!$category->id) {
        continue;
    }
    $productRender = $product->getRender();

    $productRender->setEntity($product);
    dump(__LINE__.__DIR__." --- productRender --- ");
    dump($product);

    
    $productDescription = trim(strip_tags($product->get('description', '')));

    $price = $product->getConfigPrice(true);

    $availability = $product->getAvailability();
    $fromStock = $availability === Stockable::AVAILABILITY_INSTOCK;

    if (!$fromStock && $instock === 'default') {
        $images = $product->get('images')?->get('teaser_color_variants');

        if (\is_array($images) && \count($images)) {
            $image = $images[\array_key_first($images)];
            $image['color'] = '1';
            $product->get('images')->set('teaser_color_variants', [$image]);
        }
    }
    dump(__LINE__.__DIR__." --- productRender->image() --- ");
    dump($product);
    
    ?>
    <div class="tm-product-teaser">

        

        <div class="tm-product-teaser__body uk-card uk-card-default uk-card-body uk-transition-toggle">
            <?php if ($fromStock && !$this->hyper['detect']->isMobile()) : ?>
                <span class="uk-text-muted uk-text-small uk-transition-fade uk-position-absolute uk-position-z-index">
                    <?= $product->getStockConfigurationId() ?>
                </span>
            <?php endif; ?>

            <div class="tm-product-teaser__icons">
                <?= $this->render('product/teaser/2024/_compare_button', [
                    'product' => $product
                ]);
                ?>
            </div>

            <div class="uk-text-center">
                <?= $productRender->image(tpl: 'product/teaser/2024/_image') ?>

                <div class="tm-product-teaser__heading">
                    <a href="<?= $product->getViewUrl() ?>" class="uk-link-heading">
                        <?= $product->getNameWithoutBrand() ?>
                    </a>
                </div>

                <div class="tm-product-teaser__description">
                    <?php if ($instock === 'except') : ?>
                        <?= !empty($productDescription) ? HTMLHelper::_('content.prepare', $productDescription) : '' ?>
                    <?php else :
                        $availabilityLabelClass = match ($availability) {
                            Stockable::AVAILABILITY_INSTOCK => 'uk-text-success',
                            Stockable::AVAILABILITY_PREORDER => 'uk-text-warning',
                            Stockable::AVAILABILITY_OUTOFSTOCK,
                            Stockable::AVAILABILITY_DISCONTINUED => 'uk-text-danger'
                        };
                        ?>
                        <div class="tm-color-gray-100 uk-text-muted uk-flex uk-flex-center uk-flex-middle">
                            <span class="uk-icon tm-margin-8-right <?= $availabilityLabelClass ?>">
                                <svg width="12" height="12" viewBox="0 0 12 12">
                                    <circle cx="6" cy="6" r="6"></circle>
                                </svg>
                            </span>
                            <?= Text::_('COM_HYPERPC_PRODUCT_' . \strtoupper($availability) . '_TEXT') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tm-text-medium tm-font-semi-bold tm-color-white tm-margin-16-top">
                    <?php if (!$fromStock) : ?>
                        <?= Text::sprintf('COM_HYPERPC_STARTS_FROM', $price->text()) ?>
                    <?php else : ?>
                        <?= $price->text() ?>
                    <?php endif; ?>
                </div>

                <?php if ($this->hyper['params']->get('credit_enable', '0')) : ?>
                    <div class="uk-text-small tm-color-gray-100 tm-margin-16-bottom">
                        <?= 
                        Text::_('COM_HYPERPC_OR') . ' ' .
                        Text::sprintf(
                            'COM_HYPERPC_CREDIT_MONTHLY_PAYMENT',
                            $this->hyper['helper']['credit']->getMonthlyPayment($price->val())->text()
                        )
                        ?>
                    </div>
                <?php endif; ?>

                <div class="tm-product-teaser__buttons tm-margin-16">
                    <?= $this->render('product/teaser/2024/_buttons', [
                        'product' => $product,
                        'availability' => $availability
                    ]);
                    ?>
                </div>
            </div>

            <?php if ($showFps) : ?>
            <?= $this->render('product/teaser/2024/_fps', [
                'product' => $product,
                'game' => $game ?? ''
            ]);
            ?>
            <?php endif; ?>

            <?= $this->render('product/teaser/2024/_specification', [
                'groups'  => $groups,
                'product' => $product
            ]);
            ?>
        </div>
    </div>
<?php endforeach;
