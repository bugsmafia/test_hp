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
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper $this
 * @var Stockable $entity
 * @var MeasurementsData $parcelInfo
 */

$itemType = 'product';

if (!($entity instanceof ProductMarker)) {
    $part = $entity;
    if ($entity instanceof OptionMarker) {
        $part = $entity->getPart();
    }

    /** @var PartMarker $part */
    $itemType = $part->getType();
}
?>

<div class="jsGeoCity">
    <span id="jsCardCityDropToggle" class="uk-link">
        <span class="uk-icon" style="margin-inline-end: 2px">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="15" height="15">
                <path d="M1 11l9 3 3 9 9-21z" fill="#ccc" fill-rule="evenodd"></path>
            </svg>
        </span>
        <span class="jsCityLabel tm-text-medium uk-text-bold"></span>
    </span>

    <hr class="uk-margin-small">

    <div class="uk-margin-small-top jsGeoDelivery"
         data-itemtype="<?= $itemType ?>"
         data-dimensions='<?= json_encode($parcelInfo->dimensions->toArray()) ?>'
         data-weight="<?= $parcelInfo->weight ?>"
         data-today='<?= $this->hyper['helper']['date']->getCurrentDateTime()->format('M d Y H:i', true, false) ?>'
    >
        <div class="jsDeliverySpinner uk-margin-small-top">
            <div class="uk-margin-small-right uk-spinner uk-icon" uk-spinner></div>
            <span class="uk-text-small uk-text-muted tm-text-italic"><?= Text::_('COM_HYPERPC_DELIVERY_LOAD') ?></span>
        </div>

        <div class="jsDeliveryUnavailableMsg" hidden>
            <em class="uk-text-muted uk-text-small">
                <?= Text::_('COM_HYPERPC_DELIVERY_UNAVAILABLE_TEXT') ?>
            </em>
        </div>
        <ul class="jsDeliveryOptions uk-list uk-list-divider uk-margin-remove-top">
            <!-- TODO show all delivery options
            <li class="uk-text-small">
                <a href="#" class="uk-link-muted"><?= Text::_('COM_HYPERPC_DETAILS') ?><span uk-icon="triangle-down" style="margin-inline-start: -2px"></span></a>
            </li>
             -->
        </ul>
    </div>

    <?= $this->hyper['helper']['render']->render('common/geo/location_drop', [
        'toggleSelector' => '#jsCardCityDropToggle',
        'offset'         => 15
    ]) ?>

</div>
