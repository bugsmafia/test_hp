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

namespace HYPERPC\Object\Order;

use Joomla\Registry\Registry;
use Spatie\DataTransferObject\DataTransferObject;

class DeliveryData extends DataTransferObject
{

    /**
     * Need shipping flag
     */
    public string $need_shipping;

    /**
     * Delivery service title
     */
    public ?string $delivery_service;

    /**
     * Pickup point address
     */
    public ?string $pickup_point_address;

    /**
     * Pickup store id
     */
    public ?int $store;

    /**
     * Pickup date from store
     */
    public ?string $store_pickup_dates;

    /**
     * Shipping cost
     */
    public ?int $shipping_cost;

    /**
     * Min days to shipping
     */
    public ?int $days_min;

    /**
     * Max days to shipping
     */
    public ?int $days_max;

    /**
     * Min sending date
     */
    public ?string $sending_date_min;

    /**
     * Max sending date
     */
    public ?string $sending_date_max;

    /**
     * Address typed by user
     */
    public ?string $user_address_input;

    /**
     * Full adress
     */
    public ?string $original_address;

    /**
     * Fias
     */
    public ?string $fias_id;

    /**
     * City
     */
    public ?string $granular_address_locality;

    /**
     * Postal code
     */
    public ?string $granular_address_postal_code;

    /**
     * Street type
     */
    public ?string $granular_address_street_type;

    /**
     * Street name
     */
    public ?string $granular_address_street_name;

    /**
     * House type
     */
    public ?string $granular_address_house_type;

    /**
     * House name
     */
    public ?string $granular_address_house_name;

    /**
     * Block type
     */
    public ?string $granular_address_block_type;

    /**
     * Block name
     */
    public ?string $granular_address_block_name;

    /**
     * Flat type
     */
    public ?string $granular_address_flat_type;

    /**
     * Flat name
     */
    public ?string $granular_address_flat_name;

    /**
     * Parcel length
     */
    public ?int $parcel_dimentions_length;

    /**
     * Parcel width
     */
    public ?int $parcel_dimentions_width;

    /**
     * Parcel height
     */
    public ?int $parcel_dimentions_height;

    /**
     * Parcel weight
     */
    public ?float $parcel_weight;

    /**
     * Create from array
     */
    public static function fromArray(array $params)
    {
        $params = new Registry($params);
        $result = [];

        $result['need_shipping'] = $params->get('need_shipping', '0');
        if ($result['need_shipping'] === '1') {
            $shippingData = [
                'shipping_cost' => (int) $params->get('shipping_cost'),
                'days_min' => (int) $params->get('days_min'),
                'days_max' => (int) $params->get('days_max'),
                'parcel_dimentions_length' => (int) $params->get('parcel_dimentions_length'),
                'parcel_dimentions_width' => (int) $params->get('parcel_dimentions_width'),
                'parcel_dimentions_height' => (int) $params->get('parcel_dimentions_height'),
                'parcel_weight' => (float) $params->get('parcel_weight'),
                'store' => null
            ];

            $shippingData = array_merge($params->toArray(), $shippingData);
            $result = array_merge($shippingData, $result);
        } else {
            $pickupData = [
                'store' => (int) $params->get('store'),
                'store_pickup_dates' => $params->get('store_pickup_dates')
            ];

            $result = array_merge($pickupData, $result);
        }

        return new self($result);
    }
}
