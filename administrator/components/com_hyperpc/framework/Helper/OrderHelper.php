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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use JBZoo\Data\Data;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use HYPERPC\Elements\Element;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\DateHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Object\Order\DeliveryData;
use HYPERPC\Object\Order\PositionData;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Elements\Manager as ElementManager;
use HYPERPC\MoySklad\Entity\Document\CustomerOrder;
use HYPERPC\MoySklad\Entity\Document\Position\CustomerOrderDocumentPosition;

/**
 * Class OrderHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class OrderHelper extends EntityContext
{

    const FORM_TYPE_CREDIT = 'credit';

    /**
     * Hold session helper.
     *
     * @var     SessionHelper
     *
     * @since   2.0
     */
    protected $_session;

    /**
     * Get order name by id.
     *
     * @param   int $id
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName($id)
    {
        return $id;
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Orders');
        $this->setTable($table);

        $this->_session = $this->hyper['helper']['session'];
        $this->_session->setNamespace('hpcart');

        parent::initialize();
    }

    /**
     * Get credit element.
     *
     * @param   Order $order
     *
     * @return  Element|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCreditElement(Order $order)
    {
        $eType = $order->getCreditMethod();
        if ($order->isCredit() && $eType) {
            $eConfig = (array) $this->hyper['params']->get('credit.' . $eType);
            return ElementManager::getInstance()->create($eType, 'credit', $eConfig);
        }

        return null;
    }

    /**
     * Get credit order title.
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCreditOrderTitle(Order $order)
    {
        $orderNumber = $order->getName();
        $title = Text::_('COM_HYPERPC_CREDIT_SUBJECT_DEFAULT') . ' ' . Text::_('COM_HYPERPC_NUM') . $orderNumber;

        if ($order->form === HP_ORDER_FORM_CREDIT) {
            if ($order->getCreditMethod() === 'sberbank') {
                $title = Text::_('COM_HYPERPC_CREDIT_SUBJECT_SBERBANK') . ' ' . Text::_('COM_HYPERPC_NUM') . $orderNumber;
            } elseif ($order->getCreditMethod() === 'happylend') {
                $title = Text::_('COM_HYPERPC_CREDIT_SUBJECT_7SECOND') . ' ' . Text::_('COM_HYPERPC_NUM') . $orderNumber;
            }
        }

        return $title;
    }

    /**
     * Get order delivery address
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getDeliveryAddreess(Order $order)
    {
        $elements = $order->elements;

        $addressFromElement = $elements->find('address.value', '', 'strip');
        $pointAddress       = $elements->find('yandex_delivery.pickup_point_address', '', 'strip');
        $buyerAddress       = $elements->find('yandex_delivery.original_address', '', 'strip');

        if (!empty($pointAddress)) {
            return $pointAddress;
        } elseif (!empty($buyerAddress)) {
            return $buyerAddress;
        } elseif (!empty($addressFromElement)) {
            return $addressFromElement;
        }

        return '-';
    }

    /**
     * Get order estimated sending date or delivery date
     *
     * @param   Order $order
     *
     * @return  Data
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getOrderShippingDates(Order $order)
    {
        $delivery = $order->elements->find('yandex_delivery');
        $dateFormat = 'd.m.Y';

        $result = new Data([
            'sending'  => new Data(),
            'delivery' => new Data()
        ]);

        if (isset($delivery['sending_date_min'])) {
            $result->sending->set('heading', Text::_('COM_HYPERPC_ORDER_ESTIMATED_SENDING'));
            $sendingMin = $delivery['sending_date_min'];
            $sendingMax = $delivery['sending_date_max'];
            $sendingDatesStr = date($dateFormat, strtotime($sendingMin));
            if ($sendingMin !== $sendingMax) {
                $sendingDatesStr = date($dateFormat, strtotime($sendingMin)) . ' - ' . date($dateFormat, strtotime($sendingMax));
            }
            $result->sending->set('dates', $sendingDatesStr);

            if (isset($delivery['days_min'])) {
                $result->delivery->set('heading', Text::_('COM_HYPERPC_ORDER_ESTIMATED_DELIVERY'));
                $deliveryDatesStr = '';
                if ($delivery['days_min'] === '') {
                    $deliveryDatesStr = '-';
                } else {
                    $minDays = intval($delivery['days_min']);
                    $maxDays = intval($delivery['days_max']);

                    if ($minDays === $maxDays) {
                        $deliveryDatesStr = date($dateFormat, strtotime($sendingMin . ' +' . $minDays . ' day'));
                        if ($sendingMin !== $sendingMax) {
                            $deliveryDatesStr = date($dateFormat, strtotime($sendingMin . ' +' . $minDays . ' day')) . ' - ' . date($dateFormat, strtotime($sendingMax . ' +' . $minDays . ' day'));
                        }
                    } else {
                        $deliveryDatesStr = date($dateFormat, strtotime($sendingMin . ' +' . $minDays . ' day')) . ' - ' . date($dateFormat, strtotime($sendingMax . ' +' . $maxDays . ' day'));
                    }
                }

                $result->delivery->set('dates', $deliveryDatesStr);
            }
        }

        return $result;
    }

    /**
     * Get shipping days string
     *
     * @param   Order $order
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getOrderShippingDays(Order $order)
    {
        $delivery = $order->elements->find('yandex_delivery');

        if (!isset($delivery['days_min'])) {
            return '';
        }

        $minDays = intval($delivery['days_min']);
        $maxDays = intval($delivery['days_max']);

        if ($minDays === 0 && $maxDays === 0) {
            return '';
        } elseif ($minDays === $maxDays) {
            return Text::sprintf('COM_HYPERPC_DAYS_SHORT', $maxDays);
        }

        return Text::sprintf('COM_HYPERPC_DAYS_SHORT', $minDays . ' - ' . $maxDays);
    }


    /**
     * Get success message after an order has been created.
     *
     * @param   Order  $order
     *
     * @return  string
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getSuccessMessage(Order $order)
    {
        $dataLayerPurchaseEventData = $this->hyper['helper']['google']->getDataLayerPurchaseEventData($order);
        $dataLayerPurchaseHtml      = !empty($dataLayerPurchaseEventData) ?
            '<div class="jsDataLayerPurchase uk-hidden">'.json_encode($dataLayerPurchaseEventData, JSON_UNESCAPED_UNICODE).'</div>' : '';

        $noAuthentication = (int) $order->created_user_id !== (int) Factory::getUser()->id;

        if ($order->isCredit()) {
            return implode(PHP_EOL, [
                '<div class="uk-text-emphasis">',
                    Text::sprintf(
                        'COM_HYPERPC_ORDER_CREDIT_SUCCESS_SAVE_HEADING',
                        '<span>' . Text::_('COM_HYPERPC_NUM') . $order->getName() . '</span>'
                    ),
                '</div>',
                '<div>',
                    Text::sprintf(
                        $noAuthentication ? 'COM_HYPERPC_ORDER_CREDIT_SUCCESS_SAVE_NO_AUTH' : 'COM_HYPERPC_ORDER_CREDIT_SUCCESS_SAVE',
                        Route::_('index.php?option=com_users&view=profile')
                    ),
                '</div>',
                $dataLayerPurchaseHtml
            ]);
        }

        $message = implode(PHP_EOL, [
            '<div class="uk-text-emphasis">',
                Text::sprintf(
                    'COM_HYPERPC_ORDER_SUCCESS_SAVE_HEADING',
                    '<span>' . Text::_('COM_HYPERPC_NUM') . $order->getName() . '</span>'
                ),
            '</div>',
            '<div>',
                Text::sprintf(
                    'COM_HYPERPC_ORDER_SUCCESS_SAVE',
                    sprintf('<span class="uk-text-nowrap uk-text-emphasis">%s</span>', $order->getBuyerPhone())
                ),
            '</div>',
            $dataLayerPurchaseHtml
        ]);

        return $message;
    }

    /**
     * Find order by moysklad uuid
     *
     * @param   string $uuid
     *
     * @return  Order
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function findByUuid($uuid)
    {
        $db = $this->getDbo();

        $query = $db
            ->getQuery(true)
            ->select(['a.*'])
            ->from($db->qn($this->getTable()->getTableName(), 'a'))
            ->where([
                $db->qn('a.params') . ' LIKE ' . $db->q('%"moysklad_uuid": "' . $uuid . '"%')
            ]);

        $class = $this->getTable()->getEntity();
        $order = $db->setQuery($query)->loadAssoc();

        return new $class(is_array($order) ? $order : []);
    }

    /**
     * Recount total order price.
     *
     * @param   Order $order
     *
     * @return  bool|Money
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function recount(Order $order)
    {
        if ($order->id) {
            /** @var Money $total */
            $total    = $this->hyper['helper']['money']->get();
            $products = $order->products->getArrayCopy();
            if (count($products)) {
                foreach ($products as $product) {
                    if (array_key_exists('parts', $product)) {
                        foreach ($product['parts'] as $part) {
                            $price = Filter::float($part['price']) * Filter::int($part['quantity']);
                            $total->add($price);
                        }
                    }
                }
            }

            $parts = $order->parts->getArrayCopy();
            if (count($parts)) {
                foreach ($parts as $part) {
                    $price = Filter::float($part['price']) * Filter::int($part['quantity']);
                    $total->add($price);
                }
            }

            return $total;
        }

        return false;
    }

    /**
     * Get user orders list.
     *
     * @param   int  $userId
     * @param   int  $limit
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getUserOrders($userId, $limit = 5)
    {
        $db = $this->getDbo();

        $_list = $db->setQuery($db
            ->getQuery(true)->select(['a.*'])
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'))
            ->where([
                $db->quoteName('a.created_user_id') . ' = ' . $db->quote($userId),
                $db->quoteName('a.context')         . ' = ' . $db->quote($this->hyper['params']->get('site_context'))
            ])
            ->order($db->quoteName('a.id') . ' DESC')
            ->setLimit($limit))
            ->loadAssocList('id', $this->getTable()->getEntity());

        $class = $this->getTable()->getEntity();
        $list  = [];
        foreach ($_list as $id => $item) {
            $list[$id] = new $class($item);
        }

        return $list;
    }

    /**
     * Reassign all orders between users
     *
     * @param  string $oldUserId
     * @param  string $userId
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function reassignUser(string $oldUserId, string $userId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName($this->getTable()->getTableName()))
            ->set([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($userId),
            ])
            ->where([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($oldUserId),
                $db->quoteName('context') . ' = ' . $db->quote($this->hyper['params']->get('site_context')),
            ]);

        return $db->setQuery($query)->execute();
    }

    /**
     * Setup session form data.
     *
     * @param   $key
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function setSession($key)
    {
        $jForm = $this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array');
        if (array_key_exists('elements', $jForm)) {
            $this->_session->set($key, $jForm['elements']);
        }
    }

    /**
     * Write order log.
     *
     * @param   int     $orderId
     * @param   string  $type
     * @param   string  $content
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public static function writeLog($orderId, $type, $content = '')
    {
        $type    = Str::low($type);
        $orderId = Filter::int($orderId);
        $table   = Table::getInstance('Order_Logs');

        return $table->save([
            'order_id' => $orderId,
            'type'     => $type,
            'content'  => $content
        ]);
    }

    /**
     * Update order by moysklad customerOrder entity
     *
     * @param   CustomerOrder $customerOrder
     *
     * @return  Order updated order
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function updateByMoyskladEntity(CustomerOrder $customerOrder): Order
    {
        $externalCode = $customerOrder->externalCode;

        if (!is_numeric($externalCode)) {
            throw new \Exception(__FUNCTION__ . ': Invalid cusomerorder externalCode value: ' . $externalCode);
        }

        /** @var Order */
        $order = $this->findById($externalCode);

        if (!$order->id) {
            throw new \Exception(__FUNCTION__ . ': Can\'t find order with id ' . $externalCode);
        }

        $order->params->set('moysklad_uuid', $customerOrder->getMeta()->getId());

        /** @var MoyskladCustomerOrderHelper */
        $customerOrderHelper = $this->hyper['helper']['moyskladCustomerOrder'];

        /** @var CustomerOrderDocumentPosition[] */
        $customerOrderPositions = $customerOrder->positions->rows;

        $orderPositions = [];
        $parentProduct = null;
        $positionKeys = [];
        foreach ($customerOrderPositions as $customerOrderPosition) {
            if ($customerOrderPosition->assortment->getMeta()->getId() === $customerOrderHelper->getShippingPositionUuid()) {
                continue;
            }

            $positionData = PositionData::fromMoyskladOrderPosition($customerOrderPosition);

            if ($positionData->type === 'productvariant') { // related product for services
                $parentProduct = $positionData;
            } elseif ($positionData->type !== 'service') {
                $parentProduct = null;
            }

            $itemKey = 'position-' . $positionData->id . ($positionData->option_id ? '-' . $positionData->option_id : '');

            if ($positionData->type === 'service' && $parentProduct) {
                $itemKey .= '-product-' . $parentProduct->id . '-' . $parentProduct->option_id;
            }

            $positionKey = $itemKey;
            if (array_key_exists($positionKey, $positionKeys)) {
                $positionKey .= '-' . ++$positionKeys[$itemKey];
            } else {
                $itemKeys[$positionKey] = 1;
            }

            $orderPositions[$positionKey] = $positionData->toArray();
        }

        $order->positions = new JSON($orderPositions);

        $order->total = $order->calculateTotal();

        // Set status
        if ($this->hyper['params']->get('moysklad_sync_order_statuses', false, 'bool')) {
            $stateUuid = $customerOrder->state->getMeta()->getId();

            /** @var StatusHelper $statusHelper */
            $statusHelper = $this->hyper['helper']['status'];
            $relatedStatuses = $statusHelper->findByUuid($stateUuid);

            if (!empty($relatedStatuses) && !key_exists($order->status, $relatedStatuses)) { // status changed
                $siteStatus = array_shift($relatedStatuses);
                $order->setStatus($siteStatus);

                $crmLeadId = $order->getAmoLeadId();
                if (!empty($crmLeadId) && !empty($siteStatus->pipeline_id) && !empty($siteStatus->getAmoStatusId())) {
                    $this->hyper['helper']['crm']->updateLead([[
                        'id'            => $crmLeadId,
                        'updated_at'    => time(),
                        'pipeline_id'   => $siteStatus->pipeline_id,
                        'status_id'     => $siteStatus->getAmoStatusId(),
                        'tags'          => false
                    ]]);
                }
            }
        }

        $elements = $order->elements;

        // set payments
        $paymentAttribute = $customerOrderHelper->findPaymentAttribute($customerOrder);
        if (!empty($paymentAttribute)) {
            $elementGroups = ElementManager::getInstance()->getElementsByGroups(ElementManager::ELEMENT_TYPE_PAYMENT, true);
            $paymentElements = $elementGroups[ElementManager::ELEMENT_TYPE_PAYMENT] ?? [];
            foreach ($paymentElements as $paymentType => $element) {
                $paymentTextValue = $this->hyper['params']->get('moysklad_payment_' . $element->getType() . '_value');
                if ($paymentTextValue === $paymentAttribute->value->name) {
                    $order->payment_type = $paymentType;
                    $elements->set('payments', ['value' => $paymentType]);
                    break;
                }
            }
        }

        // set shipping
        $deliveryData = DeliveryData::fromArray((array) $elements->get('yandex_delivery', []));

        $deliveryMethodAttribute = $customerOrderHelper->findDeliveryMethodAttribute($customerOrder);
        if ($deliveryMethodAttribute) {
            switch ($deliveryMethodAttribute->value->name) {
                case $this->hyper['params']->get('moysklad_delivery_method_shipping_value'):
                    $deliveryData->need_shipping = '1';
                    break;
                case $this->hyper['params']->get('moysklad_delivery_method_pickup_value'):
                    $deliveryData->need_shipping = '0';
                    $deliveryData->store = 1;
                    break;
            }

            /** @todo define store if needed */
        }

        $needShipping = (bool) $deliveryData->need_shipping;

        $deliveryServiceAttribute = $customerOrderHelper->findShippingServiceAttribute($customerOrder);
        $deliveryData->delivery_service = $deliveryServiceAttribute ? $deliveryServiceAttribute->value->name : '';

        if ($this->getDeliveryAddreess($order) !== $customerOrder->shipmentAddress) {
            $deliveryData->user_address_input = '';
            $deliveryData->fias_id = '';

            $deliveryData->granular_address_block_name = null;
            $deliveryData->granular_address_block_type = null;
            $deliveryData->granular_address_flat_name = null;
            $deliveryData->granular_address_flat_type = null;
            $deliveryData->granular_address_house_name = null;
            $deliveryData->granular_address_house_type = null;
            $deliveryData->granular_address_locality = null;
            $deliveryData->granular_address_postal_code = null;
            $deliveryData->granular_address_street_name = null;
            $deliveryData->granular_address_street_type = null;

            $deliveryData->pickup_point_address = '';
            $deliveryData->original_address = '';
            if (strpos($deliveryData->delivery_service, Text::_('COM_HYPERPC_PICKUP_POINT'))) {
                $deliveryData->pickup_point_address = $customerOrder->shipmentAddress;
            } else {
                $deliveryData->original_address = $customerOrder->shipmentAddress;
            }
        }

        /** @todo set store */

        $deliveryPositions = array_filter($customerOrder->positions->rows, function ($documentPosition) use ($customerOrderHelper) {
            return $documentPosition->assortment->getMeta()->getId() === $customerOrderHelper->getShippingPositionUuid();
        });
        $deliveryPosition = array_shift($deliveryPositions);

        if ($deliveryPosition instanceof CustomerOrderDocumentPosition) {
            $shippingCost = $deliveryPosition->price / 100;
            $shippingDiscount = min($deliveryPosition->discount, 100);
            $deliveryData->shipping_cost = (int) ($shippingCost * ((100 - $shippingDiscount) / 100));
        } else {
            $deliveryData->shipping_cost = $needShipping ? -1 : null;
        }

        $plannedReadyDate = $customerOrder->deliveryPlannedMoment;
        if ($plannedReadyDate instanceof \DateTime) {
            $plannedDate = Date::getInstance(
                $plannedReadyDate->getTimestamp(),
                $this->hyper['helper']['date']->getServerTimeZone()
            );

            $orderReadyDates = $order->getReadyDates();
            $dateMin = $orderReadyDates->min;
            $dateMax = $orderReadyDates->max;

            if (($dateMin === null || $dateMax === null) || $dateMin->format(...DateHelper::INTERNAL_FORMAT_ARGS) !== $plannedDate->format(...DateHelper::INTERNAL_FORMAT_ARGS)) {
                $dateMin = $plannedDate;
                $dateMax = $plannedDate;
            }

            $dateMinStr = $dateMin->format(...DateHelper::INTERNAL_FORMAT_ARGS);
            $dateMaxStr = $dateMax->format(...DateHelper::INTERNAL_FORMAT_ARGS);

            if ($needShipping) {
                $deliveryData->sending_date_min = $dateMinStr;
                $deliveryData->sending_date_max = $dateMaxStr;
            } else {
                $deliveryData->store_pickup_dates = $dateMinStr .
                    ($dateMaxStr !== $dateMinStr ? ' - ' . $dateMaxStr : '');
            }
        }

        $daysMinAttribute = $customerOrderHelper->findDaysForShippingMinAttribute($customerOrder);
        $deliveryData->days_min = $daysMinAttribute ? $daysMinAttribute->value : null;
        $daysMaxAttribute = $customerOrderHelper->findDaysForShippingMaxAttribute($customerOrder);
        $deliveryData->days_max = $daysMinAttribute ? $daysMaxAttribute->value : null;

        if ($needShipping) {
            $elements->set('yandex_delivery', $deliveryData->except('store', 'store_pickup_dates')->toArray());
        } else {
            $elements->set('yandex_delivery', $deliveryData->only(
                'need_shipping',
                'store',
                'store_pickup_dates',
                'parcel_dimentions_length',
                'parcel_dimentions_width',
                'parcel_dimentions_height',
                'parcel_weight'
            )->toArray());
        }

        $order->elements = $elements;

        // Set promocode
        $promocodeAttribute = $customerOrderHelper->findPromocodeAttribute($customerOrder);
        if ($promocodeAttribute) {
            $order->promo_code = $promocodeAttribute->value;
        }

        /** @todo update counterpary */

        $this->getTable()->save($order->getArray());
        $savedOrder = $this->findById($order->id, ['new' => true]);

        // Update in CRM
        /** @var \ElementOrderHookAmoCrm $elementAmoCrm */
        $elementAmoCrm = ElementManager::getInstance()->getElement(ElementManager::ELEMENT_POS_ORDER_AFTER_SAVE, 'amo_crm');
        if ($elementAmoCrm instanceof Element) {
            $elementAmoCrm->updateLeadByOrderData($savedOrder);
        }

        return $savedOrder;
    }
}
