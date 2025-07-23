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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Element;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Helper\DateHelper;
use HYPERPC\Delivery\DeliveryFactory;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class ElementOrderYandexDelivery
 *
 * @since   2.0
 */
class ElementOrderYandexDelivery extends Element
{

    const AMO_FIELD_DELIVERY_SERVICE                = 661101;
    const AMO_FIELD_DELIVERY_PICKUP_POINT_ADDRESS   = 662081;
    const AMO_FIELD_DELIVERY_PRICE                  = 661103;
    const AMO_FIELD_DELIVERY_DATE_MIN               = 661105;
    const AMO_FIELD_DELIVERY_DATE_MAX               = 661107;
    const AMO_FIELD_DELIVERY_DAY_MIN                = 661109;
    const AMO_FIELD_DELIVERY_DAY_MAX                = 661111;
    const AMO_FIELD_DELIVERY_CORE_ADDRESS           = 661113;
    const AMO_FIELD_DELIVERY_FULL_ADDRESS           = 661115;
    const AMO_FIELD_DELIVERY_FIAS                   = 661117;
    const AMO_FIELD_DELIVERY_LOCATION_POINT         = 661119;
    const AMO_FIELD_DELIVERY_POST_INDEX             = 661121;
    const AMO_FIELD_DELIVERY_STREET                 = 661125;
    const AMO_FIELD_DELIVERY_HOUSE                  = 661127;
    const AMO_FIELD_DELIVERY_FLAT                   = 661123;
    const AMO_FIELD_DELIVERY_PARCEL_LENGTH          = 661129;
    const AMO_FIELD_DELIVERY_PARCEL_WIDTH           = 661131;
    const AMO_FIELD_DELIVERY_PARCEL_HEIGHT          = 661133;
    const AMO_FIELD_DELIVERY_PARCEL_WEIGHT          = 661135;

    /**
     * Get order picking/sending dates.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getOrderPickingDates()
    {
        return $this->hyper['helper']['cart']->getOrderPickingDates();
    }

    /**
     * Setup amo custom fields from element.
     *
     * @param array $leadData
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setAmoCustomFields(array &$leadData = [])
    {
        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        foreach ((array) $this->_config->get('data') as $key => $value) {
            if ($key === 'shipping_cost') {
                if ($value === '-1') {
                    $value = '-';
                } else {
                    $value = $this->getPrice()->val();
                }
            } elseif (in_array($key, ['days_min', 'days_max']) && !empty($value)) {
                $value = Text::sprintf('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_DAYS', $value);
            } elseif ($key === 'need_shipping') {
                switch ($value) {
                    case '0':
                        $value = $crmHelper->getEnumId(CrmHelper::LEAD_FIELD_DELIVERY_PICKUP_KEY);
                        break;
                    case '1':
                        $value = $crmHelper->getEnumId(CrmHelper::LEAD_FIELD_DELIVERY_SHIPPING_KEY);
                        break;
                    default:
                        $value = null;
                        break;
                }
            }

            if (!empty($value)) {
                $leadData['custom_fields'][] = $this->_addAmoCustomField($key, $value);
            }
        }
    }

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $deliveryType = $this->hyper['params']->get('delivery_type', 'Yandex');
        $delivery     = DeliveryFactory::createDelivery($deliveryType);

        $langTag = $this->hyper->getLanguageCode();
        $langParam = [
            'free' => Text::_('COM_HYPERPC_FOR_FREE'),
            'days' => Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_DAYS'),
            'shipping' => Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHIPPING'),
        ];
        if ($langTag === 'ru-RU') {
            $langParam['methodName'] = [
                'todoor' => Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_DOOR_TO_DOOR'),
                'pickup' => Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP_POINT')
            ];
        }

        $this->hyper['helper']['assets']
            ->js('js:widget/site/geo-yandex-delivery.js')
            ->js('elements:' . $this->_group . '/' . $this->_type . '/assets/js/yandex-delivery-v3.js')
            ->widget('.hp-cart-page, .hp-credit-page', 'HyperPC.Geo.YandexDelivery.Order', [
                'elementIdentifier' => 'jform[elements][' . $this->getIdentifier() . ']',
                'cityIdentifier'    => $delivery->getCityIdentifireType(),
                'connectionInfo'    => $this->hyper['params']->get('connection_info_link', ''),
                'courierInfo'       => $this->hyper['params']->get('courier_info_link', ''),
                'connectionCost'    => $this->hyper['params']->get('connection_cost', 750, 'int'),
                'orderPickingDates' => $this->getOrderPickingDates()->get('stores', []),
                'cashOnDelivery'    => $this->hyper['params']->get('cash_on_delivery', false, 'bool'),
                'langTag'           => $this->hyper->getLanguageCode(),
                'lang'              => $langParam
            ]);
    }

    /**
     * Check is shipping.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isShipping()
    {
        return $this->_config->find('data.need_shipping', false, 'bool');
    }

    /**
     * Get delivery address.
     *
     * @param   bool $isOriginal
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getAddress($isOriginal = true)
    {
        return ($isOriginal) ? $this->_config->find('data.original_address') : $this->_config->find('data.user_address_input');
    }

    /**
     * Get delivery price.
     *
     * @return  \HYPERPC\Money\Type\Money
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getPrice()
    {
        $value = $this->_config->find('data.shipping_cost', -1);
        if ($value === '') {
            $value = -1;
        }
        return $this->hyper['helper']['money']->get($value);
    }

    /**
     * Get delivery service.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getService()
    {
        if ($this->isShipping()) {
            return $this->_config->find('data.delivery_service', '', 'trim');
        }

        return Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP_FROM_SHOP');
    }

    /**
     * Get store id.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getStoreId()
    {
        return $this->_config->find('data.store', 0, 'int');
    }

    /**
     * Get picking pate string value
     */
    public function getPickingDateString()
    {
        $date = $this->_config->find('data.store_pickup_dates');
        if (empty(trim($date))) {
            return '';
        }

        /** @var DateHelper $dateHelper */
        $dateHelper = $this->hyper['helper']['date'];

        return $dateHelper->datesRangeToString($dateHelper->parseString($date), year:true);
    }

    /**
     * Get CRM value.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCrmValue()
    {
        if ($this->isShipping()) {
            $address = $this->getAddress();
            return (!empty($address)) ? $address : $this->_config->find('data.pickup_point_address');
        }

        return Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP');
    }

    /**
     * Render AmoCRM note text.
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function getAmoCrmNoteText()
    {
        return $this->_renderLayout($this->getLayout('amo_note'));
    }

    /**
     * Bind AmoCRM data for order.
     * Used in CrmHelper::updateOrderDeliveryData which is not used anywhere
     *
     * @param   Order $order
     * @param   array $customFields     AmoCRM custom fields list.
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function bindAmoData(Order &$order, array $customFields = [])
    {
        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $data = new JSON((array) $this->_config->get('data'));

        foreach ($customFields as $field) {
            $field = new JSON($field);
            $id    = $field->get('id', 0, 'int');
            $val   = $field->find('values.0.value');
            $enum  = $field->find('values.0.enum', 0, 'int');

            switch ($id) {
                case $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_DELIVERY_KEY):
                    if ($enum === $crmHelper->getEnumId(CrmHelper::LEAD_FIELD_DELIVERY_SHIPPING_KEY)) {
                        $data->set('need_shipping', '1');
                    } elseif ($crmHelper->getEnumId(CrmHelper::LEAD_FIELD_DELIVERY_PICKUP_KEY)) {
                        $data->set('need_shipping', '0');
                    }
                    break;

                case self::AMO_FIELD_DELIVERY_SERVICE:
                    $data->set('delivery_service', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_PRICE:
                    $data->set('shipping_cost', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_DATE_MIN:
                    $data->set('sending_date_min', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_DATE_MAX:
                    $data->set('sending_date_max', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_DAY_MIN:
                    $data->set('days_min', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_DAY_MAX:
                    $data->set('days_max', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_PICKUP_POINT_ADDRESS:
                    $data->set('pickup_point_address', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_CORE_ADDRESS:
                    $data->set('user_address_input', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_FULL_ADDRESS:
                    $data->set('original_address', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_FIAS:
                    $data->set('fias_id', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_LOCATION_POINT:
                    $data->set('granular_address_locality', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_POST_INDEX:
                    $data->set('granular_address_postal_code', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_STREET:
                    $data->set('granular_address_street_name', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_HOUSE:
                    $data->set('granular_address_house_name', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_FLAT:
                    $data->set('granular_address_flat_name', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_PARCEL_LENGTH:
                    $data->set('parcel_dimentions_length', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_PARCEL_WIDTH:
                    $data->set('parcel_dimentions_width', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_PARCEL_HEIGHT:
                    $data->set('parcel_dimentions_height', $val);
                    break;

                case self::AMO_FIELD_DELIVERY_PARCEL_WEIGHT:
                    $data->set('parcel_weight', $val);
                    break;
            }
        }

        $order->elements->set('yandex_delivery', $data->getArrayCopy());
    }


    /**
     * Add amo custom field from element data.
     *
     * @param   string $key
     * @param   string $value
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _addAmoCustomField($key, $value)
    {
        /** @var CrmHelper $crmHelper */
        $crmHelper = $this->hyper['helper']['crm'];

        $amoCustomFieldId = null;

        switch ($key) {
            case 'need_shipping':
                $amoCustomFieldId = $crmHelper->getCustomFieldId(CrmHelper::LEAD_FIELD_DELIVERY_KEY);
                break;

            case 'delivery_service':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_SERVICE;
                break;

            case 'shipping_cost':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_PRICE;
                break;

            case 'sending_date_min':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_DATE_MIN;
                break;

            case 'sending_date_max':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_DATE_MAX;
                break;

            case 'days_min':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_DAY_MIN;
                break;

            case 'days_max':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_DAY_MAX;
                break;

            case 'user_address_input':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_CORE_ADDRESS;
                break;

            case 'pickup_point_address':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_PICKUP_POINT_ADDRESS;
                break;

            case 'original_address':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_FULL_ADDRESS;
                break;

            case 'parcel_dimentions_length':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_PARCEL_LENGTH;
                break;

            case 'parcel_dimentions_width':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_PARCEL_WIDTH;
                break;

            case 'parcel_dimentions_height':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_PARCEL_HEIGHT;
                break;

            case 'parcel_weight':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_PARCEL_WEIGHT;
                break;

            case 'fias_id':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_FIAS;
                break;

            case 'granular_address_locality':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_LOCATION_POINT;
                break;

            case 'granular_address_postal_code':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_POST_INDEX;
                break;

            case 'granular_address_street_name':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_STREET;
                break;

            case 'granular_address_house_name':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_HOUSE;
                break;

            case 'granular_address_flat_name':
                $amoCustomFieldId = self::AMO_FIELD_DELIVERY_FLAT;
                break;
        }

        return [
            'id'     => $amoCustomFieldId,
            'values' => [
                [
                    'value' => $value
                ]
            ]
        ];
    }
}
