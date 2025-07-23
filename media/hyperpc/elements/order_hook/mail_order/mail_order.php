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

use JBZoo\Utils\Str;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\MailTemplate;
use HYPERPC\Object\Mail\TemplateData;
use HYPERPC\Elements\ElementOrderHook;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Object\Mail\Order\OrderData;
use HYPERPC\Object\Mail\Order\DiscountData;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Object\Mail\Order\Delivery\PickupData;
use HYPERPC\Object\Mail\Order\Position\PositionData;
use HYPERPC\Object\Mail\Order\Position\QuantityData;
use HYPERPC\Object\Mail\Order\Delivery\ShippingData;
use HYPERPC\Object\Order\PositionData as OrderPositionData;

/**
 * Class ElementOrderHookMailOrder
 *
 * @since   2.0
 */
class ElementOrderHookMailOrder extends ElementOrderHook
{

    const FORM_MAIL_SNIPPET = '{form_email}';
    const POSITION_IMAGE_SIZE = 180;

    /**
     * Hook action.
     *
     * @return  void
     */
    public function hook()
    {
        try {
            $this->_getMailer()->send();
        } catch (\Throwable $th) {
            $this->hyper->log(
                $th->getMessage() . ' in ' . $th->getFile() . ' on ' . $th->getLine(),
                Log::ERROR,
                $this->_group . '_' . $this->_type . '/' . date('Y/m/d') . '/errors.php'
            );
        }
    }

    /**
     * Get mailer.
     *
     * @return  MailTemplate
     *
     * @throws  \Exception
     */
    protected function _getMailer($tmpl = 'mail')
    {
        $mailer = new MailTemplate('com_hyperpc.' . $tmpl, Factory::getApplication()->getLanguage()->getTag());

        $templateData = new TemplateData([
            'subject' => $this->_getSubject(),
            'heading' => Text::_('HYPER_ELEMENT_ORDER_HOOK_MAIL_ORDER_MAIL_HEADING'),
            'message' => Text::_('HYPER_ELEMENT_ORDER_HOOK_MAIL_ORDER_MAIL_MESSAGE'),
            'reason' => Text::sprintf(
                'HYPER_ELEMENT_ORDER_HOOK_MAIL_ORDER_MAIL_REASON',
                Factory::getApplication()->getConfig()->get('sitename', 'HYPERPC')
            )
        ]);

        $templateData->order[] = $this->_getOrderData();

        $mailer->addTemplateData($templateData->toArray());

        foreach ($this->_getRecipients() as $recipient) {
            $mailer->addRecipient($recipient);
        }

        return $mailer;
    }

    /**
     * Get order data object
     *
     * @return  OrderData
     */
    protected function _getOrderData(): OrderData
    {
        $order = $this->_getOrderInstance();

        $positionsCount = 0;

        $productsPrice = 0;
        $servicesPrice = 0;
        $totalDiscount = 0;
        $orderTotal = 0;

        $positionEntities = $order->getPositions();

        $orderPositionsData = PositionDataCollection::create($order->positions->getArrayCopy());
        $positions = [];

        foreach ($orderPositionsData as $itemKey => $position) {
            $totalDiscount += $position->price / 100 * $position->discount;
            if (preg_match('/position-\d+-product-\d+-\d+/', $itemKey)) {
                $servicesPrice += $position->price * $position->quantity;
                continue; // Skip services related to product configuration
            }

            $positionsCount += $position->quantity;

            $positionPrice = $position->price * (1 - $position->discount / 100);
            if ($position->type === 'service') {
                $servicesPrice += $positionPrice * $position->quantity;
            } else {
                $productsPrice += $positionPrice * $position->quantity;
            }

            if ($position->type === 'productvariant') {
                // Add service prices to config price
                $pattern = '/position-\d+-product-' . $position->id .  '-' . $position->option_id . '/';
                /** @var OrderPositionData $data  */
                foreach ($orderPositionsData->items() as $key => $data) {
                    if (!preg_match($pattern, $key)) {
                        continue;
                    }

                    $positionPrice += $data->price * (1 - $data->discount / 100);
                }
            }

            $positionEntity = $positionEntities[$itemKey];

            /** @todo write category to position data */
            $productFolder = $positionEntity->getFolder();
            $categoryTitle = $position->type === 'productvariant' ? /** @todo create method getTypedTitle for ProductFolder */
                Text::_('COM_HYPERPC_PRODUCT_TYPE_' . strtoupper($productFolder->getItemsType())) :
                $productFolder->title; /** @todo get a specific title for each languages */

            $imagePath = $this->hyper['helper']['cart']->getItemImage($positionEntity, 0, static::POSITION_IMAGE_SIZE);

            $image = new Image(JPATH_ROOT . DIRECTORY_SEPARATOR . ltrim($imagePath, '/'));
            if ($image->getOrientation() !== Image::ORIENTATION_SQUARE) {
                $newImagePath = preg_replace(
                    '/0x' . static::POSITION_IMAGE_SIZE . '/',
                    static::POSITION_IMAGE_SIZE . 'x' . static::POSITION_IMAGE_SIZE,
                    $imagePath
                );
                $image->crop(static::POSITION_IMAGE_SIZE, static::POSITION_IMAGE_SIZE)->toFile(JPATH_ROOT . DIRECTORY_SEPARATOR . ltrim($newImagePath, '/'));

                $imagePath = $newImagePath;
            }

            $priceVal = $positionPrice * $position->quantity;
            $orderTotal += $priceVal;

            $positionData = new PositionData([
                'title' => $position->name,
                'category' => $categoryTitle,
                'image' => Uri::root() . ltrim($imagePath, '/'),
                'price' => $this->hyper['helper']['money']->get($priceVal)->text()
            ]);

            if ($position->quantity > 1) {
                $positionData->quantity[] = new QuantityData([
                    'value' => (string) $position->quantity,
                    'unitPrice' => $this->hyper['helper']['money']->get($positionPrice)->text()
                ]);
            }

            $positions[] = $positionData;
        }

        $orderData = new OrderData([
            'orderNumber' => (string) $order->id,
            'orderLink' => $order->getAccountViewUrl(true),
            'orderDate' => HTMLHelper::date($order->created_time, Text::_('DATE_FORMAT_LC5')),
            'clientName' => $order->getBuyer(),
            'clientType' => $order->getBuyerType() ?? '-',
            'clientPhone' => $order->getBuyerPhone(),
            'clientEmail' => $order->getBuyerEmail(),
            'payment' => $order->getPayment()?->getMethodName() ?? '-',
            'positionsCount' => Text::plural('COM_HYPERPC_PRODUCT_N_COUNT', $positionsCount),
            'productsPrice' => $this->hyper['helper']['money']->get($productsPrice)->text(),
            'servicesPrice' => $this->hyper['helper']['money']->get($servicesPrice)->text(),
            'orderTotal' => $this->hyper['helper']['money']->get($orderTotal)->text()
        ]);

        $orderData->positions = $positions;

        $discount = $this->hyper['helper']['money']->get($totalDiscount);
        if ($discount->val() > 0) {
            $orderData->discount[] = new DiscountData([
                'value' => $discount->text()
            ]);
        }

        $delivery = $order->getDelivery();

        if ($delivery->isShipping()) {
            $deliveryPrice = $order->getCustomDeliveryPrice();
            $orderData->shipping[] = new ShippingData([
                'deliveryService' => $delivery->getService(),
                'deliveryAddress' => $delivery->getCrmValue(), /** @todo change method name */
                'price' => $deliveryPrice->val() >= 0 ? $order->getCustomDeliveryPrice()->text() : '-'
            ]);

            if ($deliveryPrice->val() <= 0) {
                $orderData->orderTotal .= ' + ' . Text::_('COM_HYPERPC_ORDER_DELIVERY_PRICE');
            } else {
                $orderData->orderTotal = $this->hyper['helper']['money']->get($orderTotal + $deliveryPrice->val())->text();
            }
        } else { // pickup from the store
            $orderData->pickup[] = new PickupData([
                'storeAddress' => $delivery->getStoreId() ?
                    $this->hyper['helper']['store']->getAddress($delivery->getStoreId()) :
                    '-',
                'readyDate' => $delivery->getPickingDateString() ?: '-'
            ]);
        }

        return $orderData;
    }

    /**
     * Get order instance.
     *
     * @return  Order
     */
    protected function _getOrderInstance(): Order
    {
        static $order;
        if (!$order) {
            $order = $this->_getOrder();
        }

        return $order;
    }

    /**
     * Get array of the email recipients.
     *
     * @return  array
     */
    protected function _getRecipients()
    {
        $mails    = [];
        $mailData = explode(',', $this->_config->get('recipient', ''));
        foreach ((array) $mailData as $mail) {
            $mail = Str::clean($mail);
            if ($mail === self::FORM_MAIL_SNIPPET) {
                $mail = $this->_getOrderInstance()->getBuyerEmail();
            }

            $mails[] = $mail;
        }

        return $mails;
    }

    /**
     * Get mail subject.
     *
     * @return  string
     */
    protected function _getSubject(): string
    {
        $order = $this->_getOrderInstance();

        return Text::sprintf('HYPER_ELEMENT_ORDER_HOOK_MAIL_ORDER_MAIL_SUBJECT', $order->getName());
    }
}
