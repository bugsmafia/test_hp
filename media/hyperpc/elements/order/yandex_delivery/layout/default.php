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
 * @var         \ElementOrderYandexDelivery $this
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\DateHelper;

/** @var DateHelper $dateHelper */
$dateHelper = $this->hyper['helper']['date'];

$defaultMethod = $this->getConfig('default_method', 'pickup');
$showDeliveryOptions = $this->getConfig('show_delivery_options', 1);

$pickupInput = [
    'value'   => '0',
    'type'    => 'radio',
    'class'   => 'uk-radio',
    'name'    => $this->getControlName('need_shipping'),
    'checked' => $defaultMethod === 'pickup' ? 'checked' : ''
];

$shippingInput = [
    'value'   => '1',
    'type'    => 'radio',
    'class'   => 'uk-radio',
    'name'    => $this->getControlName('need_shipping'),
    'checked' => $defaultMethod === 'shipping' ? 'checked' : ''
];

$addressInput = [
    'type'        => 'text',
    'class'       => 'jsAddressInput uk-input uk-form-large',
    'name'        => $this->getControlName('user_address_input'),
    'required'    => 'required'
];

$orderPickingDates = $this->getOrderPickingDates();
?>

<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin-medium">

    <div class="uk-h4 uk-margin-small uk-hidden">
        <?= $this->getConfig('name') ?>
    </div>

    <ul class="uk-tab jsToggleDeliveryMethod" uk-tab="swiping: false; active:<?= $defaultMethod === 'shipping' ? '0' : '1' ?>">
        <li>
            <a class="uk-position-relative">
                <input <?= $this->hyper['helper']['html']->buildAttrs($shippingInput) ?>>
                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHIPPING') ?>
                <span class="uk-position-cover"></span>
            </a>
        </li>
        <li>
            <a class="uk-position-relative">
                <input <?= $this->hyper['helper']['html']->buildAttrs($pickupInput) ?>>
                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP') ?>
                <span class="uk-position-cover"></span>
            </a>
        </li>
    </ul>

    <ul class="uk-switcher uk-margin">
        <li>
            <?php
            $sendingDates = $orderPickingDates->find('shippingReady.raw', '');
            $minSendingDate = '';
            $maxSendingDate = '';
            if (!empty($sendingDates)) {
                $sendingDates = explode(' - ', $sendingDates);
                $minSendingDate = $sendingDates[0];
                if (count($sendingDates) === 2) {
                    $maxSendingDate = $sendingDates[1];
                } else {
                    $maxSendingDate = $minSendingDate;
                }
            }
            ?>
            <?php if ($showDeliveryOptions) : ?>
                <div class="jsGeoCity">
                    <span class="uk-text-emphasis">
                        <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_DELIVERY_CITY') ?>
                        <span id="jsCartCityDropToggle" class="jsCityLabel uk-link tm-text-medium"></span>
                    </span>

                    <?= $this->hyper['helper']['render']->render('common/geo/location_drop', [
                        'toggleSelector' => '#jsCartCityDropToggle',
                        'offset'         => 15
                    ]) ?>
                </div>

                <div class="jsEstimatedSending uk-width-medium" hidden data-today="<?= $dateHelper->getCurrentDateTime()->format(...$dateHelper::INTERNAL_FORMAT_ARGS) ?>">
                    <span class="uk-text-muted">
                        <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_ESTIMATED_SENDING') ?>:
                    </span>
                    <span class="jsEstimatedSendingDates uk-text-nowrap">
                        <?= $orderPickingDates->find('shippingReady.value', '') ?>
                    </span>
                    <?php // TODO estimated dates info ?>
                    <span class="jsEstimatedSendingInfo uk-text-muted uk-text-small tm-text-italic" style="border-bottom: 1px dashed; cursor: help" hidden>
                        <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_WHY_SO_LONG') ?>
                    </span>
                </div>

                <div class="jsCartDelivery uk-margin-small-top">
                    <div class="jsDeliverySpinner uk-margin-small-top">
                        <div class="uk-margin-small-right uk-spinner uk-icon" uk-spinner></div>
                        <span class="uk-text-small uk-text-muted tm-text-italic"><?= Text::_('COM_HYPERPC_DELIVERY_LOAD') ?></span>
                    </div>
                    <div class="jsDeliveryUnavailableMsg" hidden>
                        <em class="uk-text-muted uk-text-small">
                            <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_UNAVAILABLE_TEXT') ?>
                        </em>
                    </div>
                </div>

                <table class="jsCartDeliveryOptions hp-cart-delivery-options-list uk-table uk-table-small uk-table-striped uk-table-hover uk-table-middle"></table>

                <ul class="jsTimeline uk-margin tm-list-timeline">
                    <?php
                        $orderPlaceDate = $dateHelper->getUserDateTime()->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true);

                        $orderSendingDates = $dateHelper->datesRangeToString(
                            $dateHelper->parseString($orderPickingDates->find('shippingReady.raw', ''), true)
                        );
                    ?>
                    <li class="jsTimelinePlace"<?= $orderPlaceDate === $orderSendingDates ? ' hidden' : '' ?>>
                        <span class="tm-list-timeline__icon" uk-icon="cart"></span>
                        <div><?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_TIMELINE_DELIVERY_COMPLETED') ?></div>
                        <div class="jsTimelineDate uk-text-meta">
                            <?= $orderPlaceDate ?>
                        </div>
                    </li>
                    <li class="jsTimelineSending">
                        <span class="tm-list-timeline__icon" uk-icon="hp-packaging"></span>
                        <div><?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_TIMELINE_PLACE_AN_ORDER') ?></div>
                        <div class="jsTimelineDate uk-text-meta">
                            <?= $orderSendingDates ?>
                        </div>
                    </li>
                    <li class="jsTimelineReady">
                        <span class="tm-list-timeline__icon" uk-icon="check"></span>
                        <div><?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_TIMELINE_SENDING') ?></div>
                        <div class="jsTimelineDate uk-text-meta"></div>
                    </li>
                </ul>

                <input name="<?= $this->getControlName('days_min') ?>" type="hidden" value="">
                <input name="<?= $this->getControlName('days_max') ?>" type="hidden" value="">

            <?php endif; ?>

            <input name="<?= $this->getControlName('sending_date_min') ?>" type="hidden" value="<?= $minSendingDate ?>"<?= $defaultMethod === 'pickup' ? ' disabled' : '' ?>>
            <input name="<?= $this->getControlName('sending_date_max') ?>" type="hidden" value="<?= $maxSendingDate ?>"<?= $defaultMethod === 'pickup' ? ' disabled' : '' ?>>

            <input name="<?= $this->getControlName('shipping_cost') ?>" type="hidden" value="-1"<?= $defaultMethod === 'pickup' ? ' disabled' : '' ?>>

            <div class="tm-label-infield uk-margin">
                <label class="uk-form-label tm-label-required" for="hp-input-address">
                    <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_ADDRESS_LABEL') ?>
                </label>
                <div class="uk-form-control">
                    <input <?= $this->hyper['helper']['html']->buildAttrs($addressInput) ?>>
                </div>
            </div>

            <input name="<?= $this->getControlName('original_address') ?>" type="hidden" value="">
            <input name="<?= $this->getControlName('fias_id') ?>" type="hidden" value="">

            <?php if ($showDeliveryOptions) : ?>
                <input name="<?= $this->getControlName('pickup_point_address') ?>" type="hidden" value="">

                <div class="jsPickupPointWrapper uk-flex uk-card uk-card-small uk-card-body tm-background-gray-5" hidden>
                    <span class="uk-flex-none uk-margin-small-right uk-icon" style="padding-top: 5px;" uk-icon="location"></span>
                    <div class="uk-width-expand">
                        <div>
                            <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP_POINT_ADDRESS') ?>:
                        </div>
                        <div class="jsPickupPoint tm-text-medium uk-text-emphasis"></div>
                        <div>
                            <a target="_blank" rel="noopener" class="jsPickupPointMapLink">
                                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHOW_ON_MAP') ?>
                            </a>
                        </div>

                        <div class="jsChoosePickupPoint uk-margin-top" hidden>
                            <a href="#pickup-points" class="uk-link-muted" uk-toggle>
                                <u>
                                    <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_CHOOSE_ANOTHER_PICKUP_POINT') ?>
                                </u>
                            </a>
                            <span class="jsPickupPointsCount uk-text-muted"></span>
                        </div>

                        <div id="pickup-points" class="uk-modal-container" uk-modal>
                            <div class="uk-modal-dialog">
                                <button class="uk-modal-close-full uk-close-large" type="button" uk-close></button>
                                <div class="uk-modal-header uk-text-center">
                                    <div class="uk-h1">
                                        <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP_POINTS') ?>
                                    </div>
                                        <div class="jsPickupPointsFilter">
                                            <div class="uk-inline">
                                                <a class="jsPickupPointsFilterClear uk-form-icon uk-form-icon-flip"
                                                uk-icon="icon: close"
                                                title="<?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_CLEAR_FILTER') ?>"></a>
                                                <input type="text" class="uk-input uk-form-large uk-width-xlarge"
                                                    placeholder="<?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_INPUT_PART_OF_THE_ADDRESS') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                <div class="uk-modal-body" uk-overflow-auto>
                                    <table class="hp-cart-pickup-points-list uk-table uk-table-hover uk-table-divider uk-table-middle">
                                        <tbody class="jsPickupPointsList"></tbody>
                                        <tfoot class="jsPickupPointsListFooter" hidden>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="uk-text-muted tm-text-italic uk-text-center">
                                                        <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_NO_MATCHES_FOUND') ?>
                                                        <a class="jsPickupPointsFilterClear uk-link-muted">
                                                            <u>
                                                                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_CLEAR_FILTER') ?>
                                                            </u>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </li>
        <li>
            <?php
                $stores = $this->hyper['helper']['store']->findAll();
                $checkedStoreId = $defaultMethod === 'shipping' ? null : array_key_first($stores);
            ?>
            <?php if (count($stores)) : ?>
                <div class="tm-text-medium uk-text-emphasis">
                    <span class="uk-flex-none uk-margin-small-right uk-icon uk-text-top" uk-icon="home" style="padding-top: 2px;"></span><?= Text::_('COM_HYPERPC_FROM_THE_STORE') ?>
                </div>
                <table class="jsStoresList hp-cart-delivery-options-list uk-table uk-table-small uk-table-striped uk-table-hover uk-table-middle">
                    <?php foreach ($stores as $id => $store) :
                        $storeName = trim($store->getParam('city', $store->name));
                        $storeName = empty($storeName) ? $store->name : $storeName;

                        $storeAddress = $store->getParam('address', $store->name);
                        $storeSchedule = $store->getParam('schedule_string', '');
                        $storeAddress .= !empty($storeSchedule) ? ', ' : '';

                        $storeMap = $store->params->get('map_link', '');

                        $pickingDateRaw = $orderPickingDates->find('stores.' . $id . '.pickup.raw', '');
                        $pickingDateString = $orderPickingDates->find('stores.' . $id . '.pickup.value', '');
                        $availableForPickup = $orderPickingDates->find('stores.' . $id . '.availableNow', '');
                        ?>
                        <tr class="jsStoresListOption" data-storeid="<?= $id ?>"
                            data-available="<?= $availableForPickup ? 'true' : 'false' ?>">
                            <td class="uk-table-shrink uk-padding-remove-right">
                                <input type="radio" name="<?= $this->getControlName('store') ?>"
                                       value="<?= $id ?>" class="uk-radio"<?= $id === $checkedStoreId ? 'checked="checked"' : '' ?>>
                            </td>
                            <td>
                                <div class="uk-grid uk-grid-small uk-flex-nowrap">
                                    <div class="uk-flex-none" style="margin-top: 7px">
                                        <?php if (!empty($storeMap)) : ?>
                                            <a href="<?= $storeMap ?>" target="_blank" class="jsLoadIframe">
                                                <img src="/media/hyperpc/img/other/map-teaser-50x57.png" class="uk-border-rounded" alt="map icon">
                                            </a>
                                        <?php else : ?>
                                            <img src="/media/hyperpc/img/other/map-teaser-50x57.png" class="uk-border-rounded" alt="map icon">
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="tm-text-medium uk-text-emphasis">
                                            <?= $storeName ?>
                                        </div>
                                        <div class="uk-text-muted" style="line-height: 1.2">
                                            <?= $storeAddress ?>
                                            <?php if (!empty($storeSchedule)) : ?>
                                                <span class="uk-text-nowrap">
                                                    <?= $storeSchedule ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="jsConditionsDeliveryPickingDate" data-raw="<?= $pickingDateRaw ?>">
                                            <?= $pickingDateString ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php $storePickupDatesValue = $checkedStoreId ? $orderPickingDates->find('stores.' . $checkedStoreId . '.pickup.raw', '') : ''; ?>
                <input name="<?= $this->getControlName('store_pickup_dates') ?>" type="hidden"<?= $defaultMethod !== 'pickup' ? ' disabled' : ' value="' . $storePickupDatesValue . '"' ?>>
            <?php else : ?>
                <div class="uk-display-inline-block">
                    <div class="uk-flex">
                        <span class="uk-flex-none uk-margin-small-right uk-icon" style="padding-top: 5px;" uk-icon="location"></span>
                        <div class="uk-width-expand">
                            <div>
                            <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_HYPERPC_ADDRESS_HEADING') ?>:
                            </div>
                            <div class="tm-text-medium uk-text-emphasis">
                                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_HYPERPC_ADDRESS') ?>
                            </div>
                            <div class="uk-text-small uk-text-muted">
                                (<?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PARKING_INFO') ?>)
                            </div>
                            <div uk-lightbox>
                                <a href="https://yandex.ru/map-widget/v1/-/CBF3YYsGHA" target="_blank" rel="noopener" data-caption="<?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHOP_HYPERPC') ?>" data-type="iframe">
                                    <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHOW_ON_MAP') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <hr class="uk-margin-small">
                    <div class="uk-flex">
                        <span class="uk-flex-none uk-margin-small-right uk-icon" style="padding-top: 5px;" uk-icon="clock"></span>
                        <div class="uk-width-expand">
                            <div>
                                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_WORKING_HOURS_HEADING') ?>:
                            </div>
                            <div class="tm-text-medium uk-text-emphasis">
                                <?= Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_WORKING_HOURS') ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </li>
    </ul>

    <input name="<?= $this->getControlName('granular_address_locality') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_postal_code') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_street_type') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_street_name') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_house_type') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_house_name') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_block_type') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_block_name') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_flat_type') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('granular_address_flat_name') ?>" type="hidden" value="">

    <input name="<?= $this->getControlName('parcel_dimentions_length') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('parcel_dimentions_width') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('parcel_dimentions_height') ?>" type="hidden" value="">
    <input name="<?= $this->getControlName('parcel_weight') ?>" type="hidden" value="">

</div>
