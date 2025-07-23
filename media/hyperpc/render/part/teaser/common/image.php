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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * @var bool         $optionTakenFromPart
 * @var RenderHelper $this
 * @var PartMarker   $part
 */

$imgWidth  = $this->hyper['params']->get('catalog_part_img_width', HP_PART_IMAGE_THUMB_WIDTH);
$imgHeight = $this->hyper['params']->get('catalog_part_img_height', HP_PART_IMAGE_THUMB_HEIGHT);
$image     = $part->getItemImage($imgWidth, $imgHeight);

$viewUrl = $optionTakenFromPart ? $part->option->getViewUrl() : $part->getViewUrl();

$gtmOnclick = $this->hyper['helper']['render']->render('common/teaser/gtmProductClick', ['entity' => $part]);
?>

<a href="<?= $viewUrl ?>"<?= $gtmOnclick ?>>
    <?php if (\array_key_exists('thumb', $image)) :
        $cacheImg = $image['thumb'];

        $imgAttrs = [
            'alt'      => $part->name,
            'src'      => Uri::getInstance($cacheImg->getUrl())->getPath(),
            'width'    => $cacheImg->getWidth(),
            'height'   => $cacheImg->getHeight(),
            'style'    => 'filter: contrast(0.9) brightness(1.16)',
            'loading'  => 'lazy'
        ];
        ?>
        <img <?= $this->hyper['helper']['html']->buildAttrs($imgAttrs) ?>/>
    <?php else : ?>
        <img src="<?= $this->hyper['helper']['image']->getPlaceholderPath($imgWidth, $imgHeight) ?>" alt="<?= Text::_('COM_HYPERPC_NO_IMAGE') ?>">
    <?php endif; ?>
</a>
