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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Delivery\Delivery;
use HYPERPC\Delivery\DeliveryFactory;
use HYPERPC\Helper\YandexDeliveryHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerDelivery
 *
 * @since       2.0
 *
 * @property    YandexDeliveryHelper $_helper
 */
class HyperPcControllerDelivery extends ControllerLegacy
{
    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this->_helper = $this->hyper['helper']['yandexDelivery'];

        $this
            ->registerTask('clean-address', 'cleanAddress')
            ->registerTask('get-delivery-options', 'getDeliveryOptions')
            ->registerTask('get-pickup-points-info', 'getPickupPointsInfo');
    }

    /**
     * Get delivery options
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getDeliveryOptions()
    {
        $geoIdTo        = $this->hyper['input']->get('geo_id_to');
        $geoIdFrom      = $this->hyper['input']->get('geo_id_from');
        $assessedValue  = $this->hyper['input']->get('items_sum');
        $hyperpcCourier = $this->hyper['input']->get('hyperpc_courier');

        $measurments = [
            'weight' => $this->hyper['input']->get('weight', 1, 'float'),
            'dimensions' => [
                'width'  => $this->hyper['input']->get('width', 25, 'int'),
                'length' => $this->hyper['input']->get('length', 45, 'int'),
                'height' => $this->hyper['input']->get('height', 55, 'int')
            ]
        ];

        $measurmentsData = MeasurementsData::fromArray($measurments);

        $delivery = $this->_getDeliveryInstance();

        $deliveryList = $delivery->getDeliveryOptions($geoIdTo, $geoIdFrom, $measurmentsData, $assessedValue, $hyperpcCourier);
        $output = [
            'result' => true,
            'time'   => $this->hyper['helper']['date']->getCurrentDateTime()->format('M d Y H:i', true, false),
            'body'   => $deliveryList->toArray()
        ];

        $this->hyper['cms']->close(json_encode($output));
    }

    /**
     * Get pickup points info
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getPickupPointsInfo()
    {
        $pickupPointIds = $this->hyper['input']->get('pickup_point_ids');
        $geoId = $this->hyper['input']->get('geo_id');

        try {
            $delivery         = $this->_getDeliveryInstance();
            $pickupPointsInfo = $delivery->getPickupPointsInfo($pickupPointIds, $geoId);

            $output = [
                'result' => true,
                'body'   => $pickupPointsInfo->toArray()
            ];
        } catch (\Throwable $th) {
            $output = [
                'result' => false,
                'message' => $th->getMessage()
            ];
        }

        $this->hyper['cms']->close(json_encode($output));
    }

    /**
     * Get location suggestions
     *
     * @return  void
     *
     * @deprecated
     *
     * @since   2.0
     */
    public function specifyLocation()
    {
        $requestedString = $this->hyper['input']->get('term', 'default', 'filter');

        $output = ['result' => false];

        try {
            $fullAdress = $this->_helper->getLocalitySuggestions($requestedString);
            $output = [
                'result' => true,
                'body'   => $fullAdress
            ];
        } catch (\Throwable $th) {
            $output = [
                'result' => false,
                'message' => $th->getMessage()
            ];
        }

        $this->hyper['cms']->close(json_encode($output));
    }

    /**
     * Get standardized address
     *
     * @return  void
     *
     * @since   2.0
     */
    public function cleanAddress()
    {
        $result = null;
        if ($this->hyper['params']->get('enable_dadata_api', 0)) {
            $requestedString = $this->hyper['input']->get('address', 'default', 'filter');
            $result = $this->hyper['helper']['dadata']->cleanAddress($requestedString);
        } else {
            $result = 'error: this API is disabled';
        }

        $this->hyper['cms']->close(json_encode($result));
    }

    /**
     * Get delivery object
     *
     * @return  Delivery|void
     *
     * @since   2.0
     */
    protected function _getDeliveryInstance()
    {
        try {
            $deliveryType = $this->hyper['params']->get('delivery_type', 'Yandex');
            return DeliveryFactory::createDelivery($deliveryType);
        } catch (\Exception $th) {
            $this->hyper['cms']->close(json_encode([
                'result' => false,
                'message' => $th->getMessage()
            ]));
        }
    }
}
