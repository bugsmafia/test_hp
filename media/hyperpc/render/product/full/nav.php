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
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\FpsHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper  $this
 * @var array         $properties
 * @var ProductMarker $product
 * @var Money         $price
 */

$showFeatures = !$product->isFromStock() && !empty($product->getParams()->get('capability'));

/** @var FpsHelper */
$fpsHelper = $this->hyper['helper']['fps'];
$categoryId = $product->getFolderId();
$showFps = $fpsHelper->showFps($categoryId);

?>
<div id="hp-product-nav" class="jsSeparatedNavbar hp-goods-nav tm-page-sticky-nav uk-navbar-container" uk-sticky>
    <nav class="uk-container uk-container-large">
        <div class="uk-navbar uk-flex-wrap">
            <div class="uk-navbar-left tm-page-sticky-nav__menu">
                <ul class="uk-navbar-nav jsScrollableNav" uk-scrollspy-nav="closest: li; scroll: true; overflow: true; offset: -8">
                    <?php if ($showFeatures) : ?>
                        <li>
                            <a href="#product-features"><?= Text::_('COM_HYPERPC_TAB_FEATURES') ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($showFps) : ?>
                        <li>
                            <a href="#product-performance"><?= Text::_('COM_HYPERPC_TAB_PERFORMANCE') ?></a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="#product-hardware"><?= Text::_('COM_HYPERPC_TAB_EQUIPMENT') ?></a>
                    </li>
                    <li>
                        <a href="#product-service"><?= Text::_('COM_HYPERPC_PRODUCT_SERVICE') ?></a>
                    </li>
                    <?php if ($product->getCountOfGalleries() > 0) : ?>
                        <li>
                            <a href="#product-gallery"><?= Text::_('COM_HYPERPC_PHOTOS') ?></a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="#product-specs"><?= Text::_('COM_HYPERPC_TAB_SPECIFICATION') ?></a>
                    </li>
                    <?php if ($this->hyper['params']->get('show_reviews', 0)) : ?>
                        <li>
                            <a href="#product-reviews"><?= Text::_('COM_HYPERPC_REVIEWS') ?></a>
                        </li>
                    <?php endif; ?>
                    <li class="tm-page-sticky-nav__totop">
                        <a href="#" uk-scroll uk-totop></a>
                    </li>
                </ul>
            </div>
            <div class="uk-navbar-right">
                <div class="hp-product-purchase uk-navbar-item uk-text-nowrap">
                    <div class="uk-grid uk-grid-small uk-flex-middle uk-text-nowrap" uk-margin>
                        <?= $this->hyper['helper']['render']->render('product/full/purchase', [
                            'product'    => $product,
                            'properties' => $properties,
                            'price'      => $price,
                            'layout'     => 'nav'
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>
