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
 * 
 * @var         bool $showCurrentStatus
 * @var         \HYPERPC\Helper\RenderHelper $this
 * @var         \HYPERPC\Joomla\Model\Entity\Order $order
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$elements   = $order->elements;

$payment = $order->getPayment();

$delivery = $order->getDelivery();
$deliveryService = $delivery->getService();

$shippingDates = $this->hyper['helper']['order']->getOrderShippingDates($order);
$shippingDays = $this->hyper['helper']['order']->getOrderShippingDays($order);
?>

<div class="hp-order__info-card uk-card uk-card-small uk-card-body">
    <div class="uk-grid uk-grid-divider uk-grid-medium uk-child-width-1-2@s" uk-grid>
        <div>
            <ul class="uk-list uk-list-divider">
                <li>
                    <div class="uk-text-muted uk-text-uppercase uk-text-small">
                        <?= Text::_('COM_HYPERPC_ORDER_BUYER_NAME') ?>
                    </div>
                    <?= $order->getBuyer() ?>
                </li>
                <li>
                    <div class="uk-text-muted uk-text-uppercase uk-text-small">
                        <?= Text::_('COM_HYPERPC_ORDER_EMAIL') ?>
                    </div>
                    <?= $order->getBuyerEmail() ?>
                </li>
                <li>
                    <div class="uk-text-muted uk-text-uppercase uk-text-small">
                        <?= Text::_('COM_HYPERPC_ORDER_PHONE') ?>
                    </div>
                    <span dir="ltr"><?= $order->getBuyerPhone() ?></span>
                </li>
            </ul>
        </div>
        <div>
            <ul class="uk-list uk-list-divider">
                <?php if (!empty($deliveryService)) : ?>
                    <li>
                        <div class="uk-text-muted uk-text-uppercase uk-text-small">
                            <?= Text::_('COM_HYPERPC_ORDER_SHIPPING_METHOD') ?>
                        </div>
                        <?= $deliveryService ?>
                        <?php if (!empty($shippingDays)) : ?>
                            (<?= $shippingDays ?>)
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
                <?php if ($delivery->isShipping()) : ?>
                    <li>
                        <div class="uk-text-muted uk-text-uppercase uk-text-small">
                            <?= Text::_('COM_HYPERPC_ORDER_SHIPPING_ADDRESS') ?>
                        </div>
                        <?= $this->hyper['helper']['order']->getDeliveryAddreess($order) ?>
                    </li>
                    <?php if (!empty($shippingDates->find('sending.dates'))) : ?>
                        <li>
                            <div class="uk-text-muted uk-text-uppercase uk-text-small">
                                <?= Text::_('COM_HYPERPC_ORDER_ESTIMATED_SENDING') ?>
                            </div>
                            <?= $shippingDates->find('sending.dates') ?>
                        </li>
                    <?php endif; ?>
                <?php else :
                    $storeId = $delivery->getStoreId();
                    ?>
                    <li>
                        <div class="uk-text-muted uk-text-uppercase uk-text-small">
                            <?= Text::_('COM_HYPERPC_ORDER_SHOP_ADDRESS') ?>
                        </div>
                        <?php if ($storeId) : ?>
                            <?= $this->hyper['helper']['store']->getAddress($storeId) ?>
                        <?php else : ?>
                            <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_HYPERPC_ADDRESS') ?>
                        <?php endif; ?>
                    </li>
                    <?php $pickingDate = $delivery->getPickingDateString() ?>
                    <?php if (!empty($pickingDate)) : ?>
                        <li>
                            <div class="uk-text-muted uk-text-uppercase uk-text-small">
                                <?= Text::_('COM_HYPERPC_ORDER_PICKUP_FROM_STORE_DATE') ?>
                            </div>
                            <?= $pickingDate ?>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($payment) : ?>
                    <li>
                        <div class="uk-text-muted uk-text-uppercase uk-text-small">
                            <?= Text::_('COM_HYPERPC_ORDER_PAYMENT_METHOD') ?>
                        </div>
                        <?= $payment->getMethodName() ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php if (!empty($elements->find('comment.value', '', 'strip'))) : ?>
        <hr>
        <div>
            <div class="uk-text-muted uk-text-uppercase uk-text-small">
                <?= Text::_('COM_HYPERPC_ORDER_COMMENT') ?>
            </div>
            <?= $elements->find('comment.value', '', 'strip') ?>
        </div>
    <?php endif; ?>

</div>
