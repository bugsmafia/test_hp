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
use Joomla\CMS\Layout\LayoutHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use HyperPcViewProfile_Orders as View;

/**
 * @var View $this
 * @var Order $order
 */

HTMLHelper::_('behavior.core');

$formAction = $this->hyper['route']->build(['view' => 'profile_orders']);
?>
<style>
    .list-footer .limit {
        display: none;
    }
</style>

<div class="uk-container">
    <div class="uk-text-center">
        <h1 class="uk-margin-medium-bottom">
            <?= Text::_('COM_HYPERPC_MY_ORDERS') ?>
        </h1>
    </div>
    <div class="uk-grid uk-grid-divider uk-margin-bottom" uk-grid>
        <div class="uk-width-auto uk-visible@m">
            <?= $this->hyper['helper']['render']->render('account/right_menu') ?>
        </div>
        <div class="uk-width-expand">
            <?php if (empty($this->filterForm->getData()->get('filter.search')) && !count($this->orders)) : ?>
                <div class="tm-text-italic">
                    <?= Text::_('COM_HYPERPC_YOU_HAVE_NOT_ORDERED_ANYTHING_YET') ?>
                </div>
            <?php else : ?>
                <form action="<?= $formAction ?>" method="post" name="adminForm" id="adminForm">
                    <div class="uk-clearfix"><?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?></div>
                    <?= HTMLHelper::_('form.token') ?>
                </form>

                <?php if (empty($this->orders)) : ?>
                    <div class="tm-text-italic uk-margin">
                        <?= Text::_('COM_HYPERPC_NOTHING_FOUND') ?>
                    </div>
                <?php else : ?>
                    <?= $this->hyper['helper']['render']->render('user/profile/orders_list', [
                        'orders'     => $this->orders,
                        'pagination' => $this->pagination
                    ]); ?>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>
