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
use HYPERPC\App;
use HYPERPC\Helper\YandexDeliveryHelper;
use HYPERPC\Object\Delivery\DeliveryOptions;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Object\Delivery\PointDataCollection;

/**
 * Class YandexDelivery
 *
 * @property    YandexDeliveryHelper $_helper
 *
 * @package     HYPERPC\Delivery
 *
 * @since       2.0
 */
class YandexDelivery extends Delivery
{
    private const CITY_IDENTIFYRE_TYPE = 'geoId';

    /**
     * Instance of HYPERPC application.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Hold helper object.
     *
     * @var     YandexDeliveryHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * YandexDelivery constructor.
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function __construct()
    {
        $this->hyper   = App::getInstance();
        $this->_helper = $this->hyper['helper']['YandexDelivery'];
    }

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
     * @param   string|int       $cityIdTo       Yandex geo id
     * @param   string|int       $cityIdFrom     Yandex geo id
     * @param   MeasurementsData $measurments
     * @param   string|int       $assessedValue
     * @param   string           $hyperpcCourier
     *
     * @return  DeliveryOptions
     *
     * @throws  Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getDeliveryOptions($cityIdTo, $cityIdFrom, $measurments, $assessedValue, $hyperpcCourier): DeliveryOptions
    {
        return DeliveryOptions::fromArray((array) $this->_helper->getDeliveryOptions($cityIdTo, $measurments, $cityIdFrom, $assessedValue, $hyperpcCourier));
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
        return PointDataCollection::create($this->_helper->getPickupPointsInfo($pickupPointIds, $geoId));
    }
}
