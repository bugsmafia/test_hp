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
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var array           $groups
 * @var array           $options
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 * @var ProductMarker[] $products
 */

$imgWidth  = 215;
$imgHeight = 215;
?>

<?php foreach ($products as $product) :
    $category = $product->getFolder();

    if (!$category->id) {
        continue;
    }

    $viewUrl = '/' . ltrim($product->getViewUrl(), '/');

    if ($category->params instanceof JSON && $category->params->get('teasers_type', 'default') === 'lumen') {
        $viewUrl = '/' . ltrim($category->getViewUrl(), '/');
    }

    $price      = $product->getConfigPrice(true);
    $gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $product]);

    $imageList       = $product->getImages(true);
    $teaserImagePath = array_shift($imageList);

    $image    = $product->render()->customSizeImage($teaserImagePath, $imgWidth, $imgHeight);
    $imageSrc = $image instanceof Image ? $image->getPath() : $product->params->get('image_teaser', '', 'hpimagepath');
    $imageSrc = '/' . ltrim($imageSrc, '/');
    ?>

    <div>
        <div class="hp-lineup-product">
            <div class="hp-lineup-product__image">
                <img src="<?= $imageSrc ?>" alt="<?= $product->name ?>" height="<?= $imgHeight ?>" width="<?= $imgWidth ?>" loading="lazy"/>
                <a href="<?= $viewUrl ?>" class="uk-position-cover uk-hidden-touch"<?= $gtmOnclick ?>></a>
            </div>
            <div class="uk-text-emphasis">
                <?= $product->name ?>
            </div>
            <div class="uk-text-muted">
                <?= Text::_('COM_HYPERPC_PRICE') ?>
                <?= $price->text() ?>
            </div>
            <div class="hp-lineup-product__buttons">
                <a href="<?= $viewUrl ?>" class="uk-button uk-button-primary uk-width-1-1"<?= $gtmOnclick ?>>
                    <?= Text::_('COM_HYPERPC_DETAILS') ?>
                </a>
                <a href="<?= '/' . ltrim($product->getConfigUrl(), '/') ?>" class="uk-button uk-button-default uk-width-1-1">
                    <?= Text::_('COM_HYPERPC_CONFIGURATOR') ?>
                </a>
            </div>
        </div>
    </div>

<?php endforeach;
