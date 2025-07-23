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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewProfile_Order $this
 */

$items       = $this->order->getItems();
$countItems  = count($items['products']) + count($items['parts']) + count($items['positions']);
$createdTime = HTMLHelper::date($this->order->created_time, Text::_('DATE_FORMAT_LC5'));
?>
<div class="hp-order uk-section uk-section-small">
    <div class="uk-container">
        <div class="uk-grid uk-grid-divider" uk-grid>
            <div class="uk-width-auto uk-visible@m">
                <?= $this->hyper['helper']['render']->render('account/right_menu') ?>
            </div>
            <div class="uk-width-expand">

                <h1 class="uk-h2 uk-margin-remove-bottom">
                    <?php
                    if ($this->order->isCredit() && !$this->order->hasUpgradeAccessories()) {
                        echo Text::sprintf('COM_HYPERPC_CREDIT_NUMBER', $this->order->getName());
                    } else {
                        echo Text::sprintf('COM_HYPERPC_ORDER_NUMBER', $this->order->getName());
                    }
                    ?>
                </h1>
                <div class="uk-margin-medium-bottom">
                    <?= Text::sprintf('COM_HYPERPC_ORDER_DATE', $createdTime) ?>
                </div>

                <?= $this->order->getRender()->statusHistory('status_history') ?>

                <?= $this->hyper['helper']['render']->render('order/info_card', [
                    'order'             => $this->order,
                    'showCurrentStatus' => false
                ]); ?>

                <?php
                $sellStatus   = $this->order->isSold();
                $cancelStatus = $this->order->isCancelled();

                if ($this->order->isCredit() && !$sellStatus && !$cancelStatus && !$this->order->hasUpgradeAccessories()) {
                    echo $this->hyper['helper']['render']->render('order/credit/send_btn', [
                        'order' => $this->order
                    ]);
                }
                ?>

                <?php if ($countItems) : ?>
                    <div class="uk-margin uk-margin-medium-top">
                        <h2 class="uk-h3 uk-margin-small-bottom"><?= Text::_('COM_HYPERPC_ORDER_ITEMS') ?></h2>

                        <?= $this->hyper['helper']['render']->render('order/items_table', [
                            'order'          => $this->order,
                            'items'          => $items,
                            'groups'         => $this->groups,
                            'productFolders' => $this->productFolders
                        ]); ?>

                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
