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

use HYPERPC\Helper\FpsHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 * @var string          $game
 */

/** @var FpsHelper */
$fpsHelper = $this->hyper['helper']['fps'];

if (!$fpsHelper->showFps($product->getFolderId())) {
    return;
}

$productFps = $fpsHelper->getFps($product);
$averageFps = $fpsHelper->calculateAverageFps($productFps, $game);

$props = [
    'productFps' => $productFps,
    'averageFps' => $averageFps,
    'activeGame' => $game
];
?>
<?php if ($averageFps > 0) : ?>
    <div class="vueProductTeaserFps tm-product-teaser__fps" data-props='<?= \json_encode($props) ?>' style="height: 64px"></div>
<?php endif;
