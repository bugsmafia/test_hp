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
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\StoreHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var string            $price
 * @var RenderHelper      $this
 * @var PartMarker        $part
 * @var OptionMarker|null $option
 */

/** @var CartHelper $cartHelper */
$cartHelper    = $this->hyper['helper']['cart'];
/** @var StoreHelper $storeHelper */
$storeHelper = $this->hyper['helper']['store'];

$entity = $part;
if (isset($option) && $option instanceof OptionMarker && $option->id) {
    $entity = $option;
}

$availability = $entity->getAvailability();

$availabilityTextClass = '';
switch ($availability) {
    case Stockable::AVAILABILITY_INSTOCK:
        $availabilityTextClass = ' uk-text-success';
        break;
    case Stockable::AVAILABILITY_PREORDER:
    case Stockable::AVAILABILITY_OUTOFSTOCK:
        $availabilityTextClass = ' uk-text-warning';
        break;
    case Stockable::AVAILABILITY_DISCONTINUED:
        $availabilityTextClass = ' uk-text-danger';
        break;
}

$pickingDates = $entity->getPickingDates();

$minPickingDate        = $cartHelper->getMinPickingDate($pickingDates);
$pickupFromTheStoreStr = $storeHelper->getPickupFromTheStoreStr($minPickingDate);
?>
<div class="hp-part-head__conditions uk-margin-bottom">

    <ul class="uk-list uk-margin uk-width-xlarge">
        <li class="uk-margin-bottom">
            <div class="uk-grid uk-grid-small uk-flex-middle">
                <div class="uk-flex-none">
                    <span uk-icon="icon: home; ratio: 1.75" class="uk-icon"></span>
                </div>
                <div class="uk-width-expand">
                    <div class="uk-grid uk-grid-small uk-flex-between uk-flex-middle">
                        <div>
                            <div class="jsPickupFromTheStoreStr tm-text-medium uk-text-emphasis" style="line-height: 1.2">
                                <?= $pickupFromTheStoreStr ?>
                            </div>
                            <div>
                                <a href="#" class="uk-link-muted jsDetailToggle jsShowMore"
                                   uk-toggle="target: .jsShowPickupStoresToggled; animation: uk-animation-fade;"
                                   toggled-text="<?= Text::_('COM_HYPERPC_HIDE_STORES') ?>"
                                   toggled-icon="triangle-up"> <!-- TODO toggled icon on right side -->
                                    <?= Text::_('COM_HYPERPC_CHOOSE_THE_STORE') ?><span uk-icon="triangle-down" style="margin-inline-start: -2px" class="uk-icon"></span>
                                </a>
                            </div>
                        </div>
                        <div>
                            <span class="jsShowPickupStoresToggled jsAvailabilityLabel uk-text-nowrap<?= $availabilityTextClass ?>">
                                <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . strtoupper($availability)) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="jsShowPickupStoresToggled uk-margin-small-top" hidden style="margin-inline-start: 50px">
                <?= $this->hyper['helper']['render']->render('common/full/pickup-stores', [
                    'entity'       => $entity,
                    'pickingDates' => $pickingDates
                ]); ?>
            </div>
        </li>
        <li>
            <div class="uk-flex">

                <div class="uk-width-expand" style="min-height: 180px;">
                    <?= $this->hyper['helper']['render']->render('common/full/delivery', [
                        'entity'     => $entity,
                        'parcelInfo' => $part->getDimensions()
                    ]); ?>
                </div>
            </div>
        </li>
    </ul>
</div>
