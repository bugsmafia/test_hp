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

use JBZoo\Utils\Str;
use Cake\Utility\Inflector;
use Joomla\CMS\Language\Text;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Object\Order\PositionDataCollection;

/**
 * @var         array $grous
 * @var         array $items
 * @var         RenderHelper $this
 * @var         Order $order
 */


$isMobile = $this->hyper['detect']->isMobile();

$delivery      = $order->getDelivery();
$deliveryPrice = $delivery->getPrice();

$totalPrice = $order->getTotal();

$enterPromoCode = $this->hyper['helper']['promocode']->getSessionData();

$order->set('discount', new Money());

$vat = $this->hyper['helper']['money']->getVat($order->total);
?>

<?php if ($isMobile) : ?>
    <ul class="uk-list uk-list-divider">
        <?php
        foreach ((array) $items as $type => $typeItems) : ?>
            <?php if (count($typeItems)) : ?>
                <?php if ($type === 'positions') :
                    $positionDataCollection = PositionDataCollection::create((array) $order->positions);
                    ?>
                    <?php foreach ($positionDataCollection as $itemKey => $positionData) :
                        if (preg_match('/position-\d+-product-\d+-\d+/', $itemKey)) {
                            continue; // Skip services related to product configuration
                        }

                        $item = $typeItems[$itemKey] ?? null;
                        ?>
                        <li>
                            <?php
                            echo $this->hyper['helper']['render']->render(
                                'order/tmpl/default_position',
                                [
                                    'order'          => $order,
                                    'itemKey'        => $itemKey,
                                    'item'           => $item,
                                    'items'          => $typeItems,
                                    'groups'         => $productFolders,
                                    'positionData'   => $positionData,
                                    'positionsData'  => $positionDataCollection,
                                    'enterPromoCode' => $enterPromoCode,
                                ],
                                'views'
                            );
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach ($typeItems as $itemKey => $item) : ?>
                        <li>
                            <?php
                            echo $this->hyper['helper']['render']->render(
                                'order/tmpl/default_' . Inflector::singularize(Str::low($type)),
                                [
                                    'order'          => $order,
                                    'itemKey'        => $itemKey,
                                    'item'           => $item,
                                    'enterPromoCode' => $enterPromoCode
                                ],
                                'views'
                            );
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <div class="uk-text-right tm-border-top uk-margin-bottom tm-text-medium">
        <?php if ($vat->val() > 0 || ($delivery->isShipping() && $deliveryPrice->val() >= 0)) : ?>
            <div class="uk-text-muted">
                <div>
                    <?= Text::_('COM_HYPERPC_ORDER_TOTAL') ?>:
                    <span class="uk-display-inline-block" style="min-width: 95px"><?= $order->total->text() ?></span>
                </div>

                <?php if ($vat->val() > 0) : ?>
                    <div>
                        <?= Text::_('COM_HYPERPC_INCLUDES_VAT') ?>
                        <span class="uk-display-inline-block" style="min-width: 95px">
                            <?= $vat->text() ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($delivery->isShipping() && $deliveryPrice->val() >= 0) : ?>
                    <div>
                        <?= Text::_('COM_HYPERPC_ORDER_DELIVERY_PRICE') ?>:
                        <span class="uk-display-inline-block" style="min-width: 95px">
                            <?php if (intval($deliveryPrice->val()) === 0) : ?>
                                <?= Text::_('COM_HYPERPC_FOR_FREE') ?>
                            <?php else : ?>
                                <?= $deliveryPrice->text() ?>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <hr class="uk-margin-small">
        <?php endif; ?>

        <div class="uk-text-emphasis">
            <?= Text::sprintf('COM_HYPERPC_TOTAL') ?>:
            <span class="uk-display-inline-block" style="min-width: 95px">
                <?= $totalPrice->text() ?>

                <?php if ($delivery->isShipping() && $deliveryPrice->val() < 0) : ?>
                    <span class="uk-text-lowercase">
                        + <?= Text::_('COM_HYPERPC_DELIVERY') ?>
                    </span>
                <?php endif; ?>
            </span>
        </div>

        <?php if ($order->discount->val() > 0) : ?>
            <div class="uk-text-muted">
                <?= Text::_('COM_HYPERPC_YOUR_DISCOUNT_IS') ?>:
                <span class="uk-display-inline-block" style="min-width: 95px">
                    <?= $order->discount->text() ?>
                </span>
            </div>
        <?php endif; ?>

    </div>
<?php else : ?>
    <table class="hp-cart-table uk-table uk-table-divider uk-table-middle uk-margin-small-top">
        <thead>
            <tr>
                <th><?= Text::_('COM_HYPERPC_BASKET_PRODUCT') ?></th>
                <th width="50%"></th>
                <th><?= Text::_('COM_HYPERPC_BASKET_PRODUCT_PRICE') ?></th>
                <th><?= Text::_('COM_HYPERPC_BASKET_PRODUCT_COUNT') ?></th>
                <th class="uk-width-small"><?= Text::_('COM_HYPERPC_BASKET_PRODUCT_TOTAL_PRICE') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ((array) $items as $type => $typeItems) : ?>
                <?php if (count($typeItems)) : ?>
                    <?php if ($type === 'positions') :
                        $positionDataCollection = PositionDataCollection::create((array) $order->positions);
                        ?>
                        <?php foreach ($positionDataCollection as $itemKey => $positionData) :
                            if (preg_match('/position-\d+-product-\d+-\d+/', $itemKey)) {
                                continue; // Skip services related to product configuration
                            }

                            $item = $typeItems[$itemKey] ?? null;
                            ?>
                            <tr>
                                <?php
                                echo $this->hyper['helper']['render']->render(
                                    'order/tmpl/default_position',
                                    [
                                        'order'          => $order,
                                        'itemKey'        => $itemKey,
                                        'item'           => $item,
                                        'items'          => $typeItems,
                                        'groups'         => $productFolders,
                                        'positionData'   => $positionData,
                                        'positionsData'  => $positionDataCollection,
                                        'enterPromoCode' => $enterPromoCode,
                                    ],
                                    'views'
                                );
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <?php foreach ($typeItems as $itemKey => $item) : ?>
                            <tr>
                                <?php
                                echo $this->hyper['helper']['render']->render(
                                    'order/tmpl/default_' . Inflector::singularize(Str::low($type)),
                                    [
                                        'order'          => $order,
                                        'itemKey'        => $itemKey,
                                        'item'           => $item,
                                        'groups'         => $groups,
                                        'enterPromoCode' => $enterPromoCode
                                    ],
                                    'views'
                                );
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($vat->val() > 0 || ($delivery->isShipping() && $deliveryPrice->val() >= 0)) : ?>
                <tr>
                    <td colspan="5" class="uk-text-muted uk-text-left uk-text-right@m">
                        <div>
                            <?= Text::_('COM_HYPERPC_ORDER_TOTAL') ?>
                            <span class="uk-display-inline-block uk-width-small uk-text-nowrap uk-text-left" style="padding-inline-start: 24px;">
                                <?= $order->total ?>
                            </span>
                        </div>

                        <?php if ($vat->val() > 0) : ?>
                            <div>
                                <?= Text::_('COM_HYPERPC_INCLUDES_VAT') ?>
                                <span class="uk-display-inline-block uk-width-small uk-text-nowrap uk-text-left" style="padding-inline-start: 24px;">
                                    <?= $vat->text() ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($delivery->isShipping() && $deliveryPrice->val() >= 0) : ?>
                            <div>
                                <?= Text::_('COM_HYPERPC_ORDER_DELIVERY_PRICE') ?>
                                <span class="uk-display-inline-block uk-width-small uk-text-nowrap uk-text-left" style="padding-inline-start: 24px;">
                                    <?php if (intval($deliveryPrice->val()) === 0) : ?>
                                        <?= Text::_('COM_HYPERPC_FOR_FREE') ?>
                                    <?php else : ?>
                                        <?= $deliveryPrice->text() ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endif; ?>

        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="uk-text-left uk-text-right@m">

                    <div class="uk-text-emphasis">
                        <?= Text::_('COM_HYPERPC_TOTAL') ?>
                        <span class="uk-display-inline-block uk-text-nowrap uk-text-left" style="padding-inline-start: 24px; min-width: 126px;">
                            <?= $totalPrice->text() ?>
                            <?php if ($delivery->isShipping() && $deliveryPrice->val() < 0) : ?>
                                <span class="uk-text-lowercase">
                                    + <?= Text::_('COM_HYPERPC_DELIVERY') ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if ($order->discount->val() > 0) : ?>
                        <div class="uk-text-muted">
                            <?= Text::_('COM_HYPERPC_YOUR_DISCOUNT_IS') ?>
                            <span class="uk-display-inline-block uk-width-small uk-text-nowrap uk-text-left" style="padding-inline-start: 24px;">
                                <?= $order->discount->text() ?>
                            </span>
                        </div>
                    <?php endif; ?>

                </td>
            </tr>
        </tfoot>
    </table>
<?php endif;
