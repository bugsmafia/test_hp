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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var RenderHelper        $this
 * @var CategoryMarker[]    $groups
 * @var ProductMarker[]     $products
 * @var ?bool               $showFps
 * @var ?string             $game
 * @var ?string             $instock
 */

if (!isset($showFps)) {
    $showFps = true;
}

if (!isset($game)) {
    $game = '';
}

if (!isset($instock)) {
    $instock = 'default';
}

$containerWidthClass = count($products) > 4 ? 'uk-container-large' : 'uk-container-expand';
?>

<div class="tm-products-slider uk-slider uk-slider-container" data-uk-slider="finite: true;">
    <div class="uk-container <?= $containerWidthClass ?>">
        <div class="uk-slidenav-container uk-flex-right">
            <button type="button" class="uk-icon tm-margin-16-bottom uk-slidenav uk-slidenav-previous" hidden data-uk-slidenav-previous data-uk-slider-item="previous"></button>
            <button type="button" class="uk-icon tm-margin-16-bottom uk-slidenav uk-slidenav-next" hidden data-uk-slidenav-next data-uk-slider-item="next"></button>
        </div>
        <div class="uk-grid uk-grid-small uk-slider-items" data-uk-height-match=".tm-product-teaser__description">
            <?= $this->render('product/teaser/2024/default', [
                'groups'   => $groups,
                'products' => $products,
                'showDesc' => $showDesc,
                'showFps'  => $showFps,
                'game'     => $game,
                'instock'  => $instock
            ]);
            ?>
        </div>
    </div>
</div>
