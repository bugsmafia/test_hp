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

namespace HYPERPC\Helper;

use DateTime;
use ReflectionClass;
use JBZoo\Data\Data;
use JBZoo\Utils\Arr;
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;

/**
 * Class YandexDeliveryHelper
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class YandexDeliveryHelper extends AppHelper
{
    const YANDEX_DELIVERY_API_TOKEN = 'AQAAAAAQf9MEAAbQ8bem1dlAk0f7iutHVPzkmQM';
    const DELIVERY_OPTIONS_URL      = 'https://api.delivery.yandex.ru/delivery-options';
    const PICKUP_POINTS_URL         = 'https://api.delivery.yandex.ru/pickup-points';
    const LOCATION_URL              = 'https://api.delivery.yandex.ru/location';

    const DELIVERY_CACHE_GROUP      = 'hp_delivery_options';
    const PICKUP_POINTS_CACHE_GROUP = 'hp_delivery_pickup_points';

    const PARAM_COURIER_DEFAULT = 'courier_default';
    const PARAM_COURIER_ONLY    = 'courier_only';
    const PARAM_COURIER_EXCEPT  = 'courier_except';

    const SENDER_ID_213 = 500001220; // Moscow
    const SENDER_ID_2   = 500003719; // St. Petersburg

    /**
     * Get delivery options
     *
     * @param   string|int       $geoIdTo
     * @param   string|int       $geoIdFrom
     * @param   MeasurementsData $measurments
     * @param   string           $hyperCourier
     *
     * @return  array
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @since   2.0
     */
    public function getDeliveryOptions($geoIdTo, $measurments, $geoIdFrom = 213, $assessedValue = 0, $hyperCourier = self::PARAM_COURIER_DEFAULT)
    {
        $senderId = $this->_getSenderId($geoIdFrom);

        $requestData = [
            'senderId' => $senderId,
            'from' => [
                'geoId' => $geoIdFrom
            ],
            'to' => [
                'geoId' => $geoIdTo
            ],
            'dimensions' => [
                'length' => $measurments->dimensions->length,
                'width'  => $measurments->dimensions->width,
                'height' => $measurments->dimensions->height,
                'weight' => $measurments->weight,
            ],
            'cost' => [
                'itemsSum' => min($assessedValue, 100000), // max 100 000 for accessed value
                'fullyPrepaid' => false
            ]
        ];

        $caching  = (int) Factory::getConfig()->get('caching', 0);
        $cacheKey = '';
        if ($caching > 0) {
            $cache    = Factory::getCache(self::DELIVERY_CACHE_GROUP, null);
            $cacheKeyParams = $requestData;
            unset($cacheKeyParams['cost']);
            $cacheKey = md5(json_encode($requestData) . $hyperCourier);
            $cachedList = $cache->get($cacheKey, self::DELIVERY_CACHE_GROUP);
            if ($cachedList) {
                return $cachedList;
            }
        }

        if ($this->_customShippingAvailable($geoIdTo)) {
            $todor = [];

            if ($senderId === self::SENDER_ID_213) {
                $courierCost = $this->_getCourierCost($geoIdFrom, $geoIdTo, $measurments->weight, $assessedValue);
                if ($courierCost) {
                    $todor[53082] = [
                        "companyName" => "Курьер HYPERPC",
                        "cost" => (float) $courierCost,
                        "minDays" => 1,
                        "maxDays" => 1
                    ];
                }
            }

            $yandexGoCost = $this->_getYandexGoCost($geoIdFrom, $geoIdTo, $measurments->weight, $assessedValue);
            if ($yandexGoCost) {
                $todor[53616] = [
                    "companyName" => "Курьер Yandex.Go",
                    "cost" => (float) $yandexGoCost,
                    "minDays" => 0,
                    "maxDays" => 0
                ];
            }

            $courierTKCost = $this->_getCourierTKCost($geoIdFrom, $geoIdTo, $measurments->weight, $assessedValue);
            if ($courierTKCost) {
                $todor[175437] = [
                    "companyName" => "Курьер ТК",
                    "cost" => (float) $courierTKCost,
                    "minDays" => $geoIdFrom === '2' ? 1 : 2,
                    "maxDays" => $geoIdFrom === '2' ? 2 : 3
                ];
            }

            $dellinCost = $this->_getDellinCost($geoIdFrom, $geoIdTo, $measurments->weight, $assessedValue);
            if ($dellinCost) {
                $todor[53494] = [
                    "companyName" => "Деловые линии",
                    "cost" => (float) $dellinCost,
                    "minDays" => self::DELLIN_DATA[$geoIdTo]['minDays'],
                    "maxDays" => self::DELLIN_DATA[$geoIdTo]['maxDays']
                ];
            }

            $simplifiedList = [
                'todoor' => $todor,
                'pickup' => [],
                'post'   => []
            ];

            $this->_storeDeliveryInCache($simplifiedList, $cacheKey);

            return $simplifiedList;
        }

        return [
            'todoor' => [],
            'pickup' => [],
            'post'   => []
        ];

        $http = HttpFactory::getHttp([], 'curl');
        $response = $http->put(
            self::DELIVERY_OPTIONS_URL,
            json_encode($requestData),
            [
                'Authorization' => 'OAuth ' . self::YANDEX_DELIVERY_API_TOKEN,
                'Content-Type' => 'application/json'
            ]
        );

        $simplifiedList = [
            'todoor' => [],
            'pickup' => [],
            'post'   => []
        ];

        if ($response->code === 200) {
            $data = json_decode($response->body);

            $defaultPickupPoints = [];

            $hyperCourierPartnerId = 0;
            $yandexGoPartnerId = 0;

            foreach ($data as $tariff) {
                $method = 'todoor';
                switch ($tariff->delivery->type) {
                    case 'PICKUP':
                        $method = 'pickup';
                        break;
                    case 'POST':
                        $method = 'post';
                        break;
                }

                $partnerId = $tariff->delivery->partner->id;

                if (isset($simplifiedList[$method][$partnerId])) {
                    $minDays = min(
                        $simplifiedList[$method][$partnerId]['minDays'],
                        $this->_calculateShippingDays($tariff->delivery->calculatedDeliveryDateMin)
                    );
                    $maxDays = max(
                        $simplifiedList[$method][$partnerId]['maxDays'],
                        $this->_calculateShippingDays($tariff->delivery->calculatedDeliveryDateMax)
                    );
                    $simplifiedList[$method][$partnerId]['minDays'] = $minDays;
                    $simplifiedList[$method][$partnerId]['maxDays'] = $maxDays;
                    if (isset($simplifiedList[$method][$partnerId]['pickupPointIds']) && $tariff->pickupPointIds !== null) {
                        $pickupPoints = array_merge(
                            $simplifiedList[$method][$partnerId]['pickupPointIds'],
                            $tariff->pickupPointIds
                        );
                        $simplifiedList[$method][$partnerId]['pickupPointIds'] = $pickupPoints;
                    }
                } else {
                    $fields = [
                        'companyName' => $tariff->delivery->partner->name,
                        'cost'        => ceil($tariff->cost->deliveryForCustomer / 10) * 10,
                        'minDays'     => $this->_calculateShippingDays($tariff->delivery->calculatedDeliveryDateMin),
                        'maxDays'     => $this->_calculateShippingDays($tariff->delivery->calculatedDeliveryDateMax),
                    ];

                    if ($method === 'pickup' && $tariff->pickupPointIds !== null) {
                        $fields['pickupPointIds'] = $tariff->pickupPointIds;
                        $fields['defaultPickupPoint'] = Arr::first($tariff->pickupPointIds);
                        $defaultPickupPoints[] = $fields['defaultPickupPoint'];
                    }

                    if ($tariff->delivery->partner->name === Text::_('COM_HYPERPC_DELIVERY_TARIFF_NAME_COURIER_HYPERPC')) {
                        switch ($hyperCourier) {
                            case self::PARAM_COURIER_ONLY:
                                $hyperCourierPartnerId = $partnerId;
                                break;
                            case self::PARAM_COURIER_EXCEPT:
                                continue 2;
                                break;
                        }
                    }

                    if (preg_match('/Yandex\.Go/', $tariff->delivery->partner->name)) {
                        $yandexGoPartnerId = $partnerId;
                    }

                    $simplifiedList[$method][$partnerId] = $fields;
                }
            }

            if ($hyperCourier === self::PARAM_COURIER_ONLY && $hyperCourierPartnerId) {
                $result = ['todoor' => []];
                $result['todoor'][$hyperCourierPartnerId] = $simplifiedList['todoor'][$hyperCourierPartnerId];

                if ($yandexGoPartnerId) {
                    $result['todoor'][$yandexGoPartnerId] = $simplifiedList['todoor'][$yandexGoPartnerId];
                }

                $this->_storeDeliveryInCache($result, $cacheKey);
                return $result;
            }

            if (!empty($defaultPickupPoints)) {
                $pickupPointsInfo = $this->getPickupPointsInfo($defaultPickupPoints, $geoIdTo);

                foreach ($simplifiedList['pickup'] as $partnerId => $tariff) {
                    if (isset($tariff['defaultPickupPoint']) && isset($pickupPointsInfo[$tariff['defaultPickupPoint']])) {
                        $tariff['defaultPickupPoint'] = $pickupPointsInfo[$tariff['defaultPickupPoint']];
                        $simplifiedList['pickup'][$partnerId] = $tariff;
                    }
                }
            }
        }

        $this->_storeDeliveryInCache($simplifiedList, $cacheKey);

        return $simplifiedList;
    }

    /**
     * Get pickup points info
     *
     * @param   array $pickupPointIds
     * @param   int $geoId cache key
     *
     * @return  array
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function getPickupPointsInfo($pickupPointIds, $geoId)
    {
        if (empty($pickupPointIds)) {
            return [];
        }

        $pickupPointsInfo = [];
        $cachedInfo = $this->_getPickupPointsFromCache($pickupPointIds, $geoId);
        $cachedPoints = $cachedInfo->get('cached', []);
        $uncachedPoints = $cachedInfo->get('uncached', []);
        $output = [];

        if (!empty($uncachedPoints)) {
            if (count($uncachedPoints) > 99) {
                $chunked = array_chunk($uncachedPoints, 99);
                foreach ($chunked as $ids) {
                    $pickupPointsInfo = array_merge($pickupPointsInfo, $this->_pickupPointsApiRequest($ids));
                }
            } else {
                $pickupPointsInfo = $this->_pickupPointsApiRequest($uncachedPoints);
            }

            $output = [];
            foreach ($pickupPointsInfo as $pickupPoint) {
                $output[$pickupPoint->id] = [
                    'address' => $pickupPoint->address->addressString
                ];
            }

            $output = $cachedPoints + $output;

            $this->_storePickupPointsInCache($output, $geoId);

            return $output;
        }

        return $cachedPoints;
    }

    /**
     * Get location suggestions.
     *
     * @param   string $requestedString
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getLocalitySuggestions($requestedString)
    {
        $requestUrl = self::LOCATION_URL . '?term=' . urlencode($requestedString);

        $http = HttpFactory::getHttp([], 'curl');
        $response = $http->get($requestUrl, [
            'Authorization' => 'OAuth ' . self::YANDEX_DELIVERY_API_TOKEN,
        ]);

        if ($response->code !== 200) {
            $responseBody = json_decode($response->body);
            throw new \Exception($responseBody->message, $response->code);
        }

        return json_decode($response->body);
    }

    /**
     * Calculate days to shipping by delivery date
     *
     * @param  string $calculatedDeliveryDate date in 'Y-m-d' format
     *
     * @return int|boolean
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    protected function _calculateShippingDays($calculatedDeliveryDate)
    {
        $currentDate = new DateTime(date('Y-m-d'));
        $deliveryDate = new DateTime($calculatedDeliveryDate);

        return $currentDate->diff($deliveryDate)->days;
    }

    /**
     * Get pickup points from cache
     *
     * @param  array $pickupPointIds
     * @param  string $cacheKey
     *
     * @return  Data
     *
     * @since   2.0
     */
    protected function _getPickupPointsFromCache($pickupPointIds, $cacheKey = 'pickup_points')
    {
        $caching  = (int) Factory::getConfig()->get('caching', 0);
        if ($caching > 0) {
            $cache    = Factory::getCache(self::PICKUP_POINTS_CACHE_GROUP, null);
            $cachedList = $cache->get($cacheKey, self::PICKUP_POINTS_CACHE_GROUP);
            if ($cachedList) {
                $cachedPoints = [];
                $uncachedPoints = [];
                foreach ($pickupPointIds as $id) {
                    if (isset($cachedList[$id])) {
                        $cachedPoints[$id] = $cachedList[$id];
                    } else {
                        $uncachedPoints[] = $id;
                    }
                }
                return new Data([
                    'cached' => $cachedPoints,
                    'uncached' => $uncachedPoints
                ]);
            }
        }

        return new Data([
            'cached' => [],
            'uncached' => $pickupPointIds
        ]);
    }

    /**
     * Get sender id by geo id
     *
     * @param   int $geoIdFrom
     *
     * @return  int
     *
     * @since   2.0
     */
    protected function _getSenderId($geoIdFrom)
    {
        $senderId = false;
        switch (Filter::int($geoIdFrom)) {
            case 213:
                $senderId = self::SENDER_ID_213;
                break;
            case 2:
                $senderId = self::SENDER_ID_2;
                break;
            default:
                $senderId = self::SENDER_ID_213;
                break;
        }

        return $senderId;
    }

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
        if ($caching > 0) {
            $cache = Factory::getCache(self::DELIVERY_CACHE_GROUP, null);
            $cache->store($data, $cacheKey, self::DELIVERY_CACHE_GROUP);
        }
    }

    /**
     * Store pickup points in cache
     *
     * @param  array $pickupPointsInfo
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
     * Get pickup points info by ids
     *
     * @param   array $pickupPointIds limited to 100 ids per request
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _pickupPointsApiRequest($pickupPointIds)
    {
        $requestData = [
            'pickupPointIds' => $pickupPointIds
        ];

        $http = HttpFactory::getHttp([], 'curl');
        $response = $http->put(
            self::PICKUP_POINTS_URL,
            json_encode($requestData),
            [
                'Authorization' => 'OAuth ' . self::YANDEX_DELIVERY_API_TOKEN,
                'Content-Type' => 'application/json'
            ]
        );

        if ($response->code !== 200) {
            $responseBody = json_decode($response->body);
            throw new \Exception($responseBody->message, $response->code);
        }

        return json_decode($response->body);
    }

    protected function _customShippingAvailable($geoId)
    {
        $geoIds = [];

        $geoIds = array_merge(
            array_keys(self::COURIER_PRICES),
            array_keys(self::YANDEX_GO_PRICES_2),
            array_keys(self::YANDEX_GO_PRICES_213),
            array_keys(self::COURIER_TK_PRICES_2),
            array_keys(self::COURIER_TK_PRICES_213),
            array_keys(self::DELLIN_DATA)
        );

        return in_array($geoId, $geoIds);
    }

    private function _getCourierCost($geoIdFrom, $geoIdTo, $weight, $assessedValue)
    {
        $courierPrices = self::COURIER_PRICES;

        if (isset($courierPrices[$geoIdTo])) {
            $price =  $courierPrices[$geoIdTo]['price'];

            if ($weight < 10) {
                $price -= 360;
            } elseif ($assessedValue >= 20000) {
                $price += 250;
            }

            return $price;
        }

        return null;
    }

    private function _getYandexGoCost($geoIdFrom, $geoIdTo, $weight, $assessedValue)
    {
        if ($weight > 60) {
            return null;
        }

        $thisClass = new ReflectionClass(__CLASS__);
        $prices = $thisClass->getConstant("YANDEX_GO_PRICES_$geoIdFrom");

        if (isset($prices[$geoIdTo])) {
            return $prices[$geoIdTo]['price'];
        }

        return null;
    }

    private function _getCourierTKCost($geoIdFrom, $geoIdTo, $weight, $assessedValue)
    {
        if ($weight > 13 && $assessedValue >= 60000) {
            // return null;
        }

        $thisClass = new ReflectionClass(__CLASS__);
        $prices = $thisClass->getConstant("COURIER_TK_PRICES_$geoIdFrom");

        if (isset($prices[$geoIdTo])) {
            return $prices[$geoIdTo]['price'];
        }

        return null;
    }

    private function _getDellinCost($geoIdFrom, $geoIdTo, $weight, $assessedValue)
    {
        if ($geoIdFrom === '2' || $geoIdTo === '2' || $weight < 13) {
            return null;
        }

        $data = self::DELLIN_DATA;

        if (isset($data[$geoIdTo])) {
            $price = $data[$geoIdTo]['price'];
            if ($weight > 25) {
                $price += 2000;
            }
            return ceil($price / 10) * 10;
        }

        return null;
    }

    const YANDEX_GO_PRICES_2 = [
        '2' => ['city' => 'Санкт-Петербург', 'price' => 500],
    ];

    const YANDEX_GO_PRICES_213 = [
        '213' => ['city' => 'Москва', 'price' => 500],
    ];

    const COURIER_TK_PRICES_2 = [
        '2' => ['city' => 'Санкт-Петербург', 'price' => 750],
        '98546' => ['city' => 'Петергоф', 'price' => 1400],
        '21776' => ['city' => 'Крондштадт', 'price' => 2000],
        '120591' => ['city' => 'Стрельна', 'price' => 1000],
        '109773' => ['city' => 'Красное Село', 'price' => 1800],
        '10884' => ['city' => 'Пушкин', 'price' => 1600],
        '121356' => ['city' => 'Тельмана', 'price' => 1400],
        '26081' => ['city' => 'Колпино', 'price' => 1400],
        '120583' => ['city' => 'Металлстрой', 'price' => 1000],
        '10865' => ['city' => 'Всеволожск', 'price' => 1000],
        '118936' => ['city' => 'Мурино', 'price' => 800],
        '10887' => ['city' => 'Сертолово', 'price' => 1000],
    ];

    const COURIER_TK_PRICES_213 = [
        '2' => ['city' => 'Санкт-Петербург', 'price' => 750],
    ];

    const COURIER_PRICES = [
        '213' => ['city' => 'Москва', 'price' => 750],
        '117092' => ['city' => 'п. Агрогородок', 'price' => 2150],
        '10715' => ['city' => 'Апрелевка', 'price' => 1850],
        '10716' => ['city' => 'Балашиха', 'price' => 1050],
        '10717' => ['city' => 'Бронницы', 'price' => 2550],
        '10719' => ['city' => 'Видное', 'price' => 950],
        '21646' => ['city' => 'Голицыно', 'price' => 1950],
        '21627' => ['city' => 'Дедовск', 'price' => 1550],
        '21735' => ['city' => 'Дзержинский', 'price' => 750],
        '10723' => ['city' => 'Дмитров', 'price' => 3050],
        '214' => ['city' => 'Долгопрудный', 'price' => 750],
        '10725' => ['city' => 'Домодедово', 'price' => 1470],
        '117954' => ['city' => 'Железнодорожный', 'price' => 1150],
        '20571' => ['city' => 'Жуковский', 'price' => 1710],
        '10729' => ['city' => 'Звенигород', 'price' => 2470],
        '21623' => ['city' => 'Ивантеевка', 'price' => 1550],
        '10731' => ['city' => 'Истра', 'price' => 2350],
        '37147' => ['city' => 'Климовск', 'price' => 1790],
        '29393' => ['city' => 'Коммунарка', 'price' => 850],
        '20728' => ['city' => 'Королёв', 'price' => 1150],
        '21651' => ['city' => 'Котельники', 'price' => 750],
        '100471' => ['city' => 'Красноармейск', 'price' => 2390],
        '10735' => ['city' => 'Красногорск', 'price' => 1150],
        '21647' => ['city' => 'Краснознаменск', 'price' => 1850],
        '21625' => ['city' => 'Кубинка', 'price' => 2750],
        '21641' => ['city' => 'Лобня', 'price' => 1550],
        '21635' => ['city' => 'Лосино-Петровский', 'price' => 1950],
        '21630' => ['city' => 'Лыткарино', 'price' => 1550],
        '10738' => ['city' => 'Люберцы', 'price' => 830],
        '103817' => ['city' => 'Московский', 'price' => 1250],
        '10740' => ['city' => 'Мытищи', 'price' => 750],
        '10741' => ['city' => 'Наро-Фоминск', 'price' => 3050],
        '10742' => ['city' => 'Ногинск', 'price' => 2150],
        '10743' => ['city' => 'Одинцово', 'price' => 1150],
        '10747' => ['city' => 'Подольск', 'price' => 1550],
        '10748' => ['city' => 'Пушкино', 'price' => 1550],
        '10750' => ['city' => 'Раменское', 'price' => 2050],
        '21621' => ['city' => 'Реутов', 'price' => 750],
        '10752' => ['city' => 'Сергиев Посад', 'price' => 3150],
        '10755' => ['city' => 'Солнечногорск', 'price' => 2650],
        '21656' => ['city' => 'Старая Купавна', 'price' => 1650],
        '20674' => ['city' => 'Троицк', 'price' => 1650],
        '21619' => ['city' => 'Фрязино', 'price' => 1750],
        '10758' => ['city' => 'Химки', 'price' => 750],
        '21645' => ['city' => 'Хотьково', 'price' => 2950],
        '219' => ['city' => 'Черноголовка', 'price' => 2450],
        '10761' => ['city' => 'Чехов', 'price' => 2950],
        '10765' => ['city' => 'Щёлково', 'price' => 1550],
        '21624' => ['city' => 'Щербинка', 'price' => 1050],
        '20523' => ['city' => 'Электросталь', 'price' => 2450],
        '21642' => ['city' => 'Электроугли', 'price' => 1750],
        '20735' => ['city' => 'Яхрома', 'price' => 2750],
        '120113' => ['city' => 'Сухарево', 'price' => 1550],
        '110266' => ['city' => 'Икша', 'price' => 1950],
        '100985' => ['city' => 'Деденёво', 'price' => 2350],
        '118759' => ['city' => 'Марфино', 'price' => 1650],
        '21636' => ['city' => 'Монино', 'price' => 1850],
        '105572' => ['city' => 'Малаховка', 'price' => 1450],
        '21745' => ['city' => 'Нахабино', 'price' => 1350],
        '119530' => ['city' => 'Петрово-Дальнее', 'price' => 1450],
        '10733' => ['city' => 'Клин', 'price' => 3550],
        '110269' => ['city' => 'Поварово', 'price' => 2250],
        '117423' => ['city' => 'Брёхово', 'price' => 1550],
        '119043' => ['city' => 'Николина гора', 'price' => 1750],
        '109762' => ['city' => 'Большие Вязёмы', 'price' => 1870],
        '101754' => ['city' => 'Власиха', 'price' => 1550],
        '121774' => ['city' => 'Рассказовка', 'price' => 1050],
        '117010' => ['city' => 'Кокошкино', 'price' => 1750],
        '117062' => ['city' => 'Птичное', 'price' => 1950],
        '21707' => ['city' => 'Мосрентген', 'price' => 750],
        '115078' => ['city' => 'Солнцево', 'price' => 750],
        '10755' => ['city' => 'Жаворонки', 'price' => 1550],
        '116396' => ['city' => 'Калининец', 'price' => 2350],
        '10743' => ['city' => 'НИИ Радио', 'price' => 2070],
        '120112' => ['city' => 'Суханово', 'price' => 1150],
        '101602' => ['city' => 'Ватутинки', 'price' => 1350],
        '114619' => ['city' => 'Валуево', 'price' => 1250],
        '120351' => ['city' => 'Хрипань', 'price' => 1950],
        '101060' => ['city' => 'Томилино', 'price' => 1150],
        '118178' => ['city' => 'рп.им.Воровского', 'price' => 2050],
        '119587' => ['city' => 'Поведники', 'price' => 1150],
        '117279' => ['city' => 'Беляниново', 'price' => 1050],
        '119997' => ['city' => 'Сорокино', 'price' => 1550],
        '117530' => ['city' => 'Вёшки', 'price' => 750],
        '21638' => ['city' => 'Правдинский', 'price' => 1750],
        '117099' => ['city' => 'Аксаково', 'price' => 1550],
        '119046' => ['city' => 'Николо-Прозорово', 'price' => 1870],
        '101064' => ['city' => 'Фряново', 'price' => 3150],
        '117060' => ['city' => 'Шишкин Лес', 'price' => 2110],
        '117064' => ['city' => 'Щапово', 'price' => 1950],
        '117729' => ['city' => 'Горки Ленинские', 'price' => 1350],
        '101764' => ['city' => 'Красково', 'price' => 1250],
        '105574' => ['city' => 'Октябрьский', 'price' => 1250],
        '10746' => ['city' => 'Павловский Посад', 'price' => 3150],
        '117008' => ['city' => 'Воскресенское', 'price' => 2190],
        '117713' => ['city' => 'Горки-10', 'price' => 1750],
        '10743' => ['city' => 'Горки-2', 'price' => 1350],
        '121838' => ['city' => 'Успенское', 'price' => 1550],
        '118169' => ['city' => 'Ильинское', 'price' => 1250],
        '120394' => ['city' => 'Чесноково', 'price' => 1670],
        '119291' => ['city' => 'Обушково', 'price' => 1750],
        '117092' => ['city' => 'Агрогородок', 'price' => 2150],
        '119808' => ['city' => 'Сабурово', 'price' => 1250],
        '109769' => ['city' => 'Некрасовский', 'price' => 1670],
        '117927' => ['city' => 'Ерёмино', 'price' => 1250],
        '117217' => ['city' => 'Ашукино', 'price' => 2350],
        '117008' => ['city' => 'Воскресенское', 'price' => 1250],
        '117943' => ['city' => 'Ершово', 'price' => 2350],
        '21652' => ['city' => 'Барвиха', 'price' => 1150],
        '118989' => ['city' => 'Немчиновка', 'price' => 750],
        '117555' => ['city' => 'посёлок ВНИИССОК', 'price' => 1470],
        '118559' => ['city' => 'Лайково', 'price' => 1470],
        '121889' => ['city' => 'Яковлевское', 'price' => 2350],
        '117057' => ['city' => 'Киевский', 'price' => 2590],
        '118801' => ['city' => 'Менделеево', 'price' => 1950],
        '120301' => ['city' => 'Федоскино', 'price' => 1550],
        '121322' => ['city' => 'посёлок Володарского', 'price' => 1550],
        '21655' => ['city' => 'Обухово', 'price' => 1950],
        '10750' => ['city' => 'Бисерово', 'price' => 1550],
        '119399' => ['city' => 'Островцы', 'price' => 1350],
        '118915' => ['city' => 'Молоково', 'price' => 1150],
        '121890' => ['city' => 'Ям', 'price' => 1350],
        '121863' => ['city' => 'Чурилково', 'price' => 1350],
        '121530' => ['city' => 'Барыбино', 'price' => 2670],
        '117059' => ['city' => 'Красная Пахра', 'price' => 1750],
        '117011' => ['city' => 'Марушкино', 'price' => 1470],
        '121670' => ['city' => 'Крёкшино', 'price' => 1750],
        '117782' => ['city' => 'Давыдовское', 'price' => 2450],
        '216' => ['city' => 'Зеленоград', 'price' => 1950],
        '215' => ['city' => 'Дубна', 'price' => 4550],
        '10721' => ['city' => 'Волоколамск', 'price' => 4150],
        '10745' => ['city' => 'Орехово-Зуево', 'price' => 3350],
        '10734' => ['city' => 'Коломна', 'price' => 3950],
        '10732' => ['city' => 'Кашира', 'price' => 4150],
        '10754' => ['city' => 'Серпухов', 'price' => 3550],
        '10756' => ['city' => 'Ступино', 'price' => 3750],
        '10722' => ['city' => 'Воскресенск', 'price' => 3350],
        '10737' => ['city' => 'Луховицы', 'price' => 4950],
        '37120' => ['city' => 'Электрогорск', 'price' => 2750],
        '10751' => ['city' => 'Руза', 'price' => 3750],
        '10739' => ['city' => 'Можайск', 'price' => 3950],
        '10727' => ['city' => 'Егорьевск', 'price' => 3950]
    ];

    const DELLIN_DATA = [
        '1095' => ['city' => 'Абакан', 'price' => 4412, 'minDays' => 11, 'maxDays' => 13],
        '20012' => ['city' => 'Агрыз', 'price' => 3722, 'minDays' => 7, 'maxDays' => 8],
        '239' => ['city' => 'Сочи', 'price' => 3762, 'minDays' => 6, 'maxDays' => 7],
        '20192' => ['city' => 'Алексеевка', 'price' => 4741, 'minDays' => 6, 'maxDays' => 7],
        '10825' => ['city' => 'Алексин', 'price' => 3604, 'minDays' => 4, 'maxDays' => 5],
        '11256' => ['city' => 'Ангарск', 'price' => 4161, 'minDays' => 13, 'maxDays' => 15],
        '10894' => ['city' => 'Апатиты', 'price' => 2960, 'minDays' => 7, 'maxDays' => 8],
        '10987' => ['city' => 'Армавир', 'price' => 2998, 'minDays' => 6, 'maxDays' => 7],
        '20' => ['city' => 'Архангельск', 'price' => 3182, 'minDays' => 5, 'maxDays' => 6],
        '37' => ['city' => 'Астрахань', 'price' => 3228, 'minDays' => 6, 'maxDays' => 7],
        '20197' => ['city' => 'Балабаново', 'price' => 2988, 'minDays' => 5, 'maxDays' => 6],
        '10858' => ['city' => 'Балтийск', 'price' => 8894, 'minDays' => 5, 'maxDays' => 6],
        '197' => ['city' => 'Барнаул', 'price' => 4070, 'minDays' => 8, 'maxDays' => 10],
        '11033' => ['city' => 'Батайск', 'price' => 3318, 'minDays' => 6, 'maxDays' => 7],
        '4' => ['city' => 'Белгород', 'price' => 2849, 'minDays' => 4, 'maxDays' => 5],
        '11277' => ['city' => 'Белово', 'price' => 5312, 'minDays' => 12, 'maxDays' => 14],
        '11314' => ['city' => 'Бердск', 'price' => 4180, 'minDays' => 9, 'maxDays' => 11],
        '20237' => ['city' => 'Березники', 'price' => 3228, 'minDays' => 9, 'maxDays' => 11],
        '29397' => ['city' => 'Березовский', 'price' => 6857, 'minDays' => 12, 'maxDays' => 14],
        '77' => ['city' => 'Благовещенск', 'price' => 5561, 'minDays' => 25, 'maxDays' => 29],
        '20667' => ['city' => 'Богородицк', 'price' => 4035, 'minDays' => 6, 'maxDays' => 7],
        '10906' => ['city' => 'Боровичи', 'price' => 2856, 'minDays' => 7, 'maxDays' => 8],
        '976' => ['city' => 'Братск', 'price' => 4640, 'minDays' => 13, 'maxDays' => 15],
        '191' => ['city' => 'Брянск', 'price' => 2842, 'minDays' => 4, 'maxDays' => 5],
        '11122' => ['city' => 'Бугульма', 'price' => 3705, 'minDays' => 7, 'maxDays' => 8],
        '10928' => ['city' => 'Великие Луки', 'price' => 3371, 'minDays' => 5, 'maxDays' => 6],
        '24' => ['city' => 'Великий Новгород', 'price' => 2602, 'minDays' => 4, 'maxDays' => 5],
        '10843' => ['city' => 'Вельск', 'price' => 3147, 'minDays' => 6, 'maxDays' => 7],
        '11161' => ['city' => 'Верхняя Салда', 'price' => 4087, 'minDays' => 8, 'maxDays' => 10],
        '75' => ['city' => 'Владивосток', 'price' => 6627, 'minDays' => 19, 'maxDays' => 22],
        '33' => ['city' => 'Владикавказ', 'price' => 3430, 'minDays' => 7, 'maxDays' => 8],
        '192' => ['city' => 'Владимир', 'price' => 2520, 'minDays' => 3, 'maxDays' => 4],
        '38' => ['city' => 'Волгоград', 'price' => 3174, 'minDays' => 5, 'maxDays' => 6],
        '11036' => ['city' => 'Волгодонск', 'price' => 2787, 'minDays' => 7, 'maxDays' => 8],
        '21' => ['city' => 'Вологда', 'price' => 2650, 'minDays' => 4, 'maxDays' => 5],
        '10864' => ['city' => 'Волхов', 'price' => 5639, 'minDays' => 5, 'maxDays' => 6],
        '10940' => ['city' => 'Воркута', 'price' => 3839, 'minDays' => 18, 'maxDays' => 21],
        '193' => ['city' => 'Воронеж', 'price' => 2818, 'minDays' => 4, 'maxDays' => 5],
        '10722' => ['city' => 'Воскресенск', 'price' => 3085, 'minDays' => 6, 'maxDays' => 7],
        '11149' => ['city' => 'Воткинск', 'price' => 3026, 'minDays' => 8, 'maxDays' => 10],
        '10865' => ['city' => 'Всеволожск', 'price' => 3336, 'minDays' => 9, 'maxDays' => 11],
        '1106' => ['city' => 'Грозный', 'price' => 4259, 'minDays' => 8, 'maxDays' => 9],
        '119183' => ['city' => 'Новое Девяткино', 'price' => 3052, 'minDays' => 3, 'maxDays' => 4],
        '972' => ['city' => 'Дзержинск', 'price' => 2705, 'minDays' => 5, 'maxDays' => 6],
        '11155' => ['city' => 'Димитровград', 'price' => 3131, 'minDays' => 6, 'maxDays' => 7],
        '10993' => ['city' => 'Ейск', 'price' => 2908, 'minDays' => 8, 'maxDays' => 10],
        '54' => ['city' => 'Екатеринбург', 'price' => 3468, 'minDays' => 5, 'maxDays' => 6],
        '11057' => ['city' => 'Ессентуки', 'price' => 3163, 'minDays' => 7, 'maxDays' => 8],
        '11125' => ['city' => 'Зеленодольск', 'price' => 3290, 'minDays' => 7, 'maxDays' => 8],
        '5' => ['city' => 'Иваново', 'price' => 2636, 'minDays' => 4, 'maxDays' => 5],
        '44' => ['city' => 'Ижевск', 'price' => 2461, 'minDays' => 6, 'maxDays' => 7],
        '63' => ['city' => 'Иркутск', 'price' => 4771, 'minDays' => 11, 'maxDays' => 13],
        '41' => ['city' => 'Йошкар-Ола', 'price' => 2877, 'minDays' => 5, 'maxDays' => 6],
        '43' => ['city' => 'Казань', 'price' => 3147, 'minDays' => 5, 'maxDays' => 6],
        '22' => ['city' => 'Калининград', 'price' => 3255, 'minDays' => 10, 'maxDays' => 12],
        '6' => ['city' => 'Калуга', 'price' => 2624, 'minDays' => 4, 'maxDays' => 5],
        '11164' => ['city' => 'Каменск-Уральский', 'price' => 3312, 'minDays' => 9, 'maxDays' => 10],
        '10662' => ['city' => 'Камешково', 'price' => 3228, 'minDays' => 5, 'maxDays' => 6],
        '10895' => ['city' => 'Кандалакша', 'price' => 3866, 'minDays' => 9, 'maxDays' => 11],
        '20669' => ['city' => 'Карпинск', 'price' => 6685, 'minDays' => 13, 'maxDays' => 15],
        '20234' => ['city' => 'Качканар', 'price' => 4128, 'minDays' => 8, 'maxDays' => 10],
        '64' => ['city' => 'Кемерово', 'price' => 4141, 'minDays' => 9, 'maxDays' => 11],
        '10871' => ['city' => 'Кириши', 'price' => 5990, 'minDays' => 5, 'maxDays' => 6],
        '46' => ['city' => 'Киров', 'price' => 2950, 'minDays' => 6, 'maxDays' => 7],
        '10872' => ['city' => 'Кировск', 'price' => 5545, 'minDays' => 5, 'maxDays' => 6],
        '10733' => ['city' => 'Клин', 'price' => 3091, 'minDays' => 4, 'maxDays' => 5],
        '10653' => ['city' => 'Клинцы', 'price' => 3075, 'minDays' => 5, 'maxDays' => 6],
        '20153' => ['city' => 'Ковдор', 'price' => 13464, 'minDays' => 13, 'maxDays' => 15],
        '11180' => ['city' => 'Когалым', 'price' => 6960, 'minDays' => 8, 'maxDays' => 10],
        '10734' => ['city' => 'Коломна', 'price' => 2762, 'minDays' => 4, 'maxDays' => 5],
        '11046' => ['city' => 'Константиновск', 'price' => 7562, 'minDays' => 7, 'maxDays' => 8],
        '10845' => ['city' => 'Коряжма', 'price' => 3327, 'minDays' => 6, 'maxDays' => 7],
        '7' => ['city' => 'Кострома', 'price' => 2657, 'minDays' => 4, 'maxDays' => 5],
        '10846' => ['city' => 'Котлас', 'price' => 3119, 'minDays' => 6, 'maxDays' => 7],
        '20061' => ['city' => 'Красноармейск', 'price' => 5508, 'minDays' => 7, 'maxDays' => 8],
        '35' => ['city' => 'Краснодар', 'price' => 3248, 'minDays' => 5, 'maxDays' => 6],
        '109773' => ['city' => 'Красное Село', 'price' => 3819, 'minDays' => 4, 'maxDays' => 5],
        '62' => ['city' => 'Красноярск', 'price' => 4410, 'minDays' => 10, 'maxDays' => 12],
        '21776' => ['city' => 'Кронштадт', 'price' => 5678, 'minDays' => 5, 'maxDays' => 6],
        '20044' => ['city' => 'Кстово', 'price' => 3381, 'minDays' => 5, 'maxDays' => 6],
        '53' => ['city' => 'Курган', 'price' => 3468, 'minDays' => 7, 'maxDays' => 8],
        '8' => ['city' => 'Курск', 'price' => 2672, 'minDays' => 4, 'maxDays' => 5],
        '11333' => ['city' => 'Кызыл', 'price' => 9560, 'minDays' => 11, 'maxDays' => 13],
        '11210' => ['city' => 'Кыштым', 'price' => 4399, 'minDays' => 8, 'maxDays' => 10],
        '11181' => ['city' => 'Лангепас', 'price' => 5400, 'minDays' => 9, 'maxDays' => 11],
        '20261' => ['city' => 'Лермонтов', 'price' => 3163, 'minDays' => 7, 'maxDays' => 8],
        '37141' => ['city' => 'Ликино-Дулёво', 'price' => 3137, 'minDays' => 5, 'maxDays' => 6],
        '9' => ['city' => 'Липецк', 'price' => 2653, 'minDays' => 4, 'maxDays' => 5],
        '10875' => ['city' => 'Ломоносов', 'price' => 4695, 'minDays' => 3, 'maxDays' => 4],
        '10737' => ['city' => 'Луховицы', 'price' => 3000, 'minDays' => 4, 'maxDays' => 8],
        '79' => ['city' => 'Магадан', 'price' => 13371, 'minDays' => 60, 'maxDays' => 65],
        '235' => ['city' => 'Магнитогорск', 'price' => 3378, 'minDays' => 7, 'maxDays' => 9],
        '1093' => ['city' => 'Майкоп', 'price' => 3300, 'minDays' => 7, 'maxDays' => 7],
        '20715' => ['city' => 'Мелеуз', 'price' => 3950, 'minDays' => 8, 'maxDays' => 9],
        '11212' => ['city' => 'Миасс', 'price' => 3394, 'minDays' => 7, 'maxDays' => 8],
        '11063' => ['city' => 'Минеральные Воды', 'price' => 3288, 'minDays' => 6, 'maxDays' => 7],
        '10739' => ['city' => 'Можайск', 'price' => 2548, 'minDays' => 4, 'maxDays' => 5],
        '10896' => ['city' => 'Мончегорск', 'price' => 3222, 'minDays' => 7, 'maxDays' => 8],
        '23' => ['city' => 'Мурманск', 'price' => 2735, 'minDays' => 7, 'maxDays' => 9],
        '10668' => ['city' => 'Муром', 'price' => 2520, 'minDays' => 3, 'maxDays' => 4],
        '236' => ['city' => 'Набережные Челны', 'price' => 2903, 'minDays' => 5, 'maxDays' => 6],
        '11229' => ['city' => 'Надым', 'price' => 12904, 'minDays' => 14, 'maxDays' => 16],
        '30' => ['city' => 'Нальчик', 'price' => 4384, 'minDays' => 6, 'maxDays' => 7],
        '11437' => ['city' => 'Нерюнгри', 'price' => 9974, 'minDays' => 15, 'maxDays' => 17],
        '11114' => ['city' => 'Нефтекамск', 'price' => 2881, 'minDays' => 7, 'maxDays' => 8],
        '1091' => ['city' => 'Нижневартовск', 'price' => 4284, 'minDays' => 8, 'maxDays' => 10],
        '47' => ['city' => 'Нижний Новгород', 'price' => 2770, 'minDays' => 2, 'maxDays' => 3],
        '11168' => ['city' => 'Нижний Тагил', 'price' => 3346, 'minDays' => 7, 'maxDays' => 8],
        '237' => ['city' => 'Новокузнецк', 'price' => 4153, 'minDays' => 9, 'maxDays' => 11],
        '11135' => ['city' => 'Новокуйбышевск', 'price' => 3170, 'minDays' => 5, 'maxDays' => 6],
        '10830' => ['city' => 'Новомосковск', 'price' => 2624, 'minDays' => 3, 'maxDays' => 4],
        '970' => ['city' => 'Новороссийск', 'price' => 3204, 'minDays' => 6, 'maxDays' => 7],
        '65' => ['city' => 'Новосибирск', 'price' => 4174, 'minDays' => 8, 'maxDays' => 9],
        '11230' => ['city' => 'Новый Уренгой', 'price' => 4779, 'minDays' => 14, 'maxDays' => 17],
        '10742' => ['city' => 'Ногинск', 'price' => 2873, 'minDays' => 4, 'maxDays' => 9],
        '11311' => ['city' => 'Норильск', 'price' => 23120, 'minDays' => 4, 'maxDays' => 5],
        '11231' => ['city' => 'Ноябрьск', 'price' => 4119, 'minDays' => 11, 'maxDays' => 13],
        '11186' => ['city' => 'Нягань', 'price' => 6306, 'minDays' => 11, 'maxDays' => 12],
        '967' => ['city' => 'Обнинск', 'price' => 2616, 'minDays' => 4, 'maxDays' => 5],
        '11317' => ['city' => 'Обь', 'price' => 4145, 'minDays' => 8, 'maxDays' => 8],
        '10744' => ['city' => 'Озеры', 'price' => 2986, 'minDays' => 4, 'maxDays' => 5],
        '66' => ['city' => 'Омск', 'price' => 3765, 'minDays' => 8, 'maxDays' => 9],
        '10' => ['city' => 'Орел', 'price' => 2662, 'minDays' => 4, 'maxDays' => 5],
        '48' => ['city' => 'Оренбург', 'price' => 3366, 'minDays' => 5, 'maxDays' => 6],
        '10745' => ['city' => 'Орехово-Зуево', 'price' => 3283, 'minDays' => 4, 'maxDays' => 5],
        '10818' => ['city' => 'Осташков', 'price' => 5233, 'minDays' => 5, 'maxDays' => 6],
        '49' => ['city' => 'Пенза', 'price' => 3228, 'minDays' => 4, 'maxDays' => 5],
        '50' => ['city' => 'Пермь', 'price' => 3308, 'minDays' => 7, 'maxDays' => 8],
        '98546' => ['city' => 'Петергоф', 'price' => 4422, 'minDays' => 3, 'maxDays' => 4],
        '18' => ['city' => 'Петрозаводск', 'price' => 2979, 'minDays' => 6, 'maxDays' => 7],
        '78' => ['city' => 'Петропавловск-Камчатский', 'price' => 14404, 'minDays' => 21, 'maxDays' => 24],
        '10942' => ['city' => 'Печора', 'price' => 7900, 'minDays' => 12, 'maxDays' => 14],
        '117428' => ['city' => 'Бугры', 'price' => 3858, 'minDays' => 4, 'maxDays' => 5],
        '101344' => ['city' => 'Алексеевское', 'price' => 3537, 'minDays' => 7, 'maxDays' => 8],
        '29384' => ['city' => 'Безенчук', 'price' => 6751, 'minDays' => 7, 'maxDays' => 8],
        '21686' => ['city' => 'Вычегодский', 'price' => 3171, 'minDays' => 6, 'maxDays' => 7],
        '21030' => ['city' => 'Мостовской', 'price' => 7613, 'minDays' => 6, 'maxDays' => 7],
        '101382' => ['city' => 'Пангоды', 'price' => 12904, 'minDays' => 14, 'maxDays' => 16],
        '101583' => ['city' => 'Февральск', 'price' => 21212, 'minDays' => 27, 'maxDays' => 30],
        '20282' => ['city' => 'Левашово', 'price' => 3858, 'minDays' => 3, 'maxDays' => 4],
        '120583' => ['city' => 'Металлострой', 'price' => 4000, 'minDays' => 3, 'maxDays' => 4],
        '101564' => ['city' => 'Репино', 'price' => 5405, 'minDays' => 4, 'maxDays' => 5],
        '116272' => ['city' => 'Сосново', 'price' => 8265, 'minDays' => 4, 'maxDays' => 5],
        '120591' => ['city' => 'Стрельна', 'price' => 3858, 'minDays' => 4, 'maxDays' => 5],
        '11000' => ['city' => 'Приморско-Ахтарск', 'price' => 7668, 'minDays' => 7, 'maxDays' => 8],
        '20174' => ['city' => 'Прохладный', 'price' => 4649, 'minDays' => 6, 'maxDays' => 7],
        '25' => ['city' => 'Псков', 'price' => 2889, 'minDays' => 5, 'maxDays' => 6],
        '10884' => ['city' => 'Пушкин', 'price' => 3244, 'minDays' => 6, 'maxDays' => 7],
        '11188' => ['city' => 'Пыть-Ях', 'price' => 6879, 'minDays' => 7, 'maxDays' => 8],
        '11067' => ['city' => 'Пятигорск', 'price' => 3067, 'minDays' => 6, 'maxDays' => 7],
        '110306' => ['city' => 'Комсомольский', 'price' => 6144, 'minDays' => 8, 'maxDays' => 9],
        '10753' => ['city' => 'Серебряные Пруды', 'price' => 3506, 'minDays' => 5, 'maxDays' => 6],
        '10820' => ['city' => 'Ржев', 'price' => 4321, 'minDays' => 4, 'maxDays' => 5],
        '39' => ['city' => 'Ростов-на-Дону', 'price' => 3175, 'minDays' => 5, 'maxDays' => 6],
        '10839' => ['city' => 'Рыбинск', 'price' => 2797, 'minDays' => 4, 'maxDays' => 5],
        '11' => ['city' => 'Рязань', 'price' => 2791, 'minDays' => 4, 'maxDays' => 5],
        '58' => ['city' => 'Салехард', 'price' => 12133, 'minDays' => 34, 'maxDays' => 40],
        '51' => ['city' => 'Самара', 'price' => 3163, 'minDays' => 5, 'maxDays' => 6],
        '2' => ['city' => 'Санкт-Петербург', 'price' => 3065, 'minDays' => 2, 'maxDays' => 3],
        '42' => ['city' => 'Саранск', 'price' => 2994, 'minDays' => 4, 'maxDays' => 5],
        '194' => ['city' => 'Саратов', 'price' => 3025, 'minDays' => 5, 'maxDays' => 6],
        '11083' => ['city' => 'Саров', 'price' => 3798, 'minDays' => 4, 'maxDays' => 5],
        '11341' => ['city' => 'Саяногорск', 'price' => 5725, 'minDays' => 13, 'maxDays' => 15],
        '11269' => ['city' => 'Саянск', 'price' => 14666, 'minDays' => 13, 'maxDays' => 15],
        '10886' => ['city' => 'Светогорск', 'price' => 5593, 'minDays' => 7, 'maxDays' => 8],
        '10849' => ['city' => 'Северодвинск', 'price' => 3328, 'minDays' => 6, 'maxDays' => 7],
        '121364' => ['city' => 'Зоркальцево', 'price' => 5401, 'minDays' => 12, 'maxDays' => 14],
        '134026' => ['city' => 'Розовка', 'price' => 6274, 'minDays' => 7, 'maxDays' => 8],
        '11172' => ['city' => 'Серов', 'price' => 3136, 'minDays' => 8, 'maxDays' => 9],
        '10754' => ['city' => 'Серпухов', 'price' => 3173, 'minDays' => 4, 'maxDays' => 5],
        '102557' => ['city' => 'Сестрорецк', 'price' => 5405, 'minDays' => 4, 'maxDays' => 5],
        '20704' => ['city' => 'Славянск-на-Кубани', 'price' => 6394, 'minDays' => 6, 'maxDays' => 7],
        '12' => ['city' => 'Смоленск', 'price' => 2822, 'minDays' => 4, 'maxDays' => 5],
        '11456' => ['city' => 'Советская Гавань', 'price' => 9150, 'minDays' => 22, 'maxDays' => 25],
        '11190' => ['city' => 'Советский', 'price' => 6529, 'minDays' => 8, 'maxDays' => 9],
        '36' => ['city' => 'Ставрополь', 'price' => 3235, 'minDays' => 6, 'maxDays' => 7],
        '21129' => ['city' => 'Каневская', 'price' => 6849, 'minDays' => 8, 'maxDays' => 10],
        '100639' => ['city' => 'Северская', 'price' => 3898, 'minDays' => 5, 'maxDays' => 6],
        '10649' => ['city' => 'Старый Оскол', 'price' => 2791, 'minDays' => 5, 'maxDays' => 6],
        '10756' => ['city' => 'Ступино', 'price' => 2271, 'minDays' => 5, 'maxDays' => 6],
        '973' => ['city' => 'Сургут', 'price' => 4217, 'minDays' => 7, 'maxDays' => 9],
        '19' => ['city' => 'Сыктывкар', 'price' => 3083, 'minDays' => 6, 'maxDays' => 7],
        '971' => ['city' => 'Таганрог', 'price' => 2311, 'minDays' => 6, 'maxDays' => 7],
        '13' => ['city' => 'Тамбов', 'price' => 2697, 'minDays' => 4, 'maxDays' => 5],
        '14' => ['city' => 'Тверь', 'price' => 2085, 'minDays' => 3, 'maxDays' => 4],
        '21141' => ['city' => 'Тимашевск', 'price' => 4808, 'minDays' => 5, 'maxDays' => 6],
        '10892' => ['city' => 'Тихвин', 'price' => 7927, 'minDays' => 4, 'maxDays' => 5],
        '11175' => ['city' => 'Тобольск', 'price' => 2759, 'minDays' => 8, 'maxDays' => 9],
        '240' => ['city' => 'Тольятти', 'price' => 3036, 'minDays' => 5, 'maxDays' => 6],
        '67' => ['city' => 'Томск', 'price' => 4153, 'minDays' => 9, 'maxDays' => 11],
        '10893' => ['city' => 'Тосно', 'price' => 5664, 'minDays' => 3, 'maxDays' => 4],
        '1058' => ['city' => 'Туапсе', 'price' => 3668, 'minDays' => 6, 'maxDays' => 7],
        '15' => ['city' => 'Тула', 'price' => 2616, 'minDays' => 4, 'maxDays' => 5],
        '21154' => ['city' => 'Тутаев', 'price' => 3337, 'minDays' => 4, 'maxDays' => 5],
        '55' => ['city' => 'Тюмень', 'price' => 3574, 'minDays' => 6, 'maxDays' => 7],
        '198' => ['city' => 'Улан-Удэ', 'price' => 5119, 'minDays' => 11, 'maxDays' => 12],
        '195' => ['city' => 'Ульяновск', 'price' => 2873, 'minDays' => 5, 'maxDays' => 6],
        '11426' => ['city' => 'Уссурийск', 'price' => 4713, 'minDays' => 25, 'maxDays' => 28],
        '172' => ['city' => 'Уфа', 'price' => 3328, 'minDays' => 6, 'maxDays' => 7],
        '10945' => ['city' => 'Ухта', 'price' => 3733, 'minDays' => 8, 'maxDays' => 9],
        '76' => ['city' => 'Хабаровск', 'price' => 5939, 'minDays' => 20, 'maxDays' => 23],
        '57' => ['city' => 'Ханты-Мансийск', 'price' => 4252, 'minDays' => 8, 'maxDays' => 10],
        '11011' => ['city' => 'Хасавюрт', 'price' => 4609, 'minDays' => 8, 'maxDays' => 9],
        '56' => ['city' => 'Челябинск', 'price' => 3554, 'minDays' => 6, 'maxDays' => 7],
        '11274' => ['city' => 'Черемхово', 'price' => 8151, 'minDays' => 14, 'maxDays' => 16],
        '968' => ['city' => 'Череповец', 'price' => 2564, 'minDays' => 5, 'maxDays' => 6],
        '10761' => ['city' => 'Чехов', 'price' => 2021, 'minDays' => 4, 'maxDays' => 5],
        '68' => ['city' => 'Чита', 'price' => 4858, 'minDays' => 12, 'maxDays' => 14],
        '10925' => ['city' => 'Чудово', 'price' => 3977, 'minDays' => 4, 'maxDays' => 5],
        '20690' => ['city' => 'Шали', 'price' => 5014, 'minDays' => 11, 'maxDays' => 13],
        '11053' => ['city' => 'Шахты', 'price' => 2206, 'minDays' => 6, 'maxDays' => 7],
        '21795' => ['city' => 'Шлиссельбург', 'price' => 5591, 'minDays' => 4, 'maxDays' => 5],
        '20078' => ['city' => 'Шумерля', 'price' => 8264, 'minDays' => 7, 'maxDays' => 8],
        '10834' => ['city' => 'Щёкино', 'price' => 2947, 'minDays' => 4, 'maxDays' => 5],
        '11177' => ['city' => 'Югорск', 'price' => 6529, 'minDays' => 8, 'maxDays' => 9],
        '80' => ['city' => 'Южно-Сахалинск', 'price' => 11856, 'minDays' => 22, 'maxDays' => 25],
        '20757' => ['city' => 'Юрьев-Польский', 'price' => 3539, 'minDays' => 5, 'maxDays' => 6],
        '74' => ['city' => 'Якутск', 'price' => 11325, 'minDays' => 15, 'maxDays' => 17],
        '11075' => ['city' => 'Яранск', 'price' => 5191, 'minDays' => 6, 'maxDays' => 7],
    ];
}
