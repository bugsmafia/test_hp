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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var array           $groups
 * @var array           $options
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 * @var ProductMarker[] $products
 */

$imgWidth  = 1040;
$imgHeight = 800;
?>

<?php if (count($products)) : ?>

    <div class="uk-overflow-hidden">
        <div class="uk-container uk-container-large">
            <?php foreach ($products as $product) :
                $category = $product->getFolder();

                /** @var MoyskladProductHelper */
                $productHelper = $product->getHelper();

                if (!$category->id) {
                    continue;
                }

                $viewUrl    = '/' . ltrim($product->getViewUrl(), '/');
                $price      = $product->getConfigPrice(true);
                $gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $product]);

                $imageList       = $product->getImages(true);
                $teaserImagePath = array_shift($imageList);

                $image    = $product->render()->customSizeImage($teaserImagePath, $imgWidth, $imgHeight);
                $imageSrc = $image instanceof Image ? $image->getPath() : $product->params->get('image_teaser', '', 'hpimagepath');
                $imageSrc = '/' . ltrim($imageSrc, '/');
                ?>

                <div class="hp-product-teaser hp-product-teaser--large">
                    <div class="uk-flex uk-flex-middle uk-flex-wrap">
                        <div class="uk-width-expand uk-visible@l uk-text-center uk-position-relative">
                            <img src="<?= $imageSrc ?>" alt="<?= $product->name ?>" class="hp-product-teaser__image">
                            <a href="<?= $viewUrl ?>" class="uk-position-cover"<?= $gtmOnclick ?>></a>
                        </div>
                        <div class="uk-width-1-2@l uk-position-z-index">
                            <div>
                                <h2 class="uk-h1">
                                    <a href="<?= $viewUrl ?>" class="uk-link-reset"<?= $gtmOnclick ?>>
                                        <?= $product->name ?>
                                    </a>
                                </h2>

                                <div class="uk-margin tm-text-medium">
                                    <?= HTMLHelper::_('content.prepare', strip_tags($product->description)) ?>
                                    <div>
                                        <a href="<?= $viewUrl ?>"<?= $gtmOnclick ?>>
                                            <?= Text::_('COM_HYPERPC_PRODUCT_TEASER_DETAILS') ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="uk-text-center uk-hidden@l">
                                    <a href="<?= $viewUrl ?>"<?= $gtmOnclick ?>>
                                        <img src="<?= $imageSrc ?>" alt="<?= $product->name ?>">
                                    </a>
                                </div>

                                <hr>

                                <div class="uk-flex uk-flex-column">
                                    <div class="uk-margin-bottom hp-product-teaser__purchase">
                                        <div class="uk-grid uk-grid-small uk-flex-middle uk-text-nowrap" uk-margin>

                                            <?= $this->hyper['helper']['render']->render('common/price/item-price', [
                                                'price'      => $price,
                                                'entity'     => $product,
                                                'htmlPrices' => false
                                            ]); ?>

                                            <?= $product->render()->getCartBtn('button', ['buy', 'configurator']) ?>

                                        </div>
                                    </div>
                                </div>

                                <hr class="uk-margin-remove-top">

                                <ul class="uk-grid uk-grid-small uk-child-width-1-2@s uk-text-small" uk-grid uk-height-match="target: .hp-product-teaser__part">
                                    <?= $this->hyper['helper']['render']->render('product/teaser/_item_configurator_list', [
                                        'groups'  => $groups,
                                        'parts'   => $productHelper->getTeaserParts($product, 'large'),
                                        'product' => $product,
                                        'options' => $options
                                    ], 'renderer');
                                    ?>
                                </ul>

                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>

<?php endif;
