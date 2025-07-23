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

use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * @var ProductMarker|PartMarker $item
 * @var int                      $itemId
 * @var string                   $itemType
 * @var int                      $quantity
 * @var bool                     $isDisable
 * @var bool                     $layoutCompact
 */

$isProductInStock = false;
$itemType = "positions";

$iconMinus   = $layoutCompact ? 'chevron-down; ratio: 1.2' : 'minus; ratio:0.8';
$iconPlus    = $layoutCompact ? 'chevron-up; ratio: 1.2' : 'plus; ratio:0.8';
$layoutClass = $layoutCompact ? ' hp-cart-quantity-compact' : '';

$inputAttrs = [
    'maxlength' => 2,
    'type'      => 'text',
    'value'     => $quantity,
    'name'      => 'jform[' . $itemType . '][' . $itemId . '][quantity]',
    'class'     => 'uk-input uk-form-small uk-form-width-xsmall uk-text-center jsItemQuantity',
];

if ($isDisable) {
    $inputAttrs['class'] .= ' uk-disabled';
}
?>
<div class="jsQuantityWrapper hp-cart-quantity<?= $layoutClass ?> uk-text-nowrap">
    <?php if (!$isProductInStock) : ?>
        <a class="jsItemMinus<?= $quantity === 1 ? ' uk-disabled' : '' ?>" uk-icon="icon: <?= $iconMinus ?>"></a>
    <?php else : ?>
        <a class="jsItemMinus<?= ($isDisable) ? ' uk-disabled' : '' ?>" uk-icon="icon: <?= $iconMinus ?>"></a>
    <?php endif; ?>

    <input <?= $this->hyper['helper']['html']->buildAttrs($inputAttrs) ?>>

    <a class="jsItemPlus<?= ($isDisable) ? ' uk-disabled' : '' ?>" uk-icon="icon: <?= $iconPlus ?>"></a>
</div>
