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

use JBZoo\Image\Image;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $entity
 * @var Image[]         $images
 * @var bool            $isTeaser
 * @var bool            $linkToPage
 */

if (!isset($linkToPage)) {
    $linkToPage = true;
}

$title      = $entity->get('name');
$needLink   = $this->hyper['params']->get('product_img_link', 1);
$category   = $entity->getFolder();
$teaserType = $category->id ? $category->params->get('teasers_type', 'default') : 'default';
$linkAttrs  = [
    'href'     => $teaserType === 'lumen' ? $entity->getConfigUrl() : $entity->getViewUrl(),
    'class'    => 'uk-position-cover' . ($teaserType === 'lumen' ? ' jsGoToConfigurator' : ''),
];
$gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $entity]);

if (!$linkToPage) {
    unset($linkAttrs['href']);
}

// TODO: show more then one image on product full
if (!$isTeaser) {
    $images = array_slice($images, 0, 1);
}
?>
<?php if (count($images) > 1) : ?>
    <div class="uk-position-relative uk-light">
        <ul class="uk-slidenav-container uk-position-small uk-position-bottom-center uk-dotnav uk-position-z-index" data-uk-switcher>
            <?php foreach ($images as $i => $image) : ?>
                <li><button class="uk-button uk-button-link"></button></li>
            <?php endforeach; ?>
        </ul>

        <ul class="uk-switcher hp-product-images">
            <?php foreach ($images as $image) :
                $imagePath = Uri::getInstance($image->getUrl())->getPath();;
                ?>
                <li style="background-image: url('<?= $imagePath ?>');">
                    <?= $this->hyper['helper']['html']->image($imagePath, [
                        'title'   => $title,
                        'setSize' => false,
                        'class'   => 'uk-invisible',
                        'width'   => $image->getWidth(),
                        'height'  => $image->getHeight()
                    ]);
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($isTeaser && $needLink && $linkToPage) :
            $linkAttrs['class'] .= ' uk-margin-medium-bottom';
            ?>
            <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?><?= $gtmOnclick ?>></a>
        <?php endif; ?>
    </div>
<?php elseif (count($images) === 1) :
    $image = array_shift($images);
    $imagePath = Uri::getInstance($image->getUrl())->getPath();
    $imageFromPart = $entity->params->get('image_from_part');
    ?>
    <div class="uk-position-relative uk-background-cover" style="background-image: url('<?= $imagePath ?>');<?= !$imageFromPart ? ' max-width: ' . $image->getWidth() . 'px; margin: 0 auto' : '' ?>">
        <?php if ($imageFromPart) :
            $teaserImageWidth = $this->hyper['params']->get('product_img_teaser_width', 450);
            $teaserImageHeight = $this->hyper['params']->get('product_img_teaser_height', 450);
            ?>
            <canvas width="<?= $teaserImageWidth ?>" height="<?= $teaserImageWidth ?>"></canvas>
        <?php else : ?>
            <?= $this->hyper['helper']['html']->image($imagePath, [
                'title'   => $title,
                'setSize' => false,
                'class'   => 'uk-invisible',
                'width'   => $image->getWidth(),
                'height'  => $image->getHeight()
            ]);
            ?>
        <?php endif; ?>
        <?php if ($isTeaser && $needLink && $linkToPage) : ?>
            <a <?= $this->hyper['helper']['html']->buildAttrs($linkAttrs) ?><?= $gtmOnclick ?>></a>
        <?php endif; ?>
    </div>
<?php elseif (count($images) === 0) :
    $teaserImageWidth = $this->hyper['params']->get('product_img_teaser_width', 450);
    $teaserImageHeight = $this->hyper['params']->get('product_img_teaser_height', 450);
    ?>
    <canvas width="<?= $teaserImageWidth ?>" height="<?= $teaserImageWidth ?>"></canvas>
<?php endif;
