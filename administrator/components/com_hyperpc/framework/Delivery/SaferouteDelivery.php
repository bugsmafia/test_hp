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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Object\Delivery\PointData;
use HYPERPC\Object\Delivery\DeliveryOptions;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Object\Delivery\PointDataCollection;

/**
 * Class SaferouteDelivery
 *
 * @package     HYPERPC\Delivery
 *
 * @since       2.0
 */
class SaferouteDelivery extends Delivery
{
    private const CITY_IDENTIFYRE_TYPE = 'fiasId';

    const API_URL     = 'https://api.saferoute.ru/v2/';
    const TOKEN       = 'kpuCPWlnIPgj9foQa37W97K2068redi-';
    const SHOP_ID_213 = 78637; // Moscow
    const SHOP_ID_2   = 78691; // St. Petersburg

    /**
     * Get city identifier type
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
     * @param   string|int       $cityIdTo       City fias code
     * @param   string|int       $cityIdFrom     City fias code
     * @param   MeasurementsData $measurments
     * @param   string|int       $assessedValue
     * @param   string           $hyperpcCourier
     *
     * @return  DeliveryOptions
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getDeliveryOptions($cityIdTo, $cityIdFrom, $measurments, $assessedValue, $hyperpcCourier): DeliveryOptions
    {
        $shopId = $this->_getShopId($cityIdFrom);

        $requestData['reception']['cityFias'] = $cityIdTo;
        $requestData['applyWidgetSettings']   = true;

        $requestData = array_merge(
            $requestData,
            $measurments->toArray()
        );

        $caching  = (int) Factory::getApplication()->getConfig()->get('caching', 0);
        $cache    = Factory::getCache(self::DELIVERY_CACHE_GROUP, null);
        $cacheKey = md5(json_encode($requestData) . $hyperpcCourier . $shopId);

        if ($caching > 0) {
            $cachedList = $cache->get($cacheKey, self::DELIVERY_CACHE_GROUP);
            if ($cachedList) {
                return DeliveryOptions::fromArray($cachedList);
            }
        }

        if ($assessedValue) {
            $requestData['priceCod'] = $assessedValue; // priceCod is used instead of priceDeclared because priceDeclared doesn't affect the rules
        }

        $url = self::API_URL . 'calculator';

        try {
            $deliveryTypes = $this->_executeRequest($url, $requestData, $shopId);
        } catch (Exception $th) {
            /** @todo log $th->getMessage() */
            return DeliveryOptions::fromArray(['pickup' => [], 'todoor' => [], 'post' => []]);
        }

        $keys               = ['pickup', 'todoor', 'post'];
        $deliveryTypes      = array_combine($keys, array_slice($deliveryTypes, 0, 3));
        $pickupPointsInfo   = [];
        $hasHyperpcCourier  = false;

        foreach ($deliveryTypes as $method => $deliveryType) {
            $data[$method] = [];

            if (empty($deliveryType)) {
                continue;
            }

            foreach ($deliveryType as $key => $tariff) {
                $deliveryId = $tariff['deliveryCompanyId'];

                $deliveryCost = $tariff['totalPrice'] - $tariff['priceCommissionDeclared']; // Delivery cost with rules, but without commission

                $fields = [
                    'companyName' => $tariff['deliveryCompanyName'],
                    'cost'        => (float) ceil($deliveryCost / 10) * 10,
                    'minDays'     => $tariff['deliveryDays']['min'],
                    'maxDays'     => $tariff['deliveryDays']['max'],
                ];

                if ($method === 'pickup' && !empty($tariff['points'])) {
                    foreach ($tariff['points'] as $i => $point) {
                        $pointId   = $point['id'];
                        $pointData = [
                            'address'     => $point['zipCode'] . ', ' . $point['address'],
                            'coordinates' => $point['latitude'] . ',' . $point['longitude']
                        ];

                        $pickupPointsInfo[$pointId] = $pointData;
                        $fields['pickupPointIds'][] = $pointId;

                        if ($i === 0) {
                            $deliveryCost = $point['totalPrice'] - $tariff['priceCommissionDeclared']; // tarif cost from the first pickup point

                            $fields['cost'] = (float) ceil($deliveryCost / 10) * 10;
                            $fields['defaultPickupPoint'] =  new PointData($pointData);
                        }
                    }
                }

                if ($tariff['deliveryCompanyName'] === Text::_('COM_HYPERPC_DELIVERY_TARIFF_NAME_COURIER_HYPERPC')) {
                    // Process courier cost
                    // if ($assessedValue < 50000) {
                    //     $fields['cost'] -= 360;
                    // } elseif ($measurments->weight >= 10) {
                    //     $fields['cost'] += 250;
                    // }

                    if ($hyperpcCourier === self::PARAM_COURIER_EXCEPT) {
                        continue;
                    }

                    $fields['minDays'] = 1;
                    $fields['maxDays'] = 1;

                    $hasHyperpcCourier = true;
                }

                $data[$method][$deliveryId] = $fields;
            }
        }

        if ($hasHyperpcCourier && $hyperpcCourier === self::PARAM_COURIER_ONLY) {
            $data = [
                'pickup' => [],
                'todoor' => array_filter(
                    $data['todoor'],
                    fn($tariff) => preg_match('/hyperpc|yandex\.go/i', $tariff['companyName'])
                ),
                'post' => []
            ];
        }

        $this->_storePickupPointsInCache($pickupPointsInfo, $cityIdTo);
        $this->_storeDeliveryInCache($data, $cacheKey);

        return DeliveryOptions::fromArray($data);
    }

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
    public function getPickupPointsInfo(array $pickupPointIds, string $geoId): PointDataCollection
    {
        $pickupPointInfo = [];
        $pickupPointData = $this->_getPickupPointsFromCache($pickupPointIds, $geoId);
        $uncachedPoints  = array_diff($pickupPointIds, array_keys($pickupPointData));

        if (empty($pickupPointData) || !empty($uncachedPoints)) {
            $requestData['cityFias'] = $geoId;

            $url = self::API_URL . 'lists/points';

            try {
                $pickupPoints = $this->_executeRequest($url, $requestData);
            } catch (Exception $th) {
                /** @todo log $th->getMessage() */
                return new PointDataCollection();
            }

            foreach ($pickupPoints as $point) {
                if (in_array($point['id'], $pickupPointIds)) {
                    $pickupPointInfo[] = $point;
                }
            }

            return PointDataCollection::create($pickupPointInfo);
        }

        return PointDataCollection::create($pickupPointData);
    }

    /**
     * Get shop id by geo id
     *
     * @param   int $geoIdFrom
     *
     * @return  int
     *
     * @since   2.0
     */
    protected function _getShopId($geoIdFrom)
    {
        $shopId = false;
        switch ((int) $geoIdFrom) {
            case 2:
                $shopId = self::SHOP_ID_2;
                break;
            default:
                $shopId = self::SHOP_ID_213;
                break;
        }

        return $shopId;
    }

    /**
     * Execute request.
     *
     * @param   string $url
     * @param   array  $requestData
     * @param   int    $shopId Optional
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _executeRequest($url, $requestData, $shopId = null)
    {
        $http = HttpFactory::getHttp(adapters: 'curl');

        $headers['Authorization'] = 'Bearer ' . self::TOKEN;
        if ($shopId !== null) {
            $headers['Shop-Id'] = $shopId;
        }

        $response = $http->post(
            $url,
            $requestData,
            $headers,
            20
        );

        if ($response->code != 200) {
            throw new Exception('error ' . $response->code);
        }

        return json_decode($response->body, true);
    }
}
