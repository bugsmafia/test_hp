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
 * Class KZCustomDelivery
 *
 * @package     HYPERPC\Delivery
 */
final class KZCustomDelivery extends Delivery
{
    private const CITY_IDENTIFYRE_TYPE = 'geoId';

    private const TARIFF_HYPERPC_COURIER    = 'HYPERPC_COURIER';
    private const TARIFF_YANDEX             = 'YANDEX';
    private const TARIFF_ABT_TRANS          = 'ABT_TRANS';
    private const TARIFF_ALECO              = 'ALECO';
    private const TARIFF_AVIS               = 'AVIS';
    private const TARIFF_DPD                = 'DPD';
    private const TARIFF_JET_LOGISTIC       = 'JET_LOGISTIC';

    private const SAMPLE_DATA = [
        'KZ-ABA' => ['region' => 'Абайская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-AKM' => ['region' => 'Акмолинская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-AKT' => ['region' => 'Актюбинская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-ALA' => ['region' => 'Алматы', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-ALM' => ['region' => 'Алматинская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-AST' => ['region' => 'Астана', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-ATY' => ['region' => 'Атырауская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-BAY' => ['region' => 'Байконур', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-KAR' => ['region' => 'Карагандинская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-KUS' => ['region' => 'Костанайская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-KZY' => ['region' => 'Кызылординская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-MAN' => ['region' => 'Мангистауская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-PAV' => ['region' => 'Павлодарская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-SEV' => ['region' => 'Северо-Казахстанская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-SHY' => ['region' => 'Шымкент', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-VOS' => ['region' => 'Восточно-Казахстанская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-YUZ' => ['region' => 'Туркестанская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-ZAP' => ['region' => 'Западно-Казахстанская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-ZHA' => ['region' => 'Жамбылская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-33'  => ['region' => 'Жетысуская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1],
        'KZ-62'  => ['region' => 'Улытауская область', 'price' => 0, 'minDays' => 1, 'maxDays' => 1]
    ];

    private const TARIFFS_DATA = [
        self::TARIFF_HYPERPC_COURIER => [
            'KZ-ALA' => ['region' => 'Алматы', 'price' => 5000, 'minDays' => 1, 'maxDays' => 1]
        ],

        self::TARIFF_YANDEX => [
            'KZ-ALA' => ['region' => 'Алматы', 'price' => 4000, 'minDays' => 0, 'maxDays' => 0]
        ],

        self::TARIFF_AVIS => [
            'KZ-ABA' => ['region' => 'Абайская область', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-AKM' => ['region' => 'Акмолинская область', 'price' => 10500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-3'],
            'KZ-AKT' => ['region' => 'Актюбинская область', 'price' => 10500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-2'],
            'KZ-ALM' => ['region' => 'Алматинская область', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-AST' => ['region' => 'Астана', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-ATY' => ['region' => 'Атырауская область', 'price' => 8500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-2'],
            'KZ-BAY' => ['region' => 'Байконур', 'price' => 10500, 'minDays' => 10, 'maxDays' => 15, 'zone' => '3'],
            'KZ-KAR' => ['region' => 'Карагандинская область', 'price' => 9500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-3'],
            'KZ-KUS' => ['region' => 'Костанайская область', 'price' => 11500, 'minDays' => 4, 'maxDays' => 18, 'zone' => '1-4'],
            'KZ-KZY' => ['region' => 'Кызылординская область', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-MAN' => ['region' => 'Мангистауская область', 'price' => 8500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-2'],
            'KZ-PAV' => ['region' => 'Павлодарская область', 'price' => 8500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-2'],
            'KZ-SEV' => ['region' => 'Северо-Казахстанская область', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-SHY' => ['region' => 'Шымкент', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-VOS' => ['region' => 'Восточно-Казахстанская область', 'price' => 9500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-3'],
            'KZ-YUZ' => ['region' => 'Туркестанская область', 'price' => 9500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-3'],
            'KZ-ZAP' => ['region' => 'Западно-Казахстанская область', 'price' => 8500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1-2'],
            'KZ-ZHA' => ['region' => 'Жамбылская область', 'price' => 9500, 'minDays' => 10, 'maxDays' => 15, 'zone' => '1-3'],
            'KZ-33'  => ['region' => 'Жетысуская область', 'price' => 8000, 'minDays' => 4, 'maxDays' => 15, 'zone' => '1'],
            'KZ-62'  => ['region' => 'Улытауская область', 'price' => 8500, 'minDays' => 4, 'maxDays' => 15, 'zone' => '2']
        ]
    ];

    /**
     * Get location identifier type
     *
     * @return  string
     */
    public function getCityIdentifireType(): string
    {
        return self::CITY_IDENTIFYRE_TYPE;
    }

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
     */
    public function getDeliveryOptions($cityIdTo, $cityIdFrom, $measurments, $assessedValue, $hyperpcCourier): DeliveryOptions
    {
        $todor = [];

        foreach ($this->getTariffsList() as $tariffKey => $tariffData) {
            if (!key_exists($cityIdTo, $tariffData)) {
                continue;
            }

            if ($tariffKey === self::TARIFF_HYPERPC_COURIER && $hyperpcCourier === self::PARAM_COURIER_EXCEPT) {
                continue;
            }

            if ($tariffKey === self::TARIFF_YANDEX && $measurments->weight > 8) {
                continue;
            }

            $data = [
                'companyName' => $this->getTariffNameByKey($tariffKey),
                'cost' => $this->getPrice($tariffKey, $tariffData[$cityIdTo], $measurments, (int) $assessedValue),
                'minDays' => $tariffData[$cityIdTo]['minDays'],
                'maxDays' => $tariffData[$cityIdTo]['maxDays']
            ];

            if ($tariffKey === self::TARIFF_HYPERPC_COURIER && $hyperpcCourier === self::PARAM_COURIER_ONLY) {
                return DeliveryOptions::fromArray(['pickup' => [], 'todoor' => [$data], 'post' => []]);
            }

            $todor[] = $data;
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
     */
    public function getPickupPointsInfo(array $pickupPointIds, string $geoId): PointDataCollection
    {
        return PointDataCollection::create([]);
    }

    /**
     * Get tarif name by key
     *
     * @return array
     */
    private function getTariffNameByKey(string $tariffKey): string
    {
        switch ($tariffKey) {
            case self::TARIFF_HYPERPC_COURIER:
                return 'Курьер HYPERPC';
            case self::TARIFF_YANDEX:
                return 'Курьер Yandex.Go';
            case self::TARIFF_ABT_TRANS:
                return 'ABT Trans';
            case self::TARIFF_ALECO:
                return 'Aleco';
            case self::TARIFF_AVIS:
                return 'Avis Logistics';
            case self::TARIFF_DPD:
                return 'DPD';
            case self::TARIFF_JET_LOGISTIC:
                return 'Jet Logistic';
            default:
                return 'Курьер ТК';
        }
    }

    /**
     * Get tarifs list
     *
     * @return array
     */
    private function getTariffsList(): array
    {
        return self::TARIFFS_DATA;
    }

    /**
     * Get tariff price
     *
     * @param   string           $tariffKey
     * @param   array            $data tariff data for destination
     * @param   MeasurementsData $measurments
     * @param   int              $assessedValue
     *
     * @return  float
     */
    private function getPrice(string $tariffKey, array $data, MeasurementsData $measurments, int $assessedValue): float
    {
        if ($tariffKey === self::TARIFF_AVIS) {
            $extraWeight = (int) ceil(max($measurments->weight - 10, 0));
            $extraPrice = 0;

            switch ($data['zone']) {
                case '1':
                    $extraPrice += $extraWeight * 260;
                    break;
                case '1-2':
                    $extraPrice += $extraWeight * 295;
                    break;
                case '1-3':
                    $extraPrice += $extraWeight * 380;
                    break;
                case '1-4':
                    $extraPrice += $extraWeight * 1285;
                    break;
                case '2':
                    $extraPrice += $extraWeight * 330;
                    break;
                case '3':
                    $extraPrice += $extraWeight * 550;
                    break;
            }

            $data['price'] += $extraPrice;
        }

        return (float) $data['price'];
    }
}
