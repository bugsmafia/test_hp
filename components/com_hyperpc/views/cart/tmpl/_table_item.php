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

use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var         array $groups
 * @var         int $quantity
 * @var         string $type
 * @var         string $productHash
 * @var         string $availability
 * @var         bool   $onlyUpgrade
 * @var         RenderHelper $this
 * @var         Money $totalPrice
 * @var         Money $unitPrice
 * @var         Money $promoPrice
 * @var         Entity $item
 */

?>
<td class="hp-cart-cell-img">
    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_image', [
        'item' => $item,
        'type' => $type
    ], 'views'); ?>
</td>
<td class="hp-cart-cell-title">
    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_title', [
        'item' => $item,
        'type' => $type
    ], 'views'); ?>

    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_specs_link', [
        'item'    => $item,
        'itemKey' => $productHash,
        'type'    => $type
    ], 'views'); ?>

    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_specs_' . $type, [
        'item'    => $item,
        'itemKey' => $productHash
    ], 'views'); ?>

    <?php
    if ($type === CartHelper::TYPE_POSITION && $item instanceof MoyskladProduct) {
        echo $this->hyper['helper']['render']->render('cart/tmpl/elements/item_service_moysklad', [
            'item'    => $item,
            'itemKey' => $productHash,
        ], 'views');
    }
    ?>
</td>
<td class="uk-text-center@m">
    <?php if ($item instanceof Stockable) {
        echo $this->hyper['helper']['render']->render('cart/tmpl/elements/availability', [
            'item'         => $item,
            'availability' => $availability,
            'onlyUpgrade'  => $onlyUpgrade,
        ], 'views');
    }
    ?>
</td>
<td class="hp-cart-cell-quantity">
    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/quantity', [
        'item'          => $item,
        'isDisable'     => ($type === CartHelper::TYPE_PART && $unitPrice->val() !== $promoPrice->val()),
        'layoutCompact' => false,
        'itemId'        => $productHash,
        'itemType'      => $type,
        'quantity'      => $quantity
    ], 'views'); ?>
</td>
<td>
    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/price', [
        'unitPrice'  => $unitPrice,
        'promoPrice' => $promoPrice,
        'totalPrice' => $totalPrice,
        'quantity'   => $quantity
    ], 'views'); ?>
</td>
<td class="hp-cart-cell-action">
    <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/remove', [
        'item'  => $item,
        'price' => $promoPrice
    ], 'views'); ?>
</td>
