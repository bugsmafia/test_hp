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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\ModuleHelper;
use HYPERPC\Helper\MicrodataHelper;
use HYPERPC\Helper\MoyskladProductHelper;

/**
 * @var HyperPcViewMoysklad_Product  $this
 */

/** @var MicrodataHelper */
$microdataHelper = $this->hyper['helper']['microdata'];
/** @var ModuleHelper */
$moduleHelper = $this->hyper['helper']['module'];
/** @var MoyskladProductHelper */
$moyskladProductHelper = $this->hyper['helper']['moyskladProduct'];

$certificates = $moduleHelper->renderById($this->moduleId);
$productParts = $this->configParts;

$price = $this->product->getConfigPrice(true);

echo $microdataHelper->getEntityMicrodata($this->product);
?>
<div class="hp-product uk-position-relative">
    <div class="uk-container uk-container-large uk-overflow-hidden">
        <?= $this->hyper['helper']['render']->render('product/full/head', [
            'product'     => $this->product,
            'partGroups'  => [],
            'properties'  => [],
            'price'       => $price,
            'reviewsData' => $this->reviewsData,
        ]); ?>
    </div>

    <?= $this->hyper['helper']['render']->render('product/full/nav', [
        'product'    => $this->product,
        'properties' => [],
        'price'      => $price
    ]); ?>

    <div class="product-content">
        <?php if (!empty($this->product->getParams()->get('capability'))) : ?>
            <div id="product-features">
                <?= HTMLHelper::_('content.prepare', $this->product->getParams()->get('capability')); ?>
            </div>
        <?php endif; ?>

        <?= $this->hyper['helper']['render']->render('product/full/fps', [
            'product' => $this->product
        ]); ?>

        <div id="product-hardware" class="uk-section uk-padding-remove-bottom">
            <?php
            echo $this->hyper['helper']['render']->render('product/full/hardware', [
                'view'         => $this,
                'groups'       => $this->folders,
                'productParts' => $productParts,
                'product'      => $this->product,
                'groupTree'    => $this->foldersTree,
                'allowChange'  => $this->product->isPreOrdered()
            ]);
            ?>
        </div>

        <?php if ($this->product->getCountOfGalleries() > 0) : ?>
            <div id="product-gallery" class="uk-section tm-background-gray-10">
                <?= $this->hyper['helper']['render']->render('product/full/gallery', ['product' => $this->product]); ?>
            </div>
        <?php endif; ?>

        <div id="product-specs" class="uk-section">
            <?php
            echo $this->hyper['helper']['render']->render('product/full/specification', [
                'view'         => $this,
                'groups'       => $this->folders,
                'productParts' => $productParts,
                'product'      => $this->product,
                'groupTree'    => $this->foldersTree
            ]);
            ?>
        </div>

        <?php if ($certificates) : ?>
            <div class="hp-product-pdf-info uk-margin">
                <?= $certificates ?>
            </div>
        <?php endif; ?>

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
