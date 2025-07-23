<?php

use Joomla\CMS\HTML\HTMLHelper;
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

use JBZoo\Image\Image;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var string          $name
 * @var Image[]         $images
 * @var ProductMarker   $entity
 */

$hasColors = preg_match('/[a-z0-9]{13}/', array_key_first($images));
$uniqId = $hasColors ? uniqid('colors-') : '';
?>
<?php if (!$hasColors && count($images) <= 1) :
    $gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $entity]);
    $productUrl = $entity->getViewUrl();

    $imagePath = $this->hyper['helper']['image']->getPlaceholderPath(0, 450);
    if (count($images) === 1) {
        $image = array_shift($images);
        $imagePath = Uri::getInstance($image->getUrl())->getPath();
    }

    $teaserImageWidth = $this->hyper['params']->get('product_img_teaser_width', 450);
    $teaserImageHeight = $this->hyper['params']->get('product_img_teaser_height', 450);
    ?>
    <a href="<?= $productUrl ?>" aria-label="<?= $entity->getName() ?>"<?= $gtmOnclick ?>>
        <canvas
            width="<?= $teaserImageWidth ?>"
            height="<?= $teaserImageWidth ?>"
            class="uk-background-cover"
            style="background-image: url('<?= $imagePath ?>'); filter: contrast(0.9) brightness(1.16)"
        ></canvas>
    </a>
<?php else : ?>
    <div id="<?= $uniqId ?>" class="uk-switcher">
        <?php
        $i = 0;
        foreach ($images as $key => $image) : ?>
            <div class="<?= ++$i === 1 ? 'uk-active' : '' ?>">
                <?= $this->render($name, [
                    'images' => [$image],
                    'entity' => $entity
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($hasColors) :
    $colorsFieldId = $this->hyper['params']->get('product_colors_field', 0, 'int');
    $colorField = $this->hyper['helper']['fields']->getFieldById($colorsFieldId);
    if (!$colorField) {
        return;
    }

    $fieldColors = $colorField->fieldparams->get('options', [], 'arr');

    $colors = [];
    foreach ($fieldColors as $colorData) {
        $colors[$colorData['key']] = $colorData;
    }

    $colorKeys = array_keys($images);

    $i = 0;
    ?>
    <ul class="uk-grid uk-grid-small uk-flex-center uk-position-relative tm-product-teaser__colors" data-uk-switcher="connect: #<?= $uniqId ?>; swiping: false" style="padding-top: 24px; margin-top: -24px">
        <?php foreach ($colorKeys as $colorKey) :
            $colorData = $colors[$colorKey];
            $img = HTMLHelper::_('cleanImageURL', $colorData['value']);
            ?>
            <li class="<?= ++$i === 1 ? 'uk-active ' : '' ?>uk-flex uk-flex-middle uk-flex-column">
                <a href="#" class="uk-flex uk-flex-middle uk-flex-center">
                    <span class="uk-icon uk-icon-button" style="background-image: url(<?= $img->url ?>);"></span>
                </a>
                <div class="uk-text-nowrap uk-flex-center tm-product-teaser__color-description tm-margin-4-top">
                    <?= $this->hyper['helper']['string']->filterLanguage($colorData['name']) ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif;
