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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Layout\LayoutHelper;
use HYPERPC\Elements\ElementPayment;

/**
 * @var HyperPcViewOrders $this
 */

$formAction = $this->hyper['helper']['route']->url([
    'view' => 'orders'
]);
?>
<form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (is_array($this->orders) && count($this->orders)) : ?>
                    <table class="table table-striped table-hover" id="orderList">
                        <thead>
                            <tr>
                                <th width="1%" class="center">
                                    <?= HTMLHelper::_('grid.checkall') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_NUM') ?>
                                </th>
                                <th class="text-nowrap">
                                    <?= Text::_('COM_HYPERPC_ORDER_CREATION_DATE') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_ORDER_BUYER') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_ORDER_BUYER_TYPE') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_ORDER_BUYER_PHONE') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_ORDER_PRICE') ?>
                                </th>
                                <th class="text-center">
                                    <?= Text::_('COM_HYPERPC_ORDER_PROMO_CODE') ?>
                                </th>
                                <th class="text-nowrap">
                                    <?= Text::_('COM_HYPERPC_ORDER_PAYMENT_METHOD') ?>
                                </th>
                                <th>
                                    <?= Text::_('COM_HYPERPC_ORDER_DELIVERY_METHOD') ?>
                                </th>
                                <td class="text-center">MS</td>
                                <td class="text-center">AMO</td>
                                <th>
                                    <?= Text::_('COM_HYPERPC_ORDER_STATUS') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->orders as $order) :
                                $status   = $order->getStatus();
                                $payment  = $order->getPayment();
                                $delivery = $order->getDelivery();
                                ?>
                                <tr>
                                    <td class="center">
                                        <?= HTMLHelper::_('grid.id', $order->id, $order->id) ?>
                                    </td>
                                    <td class="small">
                                        <a href="<?= $order->getEditUrl() ?>" title="<?= Text::_('COM_HYPERPC_NUM') . $order->getName() ?>">
                                            <?= Text::_('COM_HYPERPC_NUM') . $order->getName() ?>
                                        </a>
                                    </td>
                                    <td class="small">
                                        <?= HTMLHelper::date($order->created_time, Text::_('DATE_FORMAT_LC5')); ?>
                                    </td>
                                    <td class="small">
                                        <?= $order->getBuyer() ?>
                                    </td>
                                    <td class="small">
                                        <?= Text::_('COM_HYPERPC_ORDER_BUYER_' . $order->getBuyerOrderType()) ?>
                                    </td>
                                    <td class="small text-nowrap">
                                        <?= $order->getBuyerPhone() ?>
                                    </td>
                                    <td class="small text-nowrap">
                                        <?= $order->getTotal()->text() ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($order->promo_code) : ?>
                                            <span class="badge bg-secondary"><?= $order->promo_code ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?php
                                        if ($payment instanceof ElementPayment) {
                                            echo $payment->getConfig('name');
                                        } else {
                                            echo Text::_('COM_HYPERPC_ORDER_PAYMENT_NOT_SETUP');
                                        }
                                        ?>
                                    </td>
                                    <td class="small">
                                        <?= $order->getDeliveryType() ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $this->hyper['helper']['html']->published($order->getUuid() ? 1 : 0) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $this->hyper['helper']['html']->published($order->getAmoLeadId()) ?>
                                    </td>
                                    <td>
                                        <?php if ($status->id) : ?>
                                            <?php
                                            $statusColor = $status->params->get('color', '#fff');
                                            $badgeAttrs  = [
                                                'class' => 'badge badge-info',
                                                'style' => 'color: rgba(0,0,0,0.5); border: 1px solid;'
                                            ];
                                            if (!empty($statusColor) && $statusColor !== '#fff' && $statusColor !== '#ffffff') {
                                                $badgeAttrs['style'] .= ' background: ' . $statusColor . ';';
                                            }
                                            ?>
                                            <span <?= ArrayHelper::toString($badgeAttrs) ?>>
                                                <?= $status->name ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="13">
                                    <?= $this->pagination->getListFooter() ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" />
    <input type="hidden" name="boxchecked" />
    <?= HTMLHelper::_('form.token'); ?>
</form>
