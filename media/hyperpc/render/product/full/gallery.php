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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 */

$imgWidth   = $this->hyper['params']->get('product_gallery_width', 400);
$imgHeight  = $this->hyper['params']->get('product_gallery_height', 267);

$galleryImages = $product->getGalleryImages();

$galleryWrapperClass = 'uk-grid uk-grid-small uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-5@l uk-child-width-1-6@xl uk-flex-center';
?>

<div class="uk-container uk-container-expand" style="max-width: 2540px">
    <h2 class="uk-h1 uk-text-center"><?= Text::_('COM_HYPERPC_PHOTOS') . ' ' . $product->name ?></h2>

    <?php if ($product->getCountOfGalleries() === 1) : ?>
        <?php if (isset($galleryImages['gallery_1'])) : ?>
            <div class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                <?= $product->render()->gallery('gallery_1', $imgWidth, $imgHeight) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($galleryImages['gallery_2'])) : ?>
            <div class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                <?= $product->render()->gallery('gallery_2', $imgWidth, $imgHeight) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($galleryImages['gallery_3'])) : ?>
            <div class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                <?= $product->render()->gallery('gallery_3', $imgWidth, $imgHeight) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($galleryImages['gallery_4'])) : ?>
            <div class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                <?= $product->render()->gallery('gallery_4', $imgWidth, $imgHeight) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($galleryImages['gallery_5'])) : ?>
            <div class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                <?= $product->render()->gallery('gallery_5', $imgWidth, $imgHeight) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($galleryImages['gallery_6'])) : ?>
            <div class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                <?= $product->render()->gallery('gallery_6', $imgWidth, $imgHeight) ?>
            </div>
        <?php endif; ?>
    <?php elseif ($product->getCountOfGalleries() > 1) :
        $galleryNumber = 1; ?>

        <ul class="uk-flex-center" uk-tab>
            <?php if (isset($galleryImages['gallery_1'])) : ?>
                <li><a href="#"><?= Text::sprintf('COM_HYPERPC_PRODUCT_GALLERY_TAB', $galleryNumber) ?></a></li>
                <?php $galleryNumber++ ?>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_2'])) : ?>
                <li><a href="#"><?= Text::sprintf('COM_HYPERPC_PRODUCT_GALLERY_TAB', $galleryNumber) ?></a></li>
                <?php $galleryNumber++ ?>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_3'])) : ?>
                <li><a href="#"><?= Text::sprintf('COM_HYPERPC_PRODUCT_GALLERY_TAB', $galleryNumber) ?></a></li>
                <?php $galleryNumber++ ?>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_4'])) : ?>
                <li><a href="#"><?= Text::sprintf('COM_HYPERPC_PRODUCT_GALLERY_TAB', $galleryNumber) ?></a></li>
                <?php $galleryNumber++ ?>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_5'])) : ?>
                <li><a href="#"><?= Text::sprintf('COM_HYPERPC_PRODUCT_GALLERY_TAB', $galleryNumber) ?></a></li>
                <?php $galleryNumber++ ?>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_6'])) : ?>
                <li><a href="#"><?= Text::sprintf('COM_HYPERPC_PRODUCT_GALLERY_TAB', $galleryNumber) ?></a></li>
            <?php endif; ?>
        </ul>

        <ul class="uk-switcher">
            <?php if (isset($galleryImages['gallery_1'])) : ?>
                <li class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                    <?= $product->render()->gallery('gallery_1', $imgWidth, $imgHeight) ?>
                </li>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_2'])) : ?>
                <li class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                    <?= $product->render()->gallery('gallery_2', $imgWidth, $imgHeight) ?>
                </li>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_3'])) : ?>
                <li class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                    <?= $product->render()->gallery('gallery_3', $imgWidth, $imgHeight) ?>
                </li>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_4'])) : ?>
                <li class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                    <?= $product->render()->gallery('gallery_4', $imgWidth, $imgHeight) ?>
                </li>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_5'])) : ?>
                <li class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                    <?= $product->render()->gallery('gallery_5', $imgWidth, $imgHeight) ?>
                </li>
            <?php endif; ?>
            <?php if (isset($galleryImages['gallery_6'])) : ?>
                <li class="<?= $galleryWrapperClass ?>" uk-lightbox="animation: fade" uk-grid>
                    <?= $product->render()->gallery('gallery_6', $imgWidth, $imgHeight) ?>
                </li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

    <div class="uk-margin-medium uk-text-center@s uk-text-muted">
        * <?= Text::_('COM_HYPERPC_PRODUCT_GALLERY_DISCLAIMER') ?>
    </div>
</div>
