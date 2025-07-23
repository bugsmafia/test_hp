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

use JBZoo\Data\Data;
use JBZoo\Utils\Filter;
use HYPERPC\Elements\Element;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var HyperPcViewCart $this
 */

$countItems = count($this->items);

$totalValue     = $this->cart->getTotalPrice();
$totalVatValue  = $this->hyper['helper']['money']->getVat($totalValue);
$formAction     = $this->hyper['helper']['route']->url();
$isMobile       = $this->hyper['detect']->isMobile();

$creditEnabled  = $this->hyper['params']->get('credit_enable', '0');
$monthlyPayment = $this->hyper['helper']['credit']->getMonthlyPayment($totalValue);
$userIsManager  = (bool) $this->hyper['input']->cookie->get(HP_COOKIE_HMP);

$creditCalculatorArgs = [
    'view'  => 'credit_calculator',
    'type'  => 'product',
    'price' => Filter::int($totalValue->val()),
    'tmpl'  => 'component',
];
$calculateCreditUrl = $this->hyper['route']->build($creditCalculatorArgs);
?>

<div class="hp-cart-page uk-container uk-container-large">
    <h1 class="page-title uk-text-center uk-margin-top jsCartFirstStep">
        <?php
        if ($userIsManager) {
            echo Text::_('COM_HYPERPC_BASKET_MANAGER');
        } else {
            echo Text::_('COM_HYPERPC_BASKET');
        }
        ?>
    </h1>
    <?php if ($countItems) :
        $itemsData = $this->hyper['helper']['cart']->getItemsDataForRender();
        ?>
        <form id="order-form" class="uk-container" action="<?= $formAction ?>" method="post" enctype="multipart/form-data">

            <div class="jsCartFirstStep uk-width-expand">
                <div class="hp-cart-table-wrapper">

                    <?php if (count($itemsData) > 1) : ?>
                        <div class="uk-text-right">
                            <button type="button" class="jsCartClearAll uk-button uk-button-link"><?= Text::_('COM_HYPERPC_CLEAR_ALL') ?></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($isMobile) : ?>
                        <ul class="uk-list uk-list-divider">
                            <?php foreach ($itemsData as $itemId => $itemData) :
                                $rowAttr = [
                                    'data'  => $itemData->dataAttrs,
                                    'class' => 'jsCartItemRow hp-cart-item-row hp-item-' . $itemId . ' hp-item-row-' . $itemData->productType
                                ];
                                $layout = $itemData->type === CartHelper::TYPE_PART ? 'part' : 'product';
                                ?>
                                <li <?= $this->hyper['helper']['html']->buildAttrs($rowAttr) ?>>
                                    <?= $this->renderLayout('_table_item_mobile', [
                                        'type'         => $itemData->type,
                                        'item'         => $itemData->item,
                                        'availability' => $itemData->availability,
                                        'onlyUpgrade'  => $itemData->onlyUpgrade,
                                        'quantity'     => $itemData->quantity,
                                        'productHash'  => $itemData->itemHash,
                                        'unitPrice'    => $itemData->unitPrice,
                                        'promoPrice'   => $itemData->promoPrice,
                                        'totalPrice'   => $itemData->totalPrice
                                    ], false); ?>

                                    <?= $this->renderLayout('elements/item_inputs', [
                                        'itemHash'           => $itemData->itemHash,
                                        'type'               => $itemData->type,
                                        'item'               => $itemData->item,
                                        'unitPrice'          => $itemData->unitPrice,
                                        'promoRate'          => $itemData->promoRate,
                                        'option'             => $itemData->option,
                                        'savedConfiguration' => $itemData->savedConfiguration
                                    ], false); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="tm-border-top uk-margin-bottom uk-grid uk-flex-between">
                            <?php if ($this->hyper['params']->get('enable_promo_code', 1)) : ?>
                                <div class="uk-margin-small-top">
                                    <?= $this->form->renderField('promo_code') ?>
                                </div>
                            <?php endif; ?>
                            <div class="uk-text-right uk-width-expand tm-text-medium uk-text-nowrap uk-margin-small-top" style="min-width: 140px">
                                <div class="jsCartTotalPrice" style="line-height: 1">
                                    <?= Text::sprintf('COM_HYPERPC_BASKET_TOTAL_PRICE', $totalValue->html()) ?>
                                </div>
                                <?php if ($creditEnabled) : ?>
                                    <div class="jsCartMonthlyPayment uk-text-small">
                                        <?php if (isset($calculateCreditUrl)) : ?>
                                            <a href="<?= $calculateCreditUrl ?>" target="_blank" class="uk-text-muted tm-link-dashed jsLoadIframe">
                                                <?= Text::_('COM_HYPERPC_INSTALLMENT_CALCULATE_CREDIT') ?>
                                            </a>
                                        <?php else : ?>
                                            <span class="uk-text-middle uk-text-muted">
                                                <?php if ($this->hyper['helper']['credit']->getDefaultCreditRate() > 0) : ?>
                                                    <?= Text::sprintf('COM_HYPERPC_CREDIT_MONTHLY_PAYMENT', $monthlyPayment->html()); ?>
                                                <?php else : ?>
                                                    <?= Text::sprintf('COM_HYPERPC_INSTALLMENT_MONTHLY_PAYMENT', $monthlyPayment->html()); ?>
                                                <?php endif; ?>
                                                <?= $this->hyper['helper']['render']->render('common/price/credit-info'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <table class="jsCartItemsTable hp-cart-table uk-table uk-table-divider uk-table-middle uk-margin-small-top">
                            <thead>
                                <tr>
                                    <th colspan="2"><?= Text::_('COM_HYPERPC_BASKET_PRODUCT') ?></th>
                                    <th class="uk-text-center"><?= Text::_('COM_HYPERPC_AVAILABILITY') ?></th>
                                    <th><?= Text::_('COM_HYPERPC_BASKET_PRODUCT_COUNT') ?></th>
                                    <th colspan="3"><?= Text::_('COM_HYPERPC_BASKET_PRODUCT_PRICE') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                /** @var Data $itemData */
                                foreach ($itemsData as $itemId => $itemData) :
                                    $rowAttr = [
                                        'data'  => $itemData->dataAttrs,
                                        'class' => 'jsCartItemRow hp-item-row hp-item-' . $itemId . ' hp-item-row-' . $itemData->productType
                                    ];

                                    if ($itemData->get('isInStock')) {
                                        $rowAttr['data']->set('stock-id', $itemData->get('isInStock'));
                                    }

                                    if ($itemData->get('type') === 'product' && $itemData->get('availability') === Stockable::AVAILABILITY_INSTOCK) {
                                        $singleParts = $itemData->get('singleParts', []);
                                        foreach ($singleParts as $itemKey) {
                                            if (!empty($itemsData->get($itemKey))) {
                                                $itemsData[$itemKey]->set('availability', Stockable::AVAILABILITY_INSTOCK);
                                            }
                                        }
                                    }
                                    ?>
                                    <tr <?= $this->hyper['helper']['html']->buildAttrs($rowAttr) ?>>

                                        <?= $this->renderLayout('_table_item', [
                                            'type'         => $itemData->type,
                                            'item'         => $itemData->item,
                                            'availability' => $itemData->availability,
                                            'onlyUpgrade'  => $itemData->onlyUpgrade,
                                            'quantity'     => $itemData->quantity,
                                            'productHash'  => $itemData->itemHash,
                                            'unitPrice'    => $itemData->unitPrice,
                                            'promoPrice'   => $itemData->promoPrice,
                                            'totalPrice'   => $itemData->totalPrice
                                        ], false); ?>

                                        <td hidden>
                                            <?= $this->renderLayout('elements/item_inputs', [
                                                'itemHash'           => $itemData->itemHash,
                                                'type'               => $itemData->type,
                                                'item'               => $itemData->item,
                                                'unitPrice'          => $itemData->unitPrice,
                                                'promoRate'          => $itemData->promoRate,
                                                'option'             => $itemData->option,
                                                'savedConfiguration' => $itemData->savedConfiguration
                                            ], false); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="uk-text-top">
                                        <?php if ($this->hyper['params']->get('enable_promo_code', 1)) : ?>
                                            <div class="uk-margin-right">
                                                <?= $this->form->renderField('promo_code') ?>
                                            </div>
                                        <?php endif; ?>
                                    </th>
                                    <th colspan="2" class="uk-text-top">
                                        <div class="uk-text-nowrap tm-text-medium uk-text-emphasis">
                                            <div class="jsCartTotalPrice">
                                                <?= Text::sprintf('COM_HYPERPC_BASKET_TOTAL_PRICE', $totalValue->html()) ?>
                                            </div>
                                            <?php if ($creditEnabled) : ?>
                                                <div class="jsCartMonthlyPayment uk-text-small">
                                                    <?php if (isset($calculateCreditUrl)) : ?>
                                                        <a href="<?= $calculateCreditUrl ?>" target="_blank" class="uk-text-muted tm-link-dashed jsLoadIframe">
                                                            <?= Text::_('COM_HYPERPC_INSTALLMENT_CALCULATE_CREDIT') ?>
                                                        </a>
                                                    <?php else : ?>
                                                        <span class="uk-text-middle uk-text-muted">
                                                            <?php if ($this->hyper['helper']['credit']->getDefaultCreditRate() > 0) : ?>
                                                                <?= Text::sprintf('COM_HYPERPC_CREDIT_MONTHLY_PAYMENT', $monthlyPayment->html()); ?>
                                                            <?php else : ?>
                                                                <?= Text::sprintf('COM_HYPERPC_INSTALLMENT_MONTHLY_PAYMENT', $monthlyPayment->html()); ?>
                                                            <?php endif; ?>
                                                            <?= $this->hyper['helper']['render']->render('common/price/credit-info'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>

                </div>

                <div class="jsStickyBottom jsFirstStepButtons uk-flex uk-child-width-1-1 uk-child-width-auto@s uk-flex-center">
                    <div>
                        <button id="form-fields" class="jsToSecondStep uk-button uk-button-large uk-button-primary uk-width-1-1"
                                data-href="#hp-cart-form-fields"
                                type="button"
                            >
                            <?= Text::_('COM_HYPERPC_GO_TO_ORDER') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="uk-grid uk-grid-small uk-flex-right" uk-grid>

                <div class="jsCartSecondStep uk-width-1-2@l" hidden>

                    <div class="uk-margin-medium uk-margin-medium-top">
                        <?php if (count($this->elements)) : ?>
                            <div class="hp-fields-wrapper hp-cart-elements">
                                <div class="hp-order-elements jsCartUserElements" data-type="user">
                                    <?php
                                    /** @var Element $element */
                                    foreach ($this->elements as $element) :
                                        if (!$userIsManager && $element->isForManager()) {
                                            continue;
                                        }
                                        ?>
                                        <div class="hp-cart-element hp-cart-element-<?= $element->getType() ?>">
                                            <?= $element->render() ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($this->showCaptcha) : ?>
                            <div>
                                <?= $this->form->getInput('captcha') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="jsSecondStepButtons uk-flex uk-flex-wrap-reverse uk-flex-center uk-margin-bottom" uk-margin>
                        <button class="jsToFirstStep uk-button uk-button-large uk-button-default uk-margin-small-right uk-visible@s" type="button">
                            <?= Text::_('JPREV') ?>
                        </button>
                        <span class="jsToFirstStep uk-link uk-hidden@s" style="padding: 5px 15px">
                            <?= Text::_('COM_HYPERPC_GO_BACK') ?>
                        </span>
                        <button class="jsSubmitOrder uk-button uk-button-large uk-button-primary uk-width-1-1 uk-width-auto@s" type="submit">
                            <?= Text::_('COM_HYPERPC_CART_PLACE_ORDER') ?>
                        </button>
                    </div>

                    <?php
                    $agreementText = $this->hyper['helper']['module']->renderById($this->hyper['params']->get('order_agreement_text'));
                    if (!empty($agreementText)) : ?>
                        <div class="uk-text-small uk-text-muted uk-text-center uk-margin-bottom">
                            <?= $agreementText ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!$isMobile) : ?>
                    <div class="uk-width-1-3@l jsCartSummary jsCartSecondStep" hidden>
                        <div class="uk-card uk-card-small uk-card-body uk-margin-small-top">
                            <h3 class="uk-card-title">
                                <?php
                                if ($userIsManager) {
                                    echo Text::_('COM_HYPERPC_BASKET_MANAGER');
                                } else {
                                    echo Text::_('COM_HYPERPC_YOUR_CART');
                                }
                                ?>
                            </h3>
                            <hr>
                            <ul class="hp-cart-checkout-items uk-list uk-list-divider">
                                <?php foreach ($itemsData as $itemId => $itemData) :
                                    $itemClass = 'hp-cart-check-' . $itemId;
                                    ?>

                                    <li class="hp-cart-checkout-item <?= $itemClass ?>">
                                        <?= $this->hyper['helper']['render']->render('cart/tmpl/_checkout_item', [
                                            'item'        => $itemData->item,
                                            'type'        => $itemData->type,
                                            'promoPrice'  => $itemData->promoPrice,
                                            'totalPrice'  => $itemData->totalPrice,
                                            'quantity'    => $itemData->quantity
                                        ], 'views'); ?>
                                    </li>

                                <?php endforeach; ?>
                            </ul>
                            <hr>
                            <table class="hp-cart-checkout uk-table uk-table-small">
                                <tbody>
                                    <tr class="jsCartTotal tm-text-medium">
                                        <td>
                                            <div><?= Text::_('COM_HYPERPC_TOTAL') ?></div>
                                            <?php if ($totalVatValue->val() > 0) : ?>
                                                <div class="uk-text-small uk-text-muted"><?= Text::_('COM_HYPERPC_INCLUDES_VAT') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="jsCartTotalPrice uk-text-emphasis">
                                                <?= $totalValue->html() ?>
                                            </div>
                                            <?php if ($totalVatValue->val() > 0) : ?>
                                                <div class="jsCartTotalVat uk-text-small uk-text-muted">
                                                    <?= $totalVatValue->html() ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
                echo $this->form->getInput('total');
                echo $this->form->getInput('form');
                echo $this->form->getInput('delivery_type');
                echo $this->form->getInput('payment_type');
                echo $this->form->getInput('id');
                ?>
            </div>
            <?php
            echo $this->form->getInput('cid');
            ?>

            <input type="hidden" name="task" value="order.save" />
            <input type="hidden" name="jform[id]" value="0" />
            <input type="hidden" name="jform[context]" value="<?= $this->hyper->getContext() ?>" />

            <div class="jsFormToken" hidden><?= HTMLHelper::_('form.token') ?></div>
        </form>
    <?php endif; ?>

    <div class="jsCartIsEmpty uk-container uk-container-small"<?= $countItems ? ' hidden' : '' ?>>
        <div class="uk-alert uk-alert-warning">
            <?= Text::_('COM_HYPERPC_BASKET_IS_EMPTY') ?>
        </div>
    </div>

</div>
