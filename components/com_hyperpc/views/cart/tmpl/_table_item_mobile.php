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

<div class="uk-flex uk-flex-middle">

    <div class="uk-visible@s" style="max-width:120px">
        <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_image', [
            'item' => $item,
            'type' => $type
        ], 'views'); ?>
    </div>

    <div class="uk-width-expand">
        <div>
            <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_title', [
                'item'     => $item,
                'type'     => $type
            ], 'views'); ?>
        </div>

        <div class="hp-cart-item-row__price">
            <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/price', [
                'unitPrice'  => $unitPrice,
                'promoPrice' => $promoPrice,
                'totalPrice' => $totalPrice,
                'quantity'   => $quantity
            ], 'views'); ?>
        </div>

        <?php if ($availability !== Stockable::AVAILABILITY_INSTOCK) : ?>
            <div class="hp-cart-item-row__availability">
                <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/availability', [
                    'item'         => $item,
                    'availability' => $availability,
                    'onlyUpgrade'  => $onlyUpgrade,
                ], 'views');
                ?>
            </div>
        <?php endif; ?>

        <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_specs_link', [
            'item'    => $item,
            'itemKey' => $productHash,
            'type'    => $type
        ], 'views'); ?>

        <?php
        if ($type === CartHelper::TYPE_POSITION && $item instanceof MoyskladProduct) {
            echo $this->hyper['helper']['render']->render('cart/tmpl/elements/item_service_moysklad', [
                'item'    => $item,
                'itemKey' => $productHash,
            ], 'views');
        }
        ?>

    </div>

    <div>
        <?= $this->hyper['helper']['render']->render('cart/tmpl/elements/quantity', [
            'item'          => $item,
            'isDisable'     => ($type === CartHelper::TYPE_PART && $unitPrice->val() !== $promoPrice->val()),
            'layoutCompact' => true,
            'itemId'        => $productHash,
            'itemType'      => $type,
            'quantity'      => $quantity
        ], 'views'); ?>
    </div>

    <div class="hp-cart-item-row__remove">
        <?php
        echo $this->hyper['helper']['render']->render('cart/tmpl/elements/remove', [
            'item'  => $item,
            'price' => $promoPrice
        ], 'views');
        ?>
    </div>
</div>

<?= $this->hyper['helper']['render']->render('cart/tmpl/elements/item_specs_' . $type, [
    'item'    => $item,
    'itemKey' => $productHash
], 'views');
