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

namespace HYPERPC\Delivery;

use HYPERPC\Object\Delivery\DeliveryOptions;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Object\Delivery\PointDataCollection;

/**
 * Class UAECustomDelivery
 *
 * @package     HYPERPC\Delivery
 *
 * @since       2.0
 */
class UAECustomDelivery extends Delivery
{
    private const CITY_IDENTIFYRE_TYPE = 'geoId';

    const DHL_EXPRESS_DATA = [
        'BH' => ['country' => 'Bahrain', 'price' => 2036, 'minDays' => 1, 'maxDays' => 2],
        'KW' => ['country' => 'Kuwait', 'price' => 2036, 'minDays' => 1, 'maxDays' => 2],
        'OM' => ['country' => 'Oman', 'price' => 2036, 'minDays' => 1, 'maxDays' => 2],
        'QA' => ['country' => 'Qatar', 'price' => 2036, 'minDays' => 1, 'maxDays' => 2],
        'SA' => ['country' => 'Saudi Arabia', 'price' => 2036, 'minDays' => 1, 'maxDays' => 2]
    ];

    const FEDEX_EXPRESS_DATA = [
        'BH' => ['country' => 'Bahrain', 'price' => 3080, 'minDays' => 3, 'maxDays' => 7],
        'KW' => ['country' => 'Kuwait', 'price' => 3165, 'minDays' => 3, 'maxDays' => 7],
        'OM' => ['country' => 'Oman', 'price' => 2985, 'minDays' => 1, 'maxDays' => 4],
        'QA' => ['country' => 'Qatar', 'price' => 3080, 'minDays' => 1, 'maxDays' => 4],
        'SA' => ['country' => 'Saudi Arabia', 'price' => 6760, 'minDays' => 3, 'maxDays' => 7]
    ];

    const FEDEX_DATA = [
        'AE' => ['country' => 'United Arab Emirates', 'price' => 170, 'minDays' => 1, 'maxDays' => 2],
        'BH' => ['country' => 'Bahrain', 'price' => 889, 'minDays' => 7, 'maxDays' => 11],
        'KW' => ['country' => 'Kuwait', 'price' => 920, 'minDays' => 7, 'maxDays' => 11],
        'OM' => ['country' => 'Oman', 'price' => 840, 'minDays' => 3, 'maxDays' => 7],
        'QA' => ['country' => 'Qatar', 'price' => 889, 'minDays' => 3, 'maxDays' => 8],
        'SA' => ['country' => 'Saudi Arabia', 'price' => 1804, 'minDays' => 7, 'maxDays' => 10]
    ];

    /**
     * Get location identifier type
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCityIdentifireType(): string
    {
        return self::CITY_IDENTIFYRE_TYPE;
    }

    /**
     * Get delivery options
     *
     * @param   string|int       $cityIdTo       Country Id
     * @param   string|int       $cityIdFrom     Country Id
     * @param   MeasurementsData $measurments
     * @param   string|int       $assessedValue
     * @param   string           $hyperpcCourier
     *
     * @return  DeliveryOptions
     *
     * @since   2.0
     */
    public function getDeliveryOptions($cityIdTo, $cityIdFrom, $measurments, $assessedValue, $hyperpcCourier): DeliveryOptions
    {
        $todor = [];

        if (key_exists($cityIdTo, self::DHL_EXPRESS_DATA)) {
            $todor[] = [
                "companyName" => "DHL Express",
                "cost" => (float) self::DHL_EXPRESS_DATA[$cityIdTo]['price'],
                "minDays" => self::DHL_EXPRESS_DATA[$cityIdTo]['minDays'],
                "maxDays" => self::DHL_EXPRESS_DATA[$cityIdTo]['maxDays']
            ];
        }

        if (key_exists($cityIdTo, self::FEDEX_DATA)) {
            $todor[] = [
                "companyName" => "FedEx",
                "cost" => (float) self::FEDEX_DATA[$cityIdTo]['price'],
                "minDays" => self::FEDEX_DATA[$cityIdTo]['minDays'],
                "maxDays" => self::FEDEX_DATA[$cityIdTo]['maxDays']
            ];
        }

        if (key_exists($cityIdTo, self::FEDEX_EXPRESS_DATA)) {
            $todor[] = [
                "companyName" => "FedEx Express",
                "cost" => (float) self::FEDEX_EXPRESS_DATA[$cityIdTo]['price'],
                "minDays" => self::FEDEX_EXPRESS_DATA[$cityIdTo]['minDays'],
                "maxDays" => self::FEDEX_EXPRESS_DATA[$cityIdTo]['maxDays']
            ];
        }

        return DeliveryOptions::fromArray(['pickup' => [], 'todoor' => $todor, 'post' => []]);
    }

    /**
     * Get pickup points info
     *
     * @param   array  $pickupPointIds
     * @param   string $geoId cache key
     *
     * @return  PointDataCollection
     *
     * @since   2.0
     */
    public function getPickupPointsInfo(array $pickupPointIds, string $geoId): PointDataCollection
    {
        return PointDataCollection::create([]);
    }
}
