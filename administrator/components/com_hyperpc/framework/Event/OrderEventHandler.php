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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Event;

use JBZoo\Data\JSON;
use HYPERPC\Money\Type\Money;
use HYPERPC\Elements\Element;
use HYPERPC\Elements\Manager;
use Joomla\Registry\Registry;
use HYPERPC\ORM\Entity\Processingplan;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\PromoCode;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\ORM\Entity\MoyskladProductVariant;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Object\Processingplan\PlanItemDataCollection;

/**
 * Class OrderEventHandler
 *
 * @package HYPERPC\Event
 *
 * @since   2.0
 */
class OrderEventHandler extends Event
{

    /**
     * Hold element instance.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_elementInstances = [];

    /**
     * On before save front-end order.
     *
     * @param   \HyperPcTableOrders $table
     * @param   bool                $isNew
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public static function onBeforeSave(\HyperPcTableOrders $table, $isNew)
    {
        $app = self::getApp();

        if ($isNew) {
            $order        = self::getOrderEntity($table);
            $positions    = $order->getPositions(true);
            $serviceItems = $app['helper']['cart']->getServiceItems();
            $promoCode    = $app['helper']['promocode']->getSessionData();

            /** @var Money */
            $totalPrice = $app['helper']['money']->get(0);

            if (count($positions)) {
                /** @var Position $position */
                foreach ($positions as $itemKey => $position) {
                    $hasOption = isset($position->option) && $position->option instanceof MoyskladVariant && $position->option->id;

                    $baseDiscount = $position->getDiscount();
                    if ($position instanceof MoyskladProduct) {
                        $price = $position->getConfigPrice()->getClone();

                        $position->setListPrice($price);
                        $position->setSalePrice($price);
                    } else {
                        $price = $position->getListPrice();
                    }

                    $discount = $app['helper']['cart']->getPositionRate($position);
                    $discount = floatval($discount);
                    if (floor($discount) - $discount === 0.999) {
                        $discount = (float) ceil($discount);
                    }

                    $discount = max($baseDiscount, $discount);

                    $hasDiscount = $discount > 0;
                    $hasPromo    = $promoCode->find('positions.' . $position->id) && $discount > $baseDiscount;
                    $isFixedCode = $hasPromo && $promoCode->get('type') === PromoCode::TYPE_SALE_FIXED;

                    $listPrice = $position->getListPrice();
                    $position->setSalePrice(
                        $listPrice->multiply((100 - $discount) / 100, true)
                    );

                    $type = $position->getType();
                    $moyskladType = $type;
                    if ($hasOption) {
                        $moyskladType = 'variant';
                    } elseif ($type === 'part') {
                        $moyskladType = 'product';
                    }

                    if ($position instanceof MoyskladProduct) {
                        $serviceData = (array) $serviceItems->find($itemKey);
                        $countService = count($serviceData);

                        $savedConfiguration = $position->saved_configuration;
                        if (empty($savedConfiguration)) {
                            $defaultPartIds = $position->configuration->get('default', []);
                            $partsQuantity = $position->configuration->get('quantity', []);
                            $defaultPartsOptions = $position->findDefaultPartsOptions();
                            $optionsArray = $defaultPartsOptions->getArrayCopy();
                            /** @var MoyskladVariant $option */
                            foreach ($optionsArray as $option) {
                                if (!in_array((string) $option->part_id, $defaultPartIds)) {
                                    $defaultPartIds[] = (string) $option->part_id;
                                }
                            }

                            $savedConfiguration = $app['helper']['configuration']->save(
                                $position,
                                $defaultPartIds,
                                $defaultPartsOptions,
                                $partsQuantity
                            );

                            $itemKey .= '-' . $savedConfiguration;
                        }

                        $newPositions[$itemKey] = [
                            'option_id' => $savedConfiguration
                        ];
                        $moyskladType = 'productvariant';

                        /** @var SaveConfiguration */
                        $configuration = $app['helper']['configuration']->findById($savedConfiguration);
                        $configurationPositions = $configuration->getParts(true, 'a.product_folder_id ASC');
                        $price = $app['helper']['money']->get(0);
                        $processingPlanParts = [];
                        $caseFolderId = $position->getCaseGroupId();
                        $casePosition = null;
                        $customizationFolderIds = $position->getCustomizationGroupIds();
                        $customizationPositions = [];

                        /** @var MoyskladPart|MoyskladService $configurationPosition */
                        foreach ($configurationPositions as $configurationPosition) {
                            $configurationPositionType = $configurationPosition->getType();

                            if (!$configurationPosition->isDetached()) { // only internal components
                                if ($configurationPositionType === 'service') { // take services out of configuration
                                    $servicePrice = (int) $configurationPosition->getListPrice()->val();

                                    if ($countService && isset($serviceData[$configurationPosition->product_folder_id])) {
                                        $_serviceData = $serviceData[$configurationPosition->product_folder_id];
                                        $configurationPosition = $app['helper']['moyskladService']->findById($_serviceData['id']);

                                        $servicePrice = $_serviceData['price'];
                                    }

                                    $positionKey = "{$configurationPosition->getItemKey()}-product-{$position->id}-{$savedConfiguration}";

                                    $serviceDiscount = 0;
                                    if ($hasDiscount && (!$hasPromo || !$isFixedCode)) {
                                        $serviceDiscount = $discount;
                                    }

                                    $newPositions[$positionKey] = [
                                        'id'           => $configurationPosition->id,
                                        'name'         => $configurationPosition->name,
                                        'price'        => $servicePrice,
                                        'vat'          => $configurationPosition->vat,
                                        'quantity'     => $position->quantity,
                                        'type'         => 'service',
                                        'discount'     => $serviceDiscount
                                    ];

                                    if ($isFixedCode) {
                                        $totalPrice->add($servicePrice * ((100 - $serviceDiscount) / 100) * $position->quantity);
                                    }
                                } else {
                                    $processingPlanParts[$configurationPosition->getItemKey()] = $configurationPosition;

                                    // find case and cusomization positions
                                    $parentId = $configurationPosition->product_folder_id;
                                    if ($parentId === $caseFolderId) {
                                        $casePosition = $configurationPosition;
                                    } elseif (in_array($parentId, $customizationFolderIds)) {
                                        $customizationPositions[] = $configurationPosition;
                                    }

                                    $price->add($configurationPosition->getQuantityPrice(false));
                                }
                            }
                        }

                        /** @var MoyskladPart $part */
                        foreach ($customizationPositions as $part) { // change customization and case positions to one customized case position
                            if (!isset($part->option) || !($part->option instanceof MoyskladVariant) || !$part->option->id) {
                                continue;
                            }

                            $relatedCase = $part->option->getRelatedCase();
                            if ($relatedCase instanceof MoyskladPart) {
                                if (isset($casePosition)) {
                                    unset($processingPlanParts[$casePosition->getItemKey()]);
                                }

                                unset($processingPlanParts[$part->getItemKey()]);

                                $relatedCase->set('quantity', $part->quantity);
                                $processingPlanParts[$relatedCase->getItemKey()] = $relatedCase;
                                break;
                            }
                        }

                        $productVariant = new MoyskladProductVariant([
                            'id' => $savedConfiguration,
                            'context' => $app['helper']['moyskladCustomerOrder']->getProductCreationMode(),
                            'product_id' => $position->id,
                            'list_price' => $price->val(),
                            'name' => str_pad((string) $savedConfiguration, 7, '0', STR_PAD_LEFT)
                        ]);

                        $position->set('name', "{$position->name} ({$productVariant->name})");

                        $app['helper']['moyskladProductVariant']->getTable()->save($productVariant->toArray());

                        $planItemsData = [];
                        /** @var MoyskladPart $part */
                        foreach ($processingPlanParts as $part) {
                            $type = 'product';
                            $option = $part->option;
                            $optionId = null;
                            $name = $part->name;
                            if ($option instanceof MoyskladVariant && $option->id) {
                                $type = 'variant';
                                $optionId = $option->id;
                                $name .= " {$option->name}";
                            }
                            $planItemsData[$part->getItemKey()] = [
                                'id' => $part->id,
                                'option_id' => $optionId,
                                'name' => $name,
                                'quantity' => $part->quantity,
                                'type' => $type
                            ];
                        }

                        // add assembly kit to the processing plan
                        $assemblyKit = $position->getAssemblyKit();
                        if ($assemblyKit instanceof MoyskladPart && $assemblyKit->id) {
                            $planItemsData[$assemblyKit->getItemKey()] = [
                                'id' => $assemblyKit->id,
                                'option_id' => null,
                                'name' => $assemblyKit->name,
                                'quantity' => 1,
                                'type' => 'product'
                            ];
                        }

                        $planItemsData = PlanItemDataCollection::create($planItemsData);

                        $processingPlan = new Processingplan([
                            'id' => $savedConfiguration,
                            'name' => $position->name,
                            'parts' => new JSON($planItemsData->toArray())
                        ]);

                        $app['helper']['processingplan']->getTable()->save($processingPlan->toArray());

                        if ($hasDiscount && $hasPromo && $isFixedCode) {
                            $position->setListPrice($price);
                            $position->setSalePrice($price);

                            $discount = $app['helper']['cart']->getPositionRate($position, true);

                            $discount = floatval($discount);
                            if (floor($discount) - $discount === 0.999) {
                                $discount = (float) ceil($discount);
                            }

                            $position->setSalePrice(
                                $price->multiply((100 - $discount) / 100, true)
                            );
                        }
                    } elseif ($position instanceof MoyskladPart) {
                        if ($hasOption) {
                            $newPositions[$itemKey] = [
                                'option_id' => $position->option->id
                            ];

                            $position->name = $position->getName();
                        }
                    } elseif ($position instanceof MoyskladService && $position->isDetached()) {
                        $lastKey = array_key_last($newPositions);
                        // if items have already added and the last item is a product or a service related to a product,
                        // move the service, which is detached, to the start of the positions array.
                        // Otherwise, it'll be attached to the previous product at the next sync.
                        if ($lastKey && (preg_match('/^position-\d+-product/', $lastKey) || $newPositions[$lastKey]['type'] === 'productvariant')) {
                            $newPositions = array_merge([
                                $itemKey => []
                            ], $newPositions);
                        }
                    }

                    $newPositions[$itemKey] ??= [];

                    $newPositions[$itemKey]['id'] = $position->id;
                    $newPositions[$itemKey]['name'] = $position->name;
                    $newPositions[$itemKey]['price'] = $price->val();
                    $newPositions[$itemKey]['discount'] = $discount;
                    $newPositions[$itemKey]['vat'] = $position->vat;
                    $newPositions[$itemKey]['quantity'] = $position->quantity;
                    $newPositions[$itemKey]['type'] = $moyskladType;

                    /** @var Money $price */
                    $totalPrice->add($position->getSalePrice()->multiply($position->quantity)->val());
                }

                $positionsData = PositionDataCollection::create($newPositions);
                $table->positions = json_encode($positionsData->toArray(), JSON_PRETTY_PRINT);
            }

            $table->total = $totalPrice->val();

            $manager     = Manager::getInstance();
            $position    = ((int) $table->form === HP_ORDER_FORM_CREDIT) ? 'credit_form' : 'order_form';
            $elementList = (array) $manager->getByPosition($position);

            /** @var Element $element */
            foreach ($elementList as $element) {
                if (!in_array($element->getType(), self::$_elementInstances)) {
                    $element->onBeforeSaveItem($table, $isNew);
                    self::$_elementInstances[] = $element->getType();
                }
            }

            // Set YandexMetrica params
            $ymUid = $app['input']->cookie->get('_ym_uid', '');
            $ymCounter = $app['params']->get('ym_counter_id');
            if (!empty($ymUid) && !empty($ymCounter)) {
                $order->setYmCounter($ymCounter);
                $order->setYmUid($ymUid);
            }

            $table->params = $order->params->write();

            // set initial status
            $updatedOrder = self::getOrderEntity($table);
            $initialStatusId = $updatedOrder->getInitialStatusId();
            $table->status = $initialStatusId;
            $table->status_history = json_encode([new Registry([
                'statusId'  => $initialStatusId,
                'timestamp' => time()
            ])], JSON_PRETTY_PRINT);
        }

        //  All ways save order params data from administrator (backend)
        if ($app['cms']->isClient('administrator')) {
            /** @var Order $queryOrder */
            $queryOrder = $app['helper']['order']->findById($table->id);
            if ($queryOrder->id) {
                $table->params = $queryOrder->params->write();
            }
        }
    }

    /**
     * Get order entity object.
     *
     * @param   \HyperPcTableOrders $table
     *
     * @return  Order
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function getOrderEntity(\HyperPcTableOrders $table)
    {
        return new Order([
            'id'                    => $table->id,
            'cid'                   => $table->cid,
            'form'                  => $table->form,
            'total'                 => $table->total,
            'parts'                 => $table->parts,
            'status'                => $table->status,
            'products'              => $table->products,
            'elements'              => $table->elements,
            'positions'             => $table->positions,
            'worker_id'             => $table->worker_id,
            'promo_code'            => $table->promo_code,
            'payment_type'          => $table->payment_type,
            'created_time'          => $table->created_time,
            'delivery_type'         => $table->delivery_type,
            'modified_time'         => $table->modified_time,
            'status_history'        => new JSON($table->status_history),
            'params'                => new JSON($table->params),
            'created_user_id'       => $table->created_user_id,
            'modified_user_id'      => $table->modified_user_id
        ]);
    }
}
