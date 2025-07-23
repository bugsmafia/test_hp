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

use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\StoreHelper;
use Joomla\CMS\Filesystem\Path;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var RenderHelper    $this
 * @var PartMarker      $part
 * @var OptionMarker[]  $options
 * @var OptionMarker    $optionDefault
 */

/** @var CartHelper $cartHelper */
$cartHelper    = $this->hyper['helper']['cart'];
/** @var StoreHelper $storeHelper */
$storeHelper   = $this->hyper['helper']['store'];
$creditEnabled = $this->hyper['params']->get('credit_enable', '0');

$cartItems = $cartHelper->getSessionItems();
?>

<select class="uk-select hp-part-head__options jsPartOptions">

    <?php foreach ($options as $option) :
        $availability = $option->getAvailability();
        if ($availability === Stockable::AVAILABILITY_DISCONTINUED && $option->id !== $optionDefault->id) {
            continue;
        }

        $itemKey  = $option->getItemKey();
        $isInCart = array_key_exists($itemKey, $cartItems);

        $pickingDatesAttr      = '';
        $pickupFromTheStoreStr = '';

        if (in_array($availability, [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
            $_part = clone $part;
            $_part->set('option', $option);

            $itemData = [
                $itemKey => $_part
            ];

            $readyDates = $cartHelper->getOrderPickingDates($itemData, [$itemKey => ['quantity' => 1]]);

            $pickingDates     = $readyDates->get('stores', []);
            $pickingDatesAttr = json_encode($pickingDates);

            $minPickingDate        = $cartHelper->getMinPickingDate($pickingDates);
            $pickupFromTheStoreStr = $storeHelper->getPickupFromTheStoreStr($minPickingDate);
        }

        /** @todo Refactor this method after migrate on Moysklad */
        $checkRate = false;
        if ($option instanceof OptionMarker) {
            $imgSrc = $option->images->get('image', '', 'hpimagepath') ? : $part->images->get('image', '', 'hpimagepath');
        } else {
            $imgSrc    = $option->params->get('image', '', 'hpimagepath') ?: $part->image;
            $checkRate = min($part->rate, 100) !== 100;
        }

        $priceVal  = $option->getPrice($checkRate)->val();

        $optionAttr = [
            'value' => $option->id,
            'data'  => [
                'availability'    => $availability,
                'vendor-code'     => $option->vendor_code,
                'price'           => $priceVal,
                'in-cart'         => $isInCart ? 'true' : 'false',
                'monthly-payment' => $creditEnabled ? $this->hyper['helper']['credit']->getMonthlyPayment($priceVal)->val() : '',
                'image'           => Path::clean('/' . $imgSrc),
                'stores'          => $pickingDatesAttr,
                'pickup-str'      => $pickupFromTheStoreStr
            ]
        ];

        if ($optionDefault->id === $option->id) {
            $optionAttr['selected'] = 'selected';
        }

        if ($option->isOutOfStock() || $option->isDiscontinued()) {
            $optionAttr['disabled'] = 'disabled';
            $optionAttr['class'] = 'uk-disabled';
        }
        ?>

        <option <?= $this->hyper['helper']['html']->buildAttrs($optionAttr) ?>>
            <?= $option->name ?>
        </option>

    <?php endforeach; ?>

</select>
