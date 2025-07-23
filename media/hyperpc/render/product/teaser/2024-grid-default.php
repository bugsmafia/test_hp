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
 * @var ?bool               $jsSupport
 * @var ?string             $instock
 */

if (!isset($showFps)) {
    $showFps = true;
}

if (!isset($game)) {
    $game = '';
}

if (!isset($jsSupport)) {
    $jsSupport = false;
}

if (!isset($instock)) {
    $instock = 'default';
}
?>

<div class="tm-products-grid uk-grid-match<?= $jsSupport ? ' jsProductsGrid' : '' ?>"
     data-uk-height-match="target: .tm-product-teaser__description">
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
