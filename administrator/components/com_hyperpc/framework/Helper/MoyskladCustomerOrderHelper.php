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

use Joomla\CMS\Date\Date;
use MoySklad\Entity\State;
use Joomla\CMS\Language\Text;
use MoySklad\Entity\Assortment;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\MoySklad\Entity\Attribute;
use MoySklad\Entity\Agent\Organization;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\ORM\Entity\MoyskladProductVariant;
use MoySklad\Util\Exception\ApiClientException;
use HYPERPC\MoySklad\Entity\Agent\Counterparty;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\MoySklad\Entity\Document\CustomerOrder;
use HYPERPC\MoySklad\Entity\Document\Position\CustomerOrderDocumentPosition;
use HYPERPC\MoySklad\Entity\Document\Position\CustomerOrderDocumentPositions;

/**
 * Class MoyskladCustomerOrderHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladCustomerOrderHelper extends AppHelper
{
    public const PRODUCT_CREATE_MODE_PRODUCT = 'product';
    public const PRODUCT_CREATE_MODE_VARIANT = 'variant';

    protected static $_shippingPositionUuid;

    protected $_amoLeadLinkFieldUuid;
    protected $_amoSyncFieldUuid;
    protected $_daysForShippingMaxFieldUuid;
    protected $_daysForShippingMinFieldUuid;
    protected $_deliveryMethodFieldUuid;
    protected $_managerFieldUuid;
    protected $_paymentFieldUuid;
    protected $_processingplanFieldUuid;
    protected $_productCreateMode;
    protected $_promocodeFieldUuid;
    protected $_shippingServiceFieldUuid;

    /**
     * MoyskladHelper.
     *
     * @var     MoyskladHelper
     *
     * @since   2.0
     */
    protected $_moyskladHelper;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->_moyskladHelper = $this->hyper['helper']['moysklad'];

        $params = $this->hyper['params'];

        $this->_amoLeadLinkFieldUuid = $params->get('moysklad_amo_lead_link_field_uuid', '');
        $this->_amoSyncFieldUuid = $params->get('moysklad_amo_sync_field_uuid', '');
        $this->_daysForShippingMaxFieldUuid = $params->get('moysklad_days_for_shipping_max_field_uuid', '');
        $this->_daysForShippingMinFieldUuid = $params->get('moysklad_days_for_shipping_min_field_uuid', '');
        $this->_deliveryMethodFieldUuid = $params->get('moysklad_delivery_method_field_uuid', '');
        $this->_managerFieldUuid = $params->get('moysklad_manager_field_uuid', '');
        $this->_paymentFieldUuid = $params->get('moysklad_payment_field_uuid', '');
        $this->_processingplanFieldUuid = $params->get('moysklad_processingplan_field_uuid', '');
        $this->_productCreateMode = $params->get('moysklad_order_product_create_mode', self::PRODUCT_CREATE_MODE_VARIANT);
        $this->_promocodeFieldUuid = $params->get('moysklad_promocode_field_uuid', '');
        $this->_shippingServiceFieldUuid = $params->get('moysklad_shipping_service_field_uuid', '');
    }

    /**
     * Get product create mode
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getProductCreationMode()
    {
        return $this->_productCreateMode;
    }

    /**
     * Get shipping position UUID
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getShippingPositionUuid()
    {
        if (self::$_shippingPositionUuid !== null) {
            return self::$_shippingPositionUuid;
        }

        $positionId = $this->hyper['params']->get('moysklad_shipping_position');
        if (empty($positionId)) {
            return self::$_shippingPositionUuid = '';
        }

        $position = $this->hyper['helper']['position']->findById($positionId);

        return self::$_shippingPositionUuid = $position->uuid;
    }

    /**
     * Find certain attribute in a moysklad customer order
     *
     * @param   Attribute[] $attributes
     * @param   string $uuid of the attribute
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findAttributeByUuid(CustomerOrder $customerOrder, $uuid)
    {
        $attributes = (array) $customerOrder->attributes;
        $attribute = array_filter($attributes, function ($item) use ($uuid) {
            /** @var Attribute $item */
            return $item->id === $uuid;
        });

        if (empty($attribute)) {
            return null;
        }

        return $attribute = current($attribute);
    }

    /**
     * Find Amo lead link attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findAmoLeadLinkAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_amoLeadLinkFieldUuid
        );
    }

    /**
     * Find max days for shipping attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findDaysForShippingMaxAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_daysForShippingMaxFieldUuid
        );
    }

    /**
     * Find min days for shipping attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findDaysForShippingMinAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_daysForShippingMinFieldUuid
        );
    }

    /**
     * Find delivery method attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findDeliveryMethodAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_deliveryMethodFieldUuid
        );
    }

    /**
     * Find payment attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findPaymentAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_paymentFieldUuid
        );
    }

    /**
     * Find promocode attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findPromocodeAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_promocodeFieldUuid
        );
    }

    /**
     * Find shipping service attribute in a moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Attribute|null
     *
     * @since   2.0
     */
    public function findShippingServiceAttribute(CustomerOrder $customerOrder)
    {
        return $this->findAttributeByUuid(
            $customerOrder,
            $this->_shippingServiceFieldUuid
        );
    }

    /**
     * Conver order entity to moysklad CustomerOrder
     *
     * @param   Order $order
     *
     * @return  CustomerOrder
     *
     * @throws  ApiClientException
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function orderToCustomerOrder(Order $order): CustomerOrder
    {
        $meta = null;
        $uuid = $order->getUuid();
        if (!empty($uuid)) {
            $meta = $this->_moyskladHelper->buildEntityMeta(
                'customerorder',
                $uuid
            )->toBaseMeta();
        }

        $customerOrder = new CustomerOrder($meta);
        $customerOrder->organization = new Organization($this->_moyskladHelper->getOrganizationMeta()->toBaseMeta());

        $customerOrder->externalCode = $order->id;
        $customerOrder->name = $this->_getOrderName($order);
        $customerOrder->vatEnabled = true;
        $customerOrder->vatIncluded = true;
        $customerOrder->description = $order->elements->find('comment.value', '');

        $this->_setAgent($customerOrder, $order);
        $this->_setPositions($customerOrder, $order);
        $this->_setPayments($customerOrder, $order);
        $this->_setDelivery($customerOrder, $order);
        $this->_setPromocode($customerOrder, $order);
        $this->_setState($customerOrder, $order);

        return $customerOrder;
    }

    /**
     * Set amo sync fields
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @since   2.0
     */
    public function setAmoSyncFields(CustomerOrder $customerOrder, Order $order)
    {
        $amoLink = $order->getAmoLeadUrl();

        if ($amoLink) {
            $customerOrder->attributes[] = $this->_buildAttribute(
                $this->_amoSyncFieldUuid,
                'boolean',
                true
            );

            $customerOrder->attributes[] = $this->_buildAttribute(
                $this->_amoLeadLinkFieldUuid,
                'link',
                $amoLink
            );
        }
    }

    /**
     * Update manager field in Moysklad customer order
     *
     * @param   string $orderUuid
     * @param   string $managerName
     *
     * @throws  ApiClientException
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function updateManagerField($orderUuid, $managerName)
    {
        $customerOrder = $this->_getCustomerOrderInstance($orderUuid);

        $customerOrder->attributes[] = $this->_buildAttribute(
            $this->_managerFieldUuid,
            'string',
            $managerName
        );

        $this->_moyskladHelper->updateCustomerorder($customerOrder);
    }

    /**
     * Update positions in Moysklad customer order
     *
     * @param   Order $order
     *
     * @throws  ApiClientException
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function updatePositions(Order $order)
    {
        $uuid = $order->getUuid();
        if (empty($uuid)) {
            return;
        }

        $customerOrder = $this->_getCustomerOrderInstance($uuid);

        $this->_setPositions($customerOrder, $order);

        $this->_moyskladHelper->updateCustomerorder($customerOrder);
    }

    /**
     * Update state of Moysklad customer order.
     *
     * @param   Order $order
     *
     * @throws  ApiClientException
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function updateState(Order $order)
    {
        $uuid = $order->getUuid();
        if (empty($uuid)) {
            return;
        }

        $customerOrder = $this->_getCustomerOrderInstance($uuid);

        $this->_setState($customerOrder, $order);

        $this->_moyskladHelper->updateCustomerorder($customerOrder);
    }

    /**
     * Build attribute
     *
     * @param   string $uuid custom field uuid
     * @param   'string'|'long'|'time'|'file'|'double'|'boolean'|'customentity' $type
     * @param   mixed $value
     *
     * @return  Attribute
     *
     * @since   2.0
     */
    protected function _buildAttribute($uuid, $type, $value): Attribute
    {
        return $this->_moyskladHelper->buildAttribute('customerorder', $uuid, $type, $value);
    }

    /**
     * Create variant and processingplan for product in MoySklad
     *
     * @param   MoyskladProductVariant $productVariant
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  ApiClientException
     *
     * @since   2.0
     */
    protected function _createProductVariantInMoysklad(MoyskladProductVariant $productVariant)
    {
        $msEntityId = '';
        if ($productVariant->context === self::PRODUCT_CREATE_MODE_PRODUCT) {
            $msProductVariant = $productVariant->toMoyskladProductEntity();
            try {
                $msProductVariant = $this->_moyskladHelper->createProduct($msProductVariant);
            } catch (\Throwable $th) {
                $this->_moyskladHelper->log('Can\'t create product from _createProductVariantInMoysklad: ' . $th->getMessage());
            }

            $msEntityId = $msProductVariant->id;
        } else {
            $productVariant->context = self::PRODUCT_CREATE_MODE_VARIANT;
            $msProductVariant = $productVariant->toMoyskladEntity();
            try {
                $msProductVariant = $this->_moyskladHelper->createVariant($msProductVariant);
            } catch (\Throwable $th) {
                $this->_moyskladHelper->log('Can\'t create variant from _createProductVariantInMoysklad: ' . $th->getMessage());
            }

            $msEntityId = $msProductVariant->id;
        }

        $productVariant->uuid = $msEntityId;
        $this->hyper['helper']['moyskladProductVariant']->getTable()->save($productVariant->toArray());

        /** @var ProcessingplanHelper */
        $processingPlanHelper = $this->hyper['helper']['processingplan'];
        $processingPlan = $processingPlanHelper->findById($productVariant->id);
        if (!$processingPlan->id) {
            /** @todo create processingplan */
        }

        $msProcessingPlan = $processingPlan->toMoyskladEntity(
            $processingPlanHelper->getOrderConfigsGroup()
        );

        $msProcessingPlan->attributes[] = $this->_moyskladHelper->buildAttribute(
            'processingplan',
            $processingPlanHelper->getCheckAvailabilityFieldUuid(),
            'link',
            $processingPlanHelper->getCheckAvailabilityLink($processingPlan->id)
        );

        try {
            $msProcessingPlan = $this->_moyskladHelper->createProcessingplan($msProcessingPlan);
        } catch (\Throwable $th) {
            $this->_moyskladHelper->log('Can\'t create processingplan from _createProductVariantInMoysklad: ' . $th->getMessage());
        }

        $processingPlan->uuid = $msProcessingPlan->id;
        $processingPlanHelper->getTable()->save($processingPlan->toArray());

        // save link to the processingplan in the product
        $msProductVariant->attributes[] = $this->_moyskladHelper->buildAttribute(
            $msProductVariant->getMeta()->type,
            $this->_processingplanFieldUuid,
            'link',
            $processingPlan->getEditUrl()
        );
        try {
            switch ($productVariant->context) {
                case self::PRODUCT_CREATE_MODE_PRODUCT:
                    $this->_moyskladHelper->updateProduct($msProductVariant);
                    break;
                case self::PRODUCT_CREATE_MODE_VARIANT:
                    $this->_moyskladHelper->updateVariant($msProductVariant);
                    break;
            }
        } catch (\Throwable $th) {
            $this->_moyskladHelper->log('Can\'t update product variant from _createProductVariantInMoysklad: ' . $th->getMessage());
        }
    }

    /**
     * Get Customer Order instance
     *
     * @param   string $orderUuid
     *
     * @return  CustomerOrder
     *
     * @since   2.0
     */
    protected function _getCustomerOrderInstance($orderUuid)
    {
        $meta = $this->_moyskladHelper->buildEntityMeta(
            'customerorder',
            $orderUuid
        )->toBaseMeta();

        $customerOrder = new CustomerOrder($meta);

        $customerOrder->applicable = null;
        $customerOrder->shared = null;

        return $customerOrder;
    }

    /**
     * Build customer order name
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getOrderName(Order $order)
    {
        $suffix = 's';
        return $order->id . $suffix;
    }

    /**
     * Set counterparty to order agent field
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setAgent(CustomerOrder $customerOrder, Order $order)
    {
        $user = $order->getCreatedUser();

        $isCompany = $order->isBuyerACompany();
        if (!$isCompany) {
            $uuidValue = $user->getMoyskladUuid();

            if (empty($uuidValue)) {
                $isAutoEmail = $this->hyper['helper']['string']->isAutoEmail($user->email);
                $email = empty($user->id) || $isAutoEmail ? $order->getBuyerEmail() : $user->email;

                $userPhone = $user->getPhone();
                $phoneValue = $userPhone ?: $order->getBuyerPhone();

                $counterparties = $this->_moyskladHelper->getCounterparties([
                    'email' => $email,
                    'phone' => $phoneValue
                ]);

                $counterparties = array_filter($counterparties, function ($counterparty) {
                    return $counterparty->companyType === 'individual';
                });

                if (empty($counterparties)) {
                    $counterparty = new Counterparty();

                    $counterparty->name         = $order->getBuyer();
                    $counterparty->email        = $email;
                    $counterparty->phone        = $phoneValue;
                    $counterparty->externalCode = $user->id;

                    $counterparty->companyType = 'individual';

                    $counterparty = $this->_moyskladHelper->createCounterparty($counterparty);
                    $uuidValue = $counterparty->id;
                } else {
                    $counterparty = array_shift($counterparties);
                    $uuidValue = $counterparty->id;

                    $counterpartyData = join(',', [$counterparty->name, $counterparty->externalCode, $counterparty->email, $counterparty->phone]);
                    $userData = join(',', [$order->getBuyer(), $user->id, $email, $phoneValue]);

                    if ($counterpartyData !== $userData) {
                        $counterparty->attributes = null; //don't affect to the attributes

                        $counterparty->name = $order->getBuyer();
                        $counterparty->externalCode = $user->id;
                        $counterparty->email = $email;
                        $counterparty->phone = $phoneValue;
                        $this->_moyskladHelper->updateCounterparty($counterparty);
                    }
                }

                $user->setFieldValue(
                    $this->hyper['params']->get('user_uuid_field', ''),
                    $uuidValue
                );
            }
        } else { // company
            $inn = $order->getCompanyInn();
            $counterparties = $this->_moyskladHelper->findCounterpartiesByInn($inn);

            $counterparties = array_filter($counterparties, function ($counterparty) {
                return in_array($counterparty->companyType, ['legal', 'entrepreneur']);
            });

            $email = $order->getBuyerEmail();
            $phone = $order->getBuyerPhone();

            if (empty($counterparties)) {
                $counterparty = new Counterparty();
                $counterparty->inn = $inn;

                switch ($order->getBuyerOrderMethod()) {
                    case Order::BUYER_TYPE_ENTREPRENEUR:
                        $counterparty->companyType = 'entrepreneur';
                        break;
                    default:
                        $counterparty->companyType = 'legal';
                        break;
                }

                $counterparty->name = str_replace("{$inn}, ", '', $order->getCompanyName());

                $counterparty->email = $email;
                $counterparty->phone = $phone;

                $counterparty->description = $order->getBuyer();

                $counterparty = $this->_moyskladHelper->createCounterparty($counterparty);
                $uuidValue = $counterparty->id;
            } else {
                $counterparty = array_shift($counterparties);
                $uuidValue = $counterparty->id;

                $counterpartyData = join(',', [$counterparty->email, $counterparty->phone]);
                $orderData = join(',', [$email, $phone]);

                if ($counterpartyData !== $orderData) {
                    $counterparty->attributes = null; //don't affect to the attributes

                    $counterparty->email = $email;
                    $counterparty->phone = $phone;
                    $this->_moyskladHelper->updateCounterparty($counterparty);
                }
            }
        }

        $counterpartyMeta = $this->_moyskladHelper->buildEntityMeta('counterparty', $uuidValue);
        $customerOrder->agent = new Counterparty($counterpartyMeta->toBaseMeta());
    }

    /**
     * Set delivery data
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setDelivery(CustomerOrder $customerOrder, Order $order)
    {
        $element = $order->getDelivery();

        $isShipping = $element->isShipping();

        if ($isShipping) {
            $deliveryMethodName = trim($this->hyper['params']->get('moysklad_delivery_method_shipping_value', ''));

            $customerOrder->attributes[] = $this->_buildAttribute(
                $this->_deliveryMethodFieldUuid,
                'customentity',
                ($deliveryMethodName ?: Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_SHIPPING'))
            );

            $deliveryService = $element->getService();
            if (!empty($deliveryService)) {
                $customerOrder->attributes[] = $this->_buildAttribute($this->_shippingServiceFieldUuid, 'customentity', $deliveryService);
            }

            $daysMinValue = $order->elements->find('yandex_delivery.days_min');
            if ($daysMinValue !== null) {
                $customerOrder->attributes[] = $this->_buildAttribute($this->_daysForShippingMinFieldUuid, 'long', $daysMinValue);
            }

            $daysMaxValue = $order->elements->find('yandex_delivery.days_max');
            if ($daysMaxValue !== null) {
                $customerOrder->attributes[] = $this->_buildAttribute($this->_daysForShippingMaxFieldUuid, 'long', $daysMaxValue);
            }

            $customerOrder->shipmentAddress = $this->hyper['helper']['order']->getDeliveryAddreess($order);
        } else {
            $deliveryMethodName = trim($this->hyper['params']->get('moysklad_delivery_method_pickup_value', ''));

            $customerOrder->attributes[] = $this->_buildAttribute(
                $this->_deliveryMethodFieldUuid,
                'customentity',
                ($deliveryMethodName ?: Text::_('HYPER_ELEMENT_ORDER_YANDEX_DELIVERY_PICKUP'))
            );

            /**
             * @todo add store if needed
             * $storeId = (int) $order->elements->find('yandex_delivery.store');
             */
        }

        $orderReadyDates = $order->getReadyDates();
        if ($orderReadyDates->min) {
            $customerOrder->deliveryPlannedMoment = new Date($orderReadyDates->min->format('Y-m-d H:i:s.v', true));
        }
    }

    /**
     * Set payment value to the moysklad customer order
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setPayments(CustomerOrder $customerOrder, Order $order)
    {
        $elements = $order->getElements();
        foreach ($elements as $element) {
            if (!($element instanceof \ElementOrderPayments)) {
                continue;
            }

            $paymentTypeName = trim($this->hyper['params']->get('moysklad_payment_' . $element->getMethod()->getType() . '_value', ''));

            $customerOrder->attributes[] = $this->_buildAttribute(
                $this->_paymentFieldUuid,
                'customentity',
                ($paymentTypeName ?: $element->getMethod()->getTypeName())
            );

            break;
        }
    }

    /**
     * Set position to the customer order
     *
     * @param   CustomerOrder $customerOrder
     * @param   CustomerOrderDocumentPosition $position
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setPosition(CustomerOrder $customerOrder, CustomerOrderDocumentPosition $position)
    {
        if (!($customerOrder->positions instanceof CustomerOrderDocumentPositions)) {
            $customerOrder->positions = new CustomerOrderDocumentPositions();
        }

        $customerOrder->positions->rows[] = $position;
    }

    /**
     * Set order position
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setPositions(CustomerOrder $customerOrder, Order $order)
    {
        $positions = PositionDataCollection::create((array) $order->positions);
        foreach ($positions as $positionData) {
            $helper = 'position';
            $pk = $positionData->id;

            if (in_array($positionData->type, ['variant', 'productvariant'])) {
                $helper = "moysklad{$positionData->type}";
                $pk = $positionData->option_id;
                if ($positionData->type === 'productvariant' && $this->_productCreateMode === self::PRODUCT_CREATE_MODE_PRODUCT) {
                    $positionData->type = 'product';
                } else {
                    $positionData->type = 'variant';
                }
            }

            if (empty($pk)) {
                continue;
            }

            /** @var Position|MoyskladVariant|MoyskladProductVariant */
            $assortment = $this->hyper['helper'][$helper]->findById($pk);

            if ($assortment instanceof MoyskladProductVariant && empty($assortment->uuid)) {
                $this->_createProductVariantInMoysklad($assortment);
            }

            $orderPosition = new CustomerOrderDocumentPosition();
            $orderPosition->quantity = $positionData->quantity;
            $orderPosition->price = $positionData->price * 100;
            $orderPosition->discount = $positionData->discount;
            $orderPosition->vat = $positionData->vat;
            $orderPosition->assortment = new Assortment(
                $this->_moyskladHelper->buildEntityMeta(
                    $positionData->type,
                    $assortment->uuid
                )
                ->toBaseMeta()
            );

            $this->_setPosition($customerOrder, $orderPosition);
        }

        // Set shipping position
        $delivery = $order->getDelivery();
        if ($delivery->isShipping()) {
            $shippingPrice = $delivery->getPrice()->val();
            if ($shippingPrice >= 0) {
                $shippingPositionUuid = $this->getShippingPositionUuid();

                $shippingPosition = new CustomerOrderDocumentPosition();
                $shippingPosition->quantity = 1;
                $shippingPosition->price = $shippingPrice * 100;
                $shippingPosition->vat = $this->hyper['params']->get('vat', 20, 'int');
                $shippingPosition->assortment = new Assortment($this->_moyskladHelper->buildEntityMeta('service', $shippingPositionUuid)->toBaseMeta());

                $this->_setPosition($customerOrder, $shippingPosition);
            }
        }
    }

    /**
     * Set promocode
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setPromocode(CustomerOrder $customerOrder, Order $order)
    {
        $usedPromocode = $order->promo_code;
        if (empty($usedPromocode)) {
            return;
        }

        $customerOrder->attributes[] = $this->_buildAttribute(
            $this->_promocodeFieldUuid,
            'string',
            $usedPromocode
        );
    }

    /**
     * Set order state
     *
     * @param   CustomerOrder $customerOrder
     * @param   Order $order
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _setState(CustomerOrder $customerOrder, Order $order)
    {
        $statusId = $order->status;
        $status = $this->hyper['helper']['status']->findById($statusId);
        if (!empty($status->id)) {
            $uuid = $status->params->get('moysklad_uuid');
            if (!empty($uuid)) {
                $state = new State($this->_moyskladHelper->buildMeta('state', 'customerorder/metadata/states', $uuid)->toBaseMeta());
                $customerOrder->state = $state;
            }
        }
    }
}
