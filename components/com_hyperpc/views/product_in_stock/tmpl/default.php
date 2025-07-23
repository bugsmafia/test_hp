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
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\ModuleHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\MicrodataHelper;

/**
 * @var HyperPcViewProduct_In_Stock $this
 */

/** @var MicrodataHelper */
$microdataHelper = $this->hyper['helper']['microdata'];
/** @var ModuleHelper */
$moduleHelper = $this->hyper['helper']['module'];
/** @var RenderHelper */
$renderHelper = $this->hyper['helper']['render'];

$cacheGroup   = $this->product->getCacheGroup();
$certificates = $moduleHelper->renderById($this->hyper['params']->get('product_certificates'));

$price = $this->product->getConfigPrice(true);

echo $microdataHelper->getEntityMicrodata($this->product);
?>
<div class="hp-product uk-position-relative">

    <div class="uk-container uk-container-large uk-overflow-hidden">
        <?= $renderHelper->render('product_in_stock/full/head', [
            'reviewsData' => $this->reviewsData,
            'product'     => $this->product,
            'price'       => $price,
        ]); ?>
    </div>

    <?= $renderHelper->render('product/full/nav', [
        'product'    => $this->product,
        'properties' => [],
        'price'      => $price
    ]) ?>

    <div class="product-content">
        <?php
        echo $renderHelper->render('product/full/fps', [
            'product' => $this->product
        ]);
        ?>

        <div id="product-hardware" class="uk-section uk-padding-remove-bottom">
            <?php
            $groups = $this->getGroups();
            echo $renderHelper->render('product/full/hardware', [
                'view'         => $this,
                'groups'       => $groups,
                'productParts' => $this->configParts,
                'product'      => $this->product,
                'groupTree'    => $this->getGroupTree($groups),
                'allowChange'  => false
            ]);
            ?>
        </div>

        <?php if ($this->product->params->get('service') && trim($this->product->params->get('service')) !== '' && false) : // temporarily hidden ?>
            <div id="product-service" class="uk-section uk-section-muted">
                <div class="uk-container uk-container-expand">
                    <h2 class="uk-h1 uk-text-center"><?= Text::_('COM_HYPERPC_PRODUCT_SERVICE') ?></h2>
                    <div>
                        <?= HTMLHelper::_('content.prepare', $this->product->params->get('service')) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->product->getCountOfGalleries() > 0) : ?>
            <div id="product-gallery" class="uk-section tm-background-gray-10">
                <?= $renderHelper->render('product/full/gallery', ['product' => $this->product], $cacheGroup); ?>
            </div>
        <?php endif; ?>

        <div id="product-specs" class="uk-section">
            <?php
            echo $renderHelper->render('product/full/specification', [
                'view'    => $this,
                'product' => $this->product,
            ]);
            ?>
        </div>

        <?php if ($this->hyper['params']->get('show_reviews', 0)) : ?>
            <?php
            echo $this->hyper['helper']['render']->render('reviews/default', [
                'item'        => $this->product,
                'reviewsData' => $this->reviewsData,
            ]);
            ?>
        <?php endif; ?>
    </div>
</div>
