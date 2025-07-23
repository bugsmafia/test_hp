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
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * @var         RenderHelper $this
 * @var         Order[]      $orders
 */

$formAction = $this->hyper['route']->build(['view' => 'profile_orders']);
?>

<table class="uk-table uk-table-striped uk-table-hover uk-table-middle uk-table-responsive">
    <thead>
    <tr>
        <th width="1%" class="uk-text-center" hidden>
            <input class="uk-checkbox" type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)"/>
        </th>
        <th>
            <?= Text::_('COM_HYPERPC_ORDER_NUMBER_TABLE_HEAD') ?>
        </th>
        <th>
            <?= Text::_('COM_HYPERPC_ORDER_CREATION_DATE') ?>
        </th>
        <th>
            <?= Text::_('COM_HYPERPC_ORDER_PRICE') ?>
        </th>
        <th>
            <?= Text::_('COM_HYPERPC_ORDER_STATUS') ?>
        </th>
        <th>
            <?= Text::_('COM_HYPERPC_ACTIONS') ?>
        </th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order) :
            $status = $order->getStatus();

            $statusIsFinal = $order->isSold() || $order->isCancelled();

            $showCreditAdditions =
                $order->isCredit() &&
                !$order->isLoanApproved() &&
                !($order->isSold() || $order->isCancelled()) &&
                !$order->hasUpgradeAccessories() &&
                $availableRequests = $order->availableRequestCount();

            $showCreditRequestButton = false;
            $statusExtraText = [];
            if ($showCreditAdditions) {
                $sentRequests = $order->sentRequestCount();

                $showCreditRequestButton = ($availableRequests - $sentRequests) > 0;

                if ($availableRequests > 0) {
                    if ($sentRequests === 0) {
                        $statusExtraText['class'] = 'uk-text-warning';
                        $statusExtraText['text']  = Text::_('COM_HYPERPC_CREDIT_REQUEST_NEED_TO_SEND');
                    } elseif ($sentRequests >= $availableRequests) {
                        $statusExtraText['class'] = 'uk-text-success';
                        $statusExtraText['text']  = Text::_('COM_HYPERPC_CREDIT_REQUEST_COMPLETE_ALL');
                    } elseif ($sentRequests < $availableRequests) {
                        $statusExtraText['class'] = 'uk-text-warning';
                        $statusExtraText['text']  = Text::_('COM_HYPERPC_CREDIT_REQUEST_SERVICES_AVAILABLE');
                    }
                }
            }

            $orderUrl = $this->hyper['route']->build([
                'id'   => $order->id,
                'view' => 'profile_order'
            ]);
            ?>
            <tr>
                <td class="uk-text-emphasis">
                    <a href="<?= $orderUrl ?>" class="uk-link-reset">
                        #<?= $order->getName() ?>
                    </a>
                </td>
                <td class="uk-text-muted">
                    <?= HTMLHelper::date($order->created_time, Text::_('DATE_FORMAT_LC5')); ?>
                </td>
                <td class="uk-text-nowrap">
                    <?= $order->getTotal()->html() ?>
                </td>
                <td class="uk-text-small uk-text-muted">
                    <?php
                    $statusColor = (is_object($status->params)) ? $status->params->get('color', 'transparent') : 'transparent';
                    $badgeAttrs  = [
                        'class' => 'hp-status-badge',
                        'style' => 'background: ' . $statusColor
                    ];
                    ?>
                    <span <?= $this->hyper['helper']['html']->buildAttrs($badgeAttrs) ?>></span>
                    <span>
                        <?= $status->name ?>
                    </span>
                    <?php if (!empty($statusExtraText)) : ?>
                        <span class="<?= $statusExtraText['class'] ?> uk-text-small uk-display-block">
                            <?= $statusExtraText['text'] ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <ul class="uk-nav uk-nav-default">
                        <li>
                            <a href="<?= $orderUrl ?>" style="text-decoration:underline" title="#<?= $order->getName() ?>">
                                <?= Text::_('COM_HYPERPC_ORDER_DETAILS') ?>
                            </a>
                        </li>

                        <?php if ($showCreditRequestButton) : ?>
                            <li>
                                <a href="<?= $orderUrl ?>" class="uk-display-block" style="text-decoration:underline">
                                    <?= Text::_('COM_HYPERPC_CART_CREDIT_REQUIEST') ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php $reviewItem = $order->getReviewProduct();
                        if ($reviewItem) :
                            $this->hyper['helper']['assets']
                                ->jqueryRaty()
                                ->js('js:widget/site/review.js')
                                ->widget('body', 'HyperPC.SiteReview', []);

                            $product = $reviewItem;
                            $modalId = uniqid('hp-modal-');

                            echo $this->hyper['helper']['uikit']->modal(
                                $modalId,
                                $this->hyper['helper']['render']->render('reviews/default/form', [
                                'item'  => $product,
                                'order' => $order
                                ])
                            );
                            ?>
                            <li>
                                <a class="uk-display-block" style="text-decoration:underline" uk-toggle="target: #<?= $modalId ?>">
                                <?= Text::_('COM_HYPERPC_REVIEW_LEAVE_A_REVIEW') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <?php if (isset($pagination)) : ?>
        <tfoot>
            <tr>
                <td colspan="8">
                    <?= $pagination->getListFooter() ?>
                </td>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>
