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
 * @author      Artem Vyshnevskiy
 *
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>

<div class="uk-display-inline-block" aria-expanded="true">
    <div class="uk-flex">
        <span class="uk-flex-none uk-margin-small-right uk-icon" style="padding-top: 5px;" uk-icon="location"></span>
        <div class="uk-width-expand">
            <div>
                <?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_HYPERPC_ADDRESS_HEADING') ?>:
            </div>
            <div class="tm-text-medium uk-text-emphasis">
                <?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_HYPERPC_ADDRESS') ?>
            </div>
            <div class="uk-text-small uk-text-muted">
                <?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_PARKING_INFO') ?>
            </div>
            <div uk-lightbox="">
                <a href="https://yandex.ru/map-widget/v1/-/CBF3YYsGHA" target="_blank" rel="noopener" data-caption="<?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_SHOP_HYPERPC') ?>" data-type="iframe">
                    <?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_SHOW_ON_MAP') ?>
                </a>
            </div>
        </div>
    </div>
    <hr class="uk-margin-small">
    <div class="uk-flex">
        <span class="uk-flex-none uk-margin-small-right uk-icon" style="padding-top: 5px;" uk-icon="clock"></span>
        <div class="uk-width-expand">
            <div>
                <?= Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_WORKING_HOURS_HEADING') ?>:
            </div>
            <div class="tm-text-medium uk-text-emphasis">
                <?= $this->hyper['params']->get('schedule_string', Text::_('COM_HYPERPC_ORDER_PICKUP_STORE_WORKING_HOURS')) ?>
            </div>
        </div>
    </div>
</div>
