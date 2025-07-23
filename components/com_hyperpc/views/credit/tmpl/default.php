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

use HYPERPC\Elements\Element;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var Element                  $element
 * @var PartMarker|ProductMarker $item
 * @var HyperPcViewCredit        $this
 */

$countItems     = $this->cart->getCount();
$totalValue     = $this->cart->getTotalPrice();
$formAction     = $this->hyper['helper']['route']->url();
$countElements  = count($this->elements);
$itemsData      = $this->hyper['helper']['cart']->getItemsDataForRender();
?>

<div class="hp-credit-page uk-container uk-container-large">
    <?php if ($countElements) : ?>
        <h1 class="page-title uk-text-center uk-margin-top jsCartFirstStep">
            <?= Text::_('COM_HYPERPC_CREDIT_TITLE') ?>
        </h1>
        <?php if ($countItems) : ?>
        <form id="credit-form" class="uk-container uk-width-4-5@s uk-width-2-3@m uk-width-4-5@l" action="<?= $formAction ?>" method="post" enctype="multipart/form-data">
            <div class="uk-grid uk-grid-small uk-flex-right" uk-grid>
                <div class="jsCartSecondStep uk-width-1-2@l">

                    <div class="hp-cart-fields uk-margin-medium-top">
                        <div id="hp-cart-form-fields">
                            <div class="hp-fields-wrapper hp-cart-elements uk-margin-medium">
                                <div class="hp-order-elements jsCartUserElements" data-type="user">
                                    <?php foreach ($this->elements as $element) : ?>
                                        <?php if ($element->getType() === 'delivery' || $element->getType() === 'payments') : ?>
                                            <div class="uk-width-1-2@s hp-cart-element hp-cart-element-<?= $element->getType() ?>">
                                                <?= $element->render() ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="uk-width-1-1 hp-cart-element hp-cart-element-<?= $element->getType() ?>">
                                                <?= $element->render() ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="uk-text-center uk-margin-bottom" uk-margin>
                        <a href="<?= $this->hyper['helper']['route']->getCartRoute() ?>" class="uk-button uk-button-normal uk-button-large@m uk-button-default">
                            <?= Text::_('JPREV') ?>
                        </a>
                        <button class="uk-button uk-button-normal uk-button-large@m uk-button-primary jsSubmit">
                            <?= Text::_('COM_HYPERPC_CART_CREDIT_REQUIEST') ?>
                        </button>
                    </div>
                </div>

                <div class="uk-width-1-3@l">
                    <div class="uk-card uk-card-small uk-card-body uk-margin-small-top">
                        <h3 class="uk-card-title"><?= Text::_('COM_HYPERPC_YOUR_CART') ?></h3>
                        <hr>
                        <ul class="hp-cart-checkout-items uk-list uk-list-divider">
                            <?php foreach ($itemsData as $itemId => $itemData) : ?>
                                <li class="hp-cart-checkout-item hp-cart-check-<?= $itemId ?>">

                                    <?= $this->hyper['helper']['render']->render('cart/tmpl/_checkout_item', [
                                        'item'        => $itemData->item,
                                        'type'        => $itemData->type,
                                        'promoPrice'  => $itemData->promoPrice,
                                        'totalPrice'  => $itemData->totalPrice,
                                        'quantity'    => $itemData->quantity
                                    ], 'views'); ?>

                                    <?php
                                    $identifier = 'jform[parts][' . $itemData->itemHash . ']';
                                    if ($itemData->type === CartHelper::TYPE_PRODUCT) {
                                        $identifier = 'jform[products][' . $itemData->itemHash . ']';
                                    }
                                    ?>

                                    <input type="hidden" value="<?= $itemData->item->id ?>" name="<?= $identifier ?>[id]" />
                                    <input type="hidden" value="<?= $itemData->unitPrice->val() ?>" name="<?= $identifier ?>[price]" />
                                    <input type="hidden" value="<?= $itemData->promoRate ?>" name="<?= $identifier ?>[rate]" />
                                    <input type="hidden" value="<?= $itemData->quantity ?>" name="<?= $identifier ?>[quantity]" />

                                    <?php if (in_array($itemData->type, [CartHelper::TYPE_CONFIGURATION, CartHelper::TYPE_PRODUCT])) : ?>
                                        <input type="hidden" value="<?= $itemData->savedConfiguration ?>" name="<?= $identifier ?>[saved_configuration]" />
                                    <?php elseif ($itemData->type === CartHelper::TYPE_PART) : ?>
                                        <input type="hidden" value="<?= $itemData->item->group_id ?>" name="<?= $identifier ?>[group_id]" />
                                        <input type="hidden" value="<?= $itemData->option ?>" name="<?= $identifier ?>[option_id]" />
                                    <?php endif; ?>

                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <hr>
                        <table class="hp-cart-checkout uk-table uk-table-small">
                            <tbody>
                            <tr class="uk-hidden">
                                <td>Промежуточный итог</td>
                                <td><?= $totalValue->html() ?></td>
                            </tr>
                            <tr class="uk-hidden">
                                <td>Скидка</td>
                                <td>0 ₽</td>
                            </tr>
                            <tr class="uk-hidden">
                                <td>Доставка</td>
                                <td>0 ₽</td>
                            </tr>
                            <tr class="tm-text-medium">
                                <td>Итого</td>
                                <td class="uk-text-emphasis">
                                    <div class="jsCartTotalPrice">
                                        <?= $totalValue->html() ?>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="uk-hidden">
                <?= $this->form->getInput('promo_code'); ?>
            </div>
            <div>
                <?php
                echo $this->form->getInput('cid');
                echo $this->form->getInput('total');
                echo $this->form->getInput('form');
                echo $this->form->getInput('delivery_type');
                echo $this->form->getInput('payment_type');
                ?>

                <input type="hidden" name="jform[id]" value="0" />
                <input type="hidden" name="task" value="order.save_credit" />
                <input type="hidden" name="application_id" class="jsApplicationId" />
                <?= HTMLHelper::_('form.token'); ?>
            </div>
        </form>
        <?php endif; ?>
    <?php else : ?>
        <div class="uk-alert uk-alert-warning uk-margin-top">
            Пожалуйста настройте поля формы для кредита
        </div>
    <?php endif; ?>
</div>
