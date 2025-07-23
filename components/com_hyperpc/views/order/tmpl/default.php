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
 * @var         HyperPcViewOrder $this
 */

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

$items       = $this->order->getItems();
$countItems  = count($items['products']) + count($items['parts']) + count($items['positions']);
$createdTime = HTMLHelper::date($this->order->created_time, Text::_('DATE_FORMAT_LC5'));
?>

<div class="hp-order uk-section uk-section-small">
    <div class="uk-container">
        <div class="uk-grid uk-grid-divider" uk-grid>
            <div class="uk-width-auto uk-visible@m">
                <ul class="uk-list">
                    <li>
                        <a href="<?= Route::_('index.php?option=com_users&view=profile'); ?>" class="uk-link-muted">
                            <?= Text::_('COM_HYPERPC_PROFILE') ?>
                        </a>
                    </li>
                    <li><hr></li>
                    <li>
                        <a href="<?= $this->hyper['route']->build(['view' => 'profile_orders']); ?>" class="uk-link-muted">
                            <?= Text::_('COM_HYPERPC_MY_ORDERS') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $this->hyper['route']->build(['view' => 'profile_configurations']); ?>" class="uk-link-muted">
                            <?= Text::_('COM_HYPERPC_MY_CONFIGURATIONS') ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="uk-width-expand">
                <h1 class="uk-h2 uk-margin-remove">
                    <?php
                    if ($this->order->isCredit() && !$this->order->hasUpgradeAccessories()) {
                        echo $this->hyper['helper']['order']->getCreditOrderTitle($this->order);
                    } else {
                        echo Text::sprintf('COM_HYPERPC_ORDER_NUMBER', $this->order->getName());
                    }
                    ?>
                </h1>
                <div class="uk-margin-medium-bottom">
                    <?= Text::sprintf('COM_HYPERPC_ORDER_DATE', $createdTime) ?>
                </div>

                <?php if (in_array($this->hyper->getUserIp(), (array) $this->hyper['config']->get('debug_ip'))) : ?>
                    <span class="uk-text-muted uk-text-italic">debug info:</span>
                    <div class="tm-card-bordered uk-padding-small">
                        <?= $this->order->getRender()->statusHistory('status_history') ?>

                        <?= $this->hyper['helper']['render']->render('order/info_card', [
                            'order'             => $this->order,
                            'showCurrentStatus' => true
                        ]); ?>

                        <?php
                        if ($this->order->isCredit() && !$this->order->hasUpgradeAccessories()) {
                            echo $this->hyper['helper']['render']->render('order/credit/send_btn', [
                                'order' => $this->order
                            ]);
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($countItems) : ?>
                    <div class="uk-margin uk-margin-medium-top">
                        <h2 class="uk-h3 uk-margin-remove"><?= Text::_('COM_HYPERPC_ORDER_ITEMS') ?></h2>

                        <?= $this->hyper['helper']['render']->render('order/items_table', [
                            'order'  => $this->order,
                            'items'  => $items,
                            'groups' => $this->groups,
                            'productFolders' => $this->productFolders
                        ]); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
