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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Delivery;

use Exception;
use Joomla\CMS\Factory;
use HYPERPC\Object\Delivery\DeliveryOptions;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Object\Delivery\PointDataCollection;

/**
 * Class Delivery
 *
 * @package     HYPERPC\Delivery
 *
 * @since       2.0
 */
abstract class Delivery
{
    const DELIVERY_CACHE_GROUP      = 'hp_delivery_options';
    const PICKUP_POINTS_CACHE_GROUP = 'hp_delivery_pickup_points';

    const PARAM_COURIER_DEFAULT = 'courier_default';
    const PARAM_COURIER_ONLY    = 'courier_only';
    const PARAM_COURIER_EXCEPT  = 'courier_except';

    /**
     * Get city identifier type
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getCityIdentifireType(): string;

    /**
     * Get delivery options
     *
     * @param   string|int       $cityIdTo
     * @param   string|int       $cityIdFrom
     * @param   MeasurementsData $measurments
     * @param   string|int       $assessedValue
     * @param   string           $hyperpcCourier
     *
     * @return  DeliveryOptions
     *
     * @since   2.0
     */
    abstract public function getDeliveryOptions($cityIdTo, $cityIdFrom, $measurments, $assessedValue, $hyperpcCourier): DeliveryOptions;

    /**
     * Get pickup points info
     *
     * @param   array  $pickupPointIds
     * @param   string $geoId cache key
     *
     * @return  PointDataCollection
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    abstract public function getPickupPointsInfo(array $pickupPointIds, string $geoId): PointDataCollection;

    /**
     * Try to store delivery list to the cache
     *
     * @param   mixed    $data data to store
     * @param   string   $cacheKey
     *
     * @return  void
     *
     * @since  2.0
     */
    protected function _storeDeliveryInCache($data, $cacheKey)
    {
        $caching = (int) Factory::getConfig()->get('caching', 0);
        $cache = Factory::getCache(self::DELIVERY_CACHE_GROUP, null);

        if ($caching > 0) {
            $cache->store($data, $cacheKey, self::DELIVERY_CACHE_GROUP);
        }
    }

    /**
     * Try to store pickup points in cache
     *
     * @param  array  $pickupPointsInfo
     * @param  string $cacheKey
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _storePickupPointsInCache($pickupPointsInfo, $cacheKey = 'pickup_points')
    {
        $caching  = (int) Factory::getConfig()->get('caching', 0);
        if ($caching > 0) {
            $cache      = Factory::getCache(self::PICKUP_POINTS_CACHE_GROUP, null);
            $cachedList = $cache->get($cacheKey, self::PICKUP_POINTS_CACHE_GROUP);

            if ($cachedList) {
                $list = $pickupPointsInfo + $cachedList;
                $cache->store($list, $cacheKey, self::PICKUP_POINTS_CACHE_GROUP);
            } else {
                $cache->store($pickupPointsInfo, $cacheKey, self::PICKUP_POINTS_CACHE_GROUP);
            }
        }
    }

    /**
     * Get pickup points from cache
     *
     * @param   array  $pickupPointIds
     * @param   string $cacheKey
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getPickupPointsFromCache($pickupPointIds, $cacheKey = 'pickup_points')
    {
        $caching      = (int) Factory::getConfig()->get('caching', 0);
        $cachedPoints = [];

        if ($caching > 0) {
            $cache      = Factory::getCache(self::PICKUP_POINTS_CACHE_GROUP, null);
            $cachedList = (array) $cache->get($cacheKey, self::PICKUP_POINTS_CACHE_GROUP);

            foreach ($cachedList as $pointId => $point) {
                if (!in_array($pointId, $pickupPointIds)) {
                    continue;
                }

                $cachedPoints[$pointId] = $point;
            }
        }

        return $cachedPoints;
    }
}
