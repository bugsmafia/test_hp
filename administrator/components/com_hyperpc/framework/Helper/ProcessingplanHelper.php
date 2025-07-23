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

use Joomla\CMS\Uri\Uri;
use MoySklad\Entity\Group;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\MoySkladHelper;
use HYPERPC\Helper\PositionHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Object\Order\PositionData;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\MoySklad\Entity\Document\PlanItem;
use HYPERPC\ORM\Entity\MoyskladProductVariant;
use HYPERPC\Object\Processingplan\PlanItemData;
use HYPERPC\Object\SavedConfiguration\PartData;
use HYPERPC\Helper\MoyskladProductVariantHelper;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\MoySklad\Entity\Document\ProcessingPlan;
use HYPERPC\Object\Processingplan\PlanItemDataCollection;
use HYPERPC\Object\SavedConfiguration\PartDataCollection;
use HYPERPC\ORM\Entity\Processingplan as HpProcessingplan;

/**
 * Class ProcessingplanHelper
 *
 * @package     HYPERPC\Helper
 *
 * @property    \HyperPcTableProcessingplans $_table
 *
 * @since       2.0
 */
class ProcessingplanHelper extends EntityContext
{

    /**
     * Folder configs from orders uuid
     *
     * @var     string
     *
     * @since   2.0
     */
    protected static $_checkAvailabilityFieldUuid;

    /**
     * Folder configs from orders uuid
     *
     * @var     string
     *
     * @since   2.0
     */
    protected static $_folderConfigsFromOrdersUuid;

    /**
     * Temp property for case itemKey
     *
     * @var     string
     *
     * @since   2.0
     */
    private string $_unpackableCaseItemKey = '';

    /**
     * Temp property for the unpacked parts cost
     *
     * @var     int
     *
     * @since   2.0
     */
    private int $_unpackedTotal = 0;

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
        $table = Table::getInstance('Processingplans');
        $this->setTable($table);

        $params = $this->hyper['params'];

        self::$_checkAvailabilityFieldUuid =
            self::$_checkAvailabilityFieldUuid ??
            $params->get('moysklad_check_availability_field_uuid', '');

        self::$_folderConfigsFromOrdersUuid =
            self::$_folderConfigsFromOrdersUuid ??
            $params->get('moysklad_folder_configs_from_orders_uuid', '');

        parent::initialize();
    }

    /**
     * Get check availability field UUID
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCheckAvailabilityFieldUuid()
    {
        return self::$_checkAvailabilityFieldUuid;
    }

    /**
     * Get check availability link
     *
     * @param   int $id
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCheckAvailabilityLink($id)
    {
        return Uri::root() . "index.php?option=com_hyperpc&view=processingplan&id={$id}&tmpl=component";
    }

    /**
     * Get meta for moysklad group of processingplans from orders
     *
     * @return  Group
     *
     * @since   2.0
     */
    public function getOrderConfigsGroup(): Group
    {
        /** @var MoySkladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];
        return new Group(
            $moyskladHelper->buildEntityMeta('processingplanfolder', self::$_folderConfigsFromOrdersUuid)->toBaseMeta()
        );
    }

    /**
     * Update processingplan by moysklad entity
     *
     * @param   ProcessingPlan $processingplan
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \MoySklad\Util\Exception\ApiClientException
     *
     * @since   2.0
     */
    public function updateByMoyskladEntity(ProcessingPlan $processingplan)
    {
        if ($processingplan->parent->getMeta()->getId() !== self::$_folderConfigsFromOrdersUuid) {
            return;
        }

        $planId = $processingplan->externalCode;
        /** @var HpProcessingplan */
        $hpProcessingPlan = $this->findById($planId);
        if (!$hpProcessingPlan->id) {
            return;
        }

        $oldPlanItems = PlanItemDataCollection::create($hpProcessingPlan->parts->getArrayCopy());
        $newPlanItems = $this->_getMaterials($processingplan);

        // Save processingplan
        $hpProcessingPlan->parts = $newPlanItems->toArray();
        $this->getTable()->save($hpProcessingPlan->toArray());

        $removedPlanItems = $this->_findRemovedPlanItems($oldPlanItems, $newPlanItems);
        $addedPlanItems   = $this->_findAddedPlanItems($oldPlanItems, $newPlanItems);

        /** @var ConfigurationHelper $configurationHelper */
        $configurationHelper = $this->hyper['helper']['configuration'];

        $savedConfiguration = $configurationHelper->findById($planId);
        $partDataCollection = PartDataCollection::create($savedConfiguration->parts->getArrayCopy());

        $partDataCollection = $this->_removeItemsFromConfiguration($removedPlanItems, $partDataCollection);

        /** @var MoyskladProduct */
        $product = $savedConfiguration->getProduct();
        $assemblyKitId = $product->getAssemblyKitId();
        $customizationGroupIds = $product->getCustomizationGroupIds();
        $partDataCollection = $this->_addItemsToConfiguration($addedPlanItems, $partDataCollection, $assemblyKitId, $customizationGroupIds);

        /** @var PositionHelper $positionHelper */
        $positionHelper = $this->hyper['helper']['position'];

        foreach ($newPlanItems->items() as $itemKey => $planItem) {
            if ($partDataCollection->offsetExists($planItem->id)) { // Set quantities from processingplan to configuration
                $partDataCollection->offsetGet($planItem->id)->quantity = $planItem->quantity;
            }

            if (!$addedPlanItems->offsetExists($itemKey)) { // not changed item
                $part = $positionHelper->getByItemKey($itemKey);
                if ($part->params->get('unpack_from_processingplan', false, 'bool')) {
                    // try to unpack
                    if ($planItem->option_id) {
                        $option = $this->hyper['helper']['moyskladVariant']->findById($planItem->option_id);
                        $unpackedCaseItemKey = $option->params->get('unpacked_case_position', '');
                        $unpackedCustomizationItemKey = $option->params->get('unpacked_customization_position', '');
                    } else {
                        $unpackedCaseItemKey = $part->params->get('unpacked_case_position', '');
                        $unpackedCustomizationItemKey = $part->params->get('unpacked_customization_position', '');
                    }

                    $unpackedCase = $positionHelper->getByItemKey($unpackedCaseItemKey);
                    if ($unpackedCase instanceof Position && $unpackedCase->id) {
                        $price = (int) $unpackedCase->getSalePrice()->val();

                        if ($unpackedCaseItemKey !== $itemKey || !$partDataCollection->offsetExists($unpackedCase->id)) { // set data for calculating product varian total
                            $this->_unpackableCaseItemKey = $itemKey;
                            $this->_unpackedTotal += $price * $planItem->quantity;
                        }
                    }

                    $unpackedCustomization = $positionHelper->getByItemKey($unpackedCustomizationItemKey);
                    if ($unpackedCustomization instanceof Position && $unpackedCustomization->id) {
                        $folderId = $unpackedCustomization->getFolderId();

                        $hasAnotherCustomization = false;

                        /** @var PartData $partData */
                        foreach ($partDataCollection->items() as $id => $partData) {
                            if ($partData->group_id === $folderId && $id !== $unpackedCustomization->id) {
                                $hasAnotherCustomization = true;
                                break;
                            }
                        }

                        if (!$hasAnotherCustomization) { // set data for calculating product varian total
                            $price = (int) $unpackedCustomization->getSalePrice()->val();
                            $this->_unpackedTotal += $price * $planItem->quantity;
                        }
                    }
                }
            }
        }

        // calculate configuration new total
        $configurationTotal = 0;
        /** @var PartData $partData */
        foreach ($partDataCollection->items() as $partData) {
            $configurationTotal += $partData->price * $partData->quantity;
        }
        $savedConfiguration->price = $this->hyper['helper']['money']->get($configurationTotal);
        $savedConfiguration->parts = json_encode($partDataCollection->toArray(), JSON_PRETTY_PRINT);

        $configurationHelper->getTable()->save($savedConfiguration->getArray());

        // calculate product variant new total
        $variantTotal = $this->_calculateVariantTotal($newPlanItems, $partDataCollection);

        /** @var MoyskladProductVariantHelper */
        $productVariantHelper = $this->hyper['helper']['moyskladProductVariant'];
        /** @var MoyskladProductVariant */
        $productVariant = $productVariantHelper->findById($planId);
        if ($productVariant->list_price->val() !== $variantTotal) {
            $productVariant->list_price = $this->hyper['helper']['money']->get($variantTotal);
            $productVariant->updateInMoysklad();
            $productVariantHelper->getTable()->save($productVariant->toArray());

            // Update order
            $orderHelper = $this->hyper['helper']['order'];
            $orderId = $savedConfiguration->order_id;
            /** @var Order */
            $order = $orderHelper->findById($orderId);

            $orderPositionDataCollection = PositionDataCollection::create($order->positions->getArrayCopy());
            $positionItemKey = "position-{$productVariant->product_id}-{$productVariant->id}";
            if ($orderPositionDataCollection->offsetExists($positionItemKey)) {
                /** @var PositionData $productPosition */
                $productPosition = $orderPositionDataCollection->offsetGet($positionItemKey);

                if ($productPosition->price !== $variantTotal) {
                    $productPosition->price = $variantTotal;

                    $order->positions = $orderPositionDataCollection->toArray();
                    $order->total = $order->calculateTotal(true);

                    $orderHelper->getTable()->save($order->getArray());

                    $this->hyper['helper']['moyskladCustomerOrder']->updatePositions($order);
                }
            }

            // update index if product in stock
            $stockProducts = $this->hyper['helper']['moyskladStock']->getProductsByConfigurationId($planId);
            foreach ($stockProducts as $stockProduct) {
                $this->hyper['helper']['moyskladFilter']->updateProductIndex($stockProduct);
            }
        }
    }

    /**
     * Get moysklad processingplan materials collection
     *
     * @param   ProcessingPlan $processingplan
     *
     * @return  PlanItemDataCollection
     *
     * @since   2.0
     */
    protected function _getMaterials(ProcessingPlan $processingplan): PlanItemDataCollection
    {
        /** @var PlanItem[] */
        $materials = (array) $processingplan->materials->rows;
        $items = new PlanItemDataCollection();
        foreach ($materials as $planItem) {
            $partData = PlanItemData::fromMoyskladPlanItem($planItem);
            $itemKey = 'position-' . $partData->id . ($partData->option_id ? '-' . $partData->option_id : '');
            if ($items->offsetExists($itemKey)) {
                $items->offsetGet($itemKey)->quantity += $partData->quantity;
            } else {
                $items[$itemKey] = $partData;
            }
        }

        return $items;
    }

    /**
     * Add items to the configuration parts data collection
     *
     * @param   PlanItemDataCollection $planItems
     * @param   PartDataCollection $partsData
     * @param   int $assemblyKitId
     * @param   int[] $customizationGroupIds
     *
     * @return  PartDataCollection
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function _addItemsToConfiguration(PlanItemDataCollection $planItems, PartDataCollection $partsData, $assemblyKitId, $customizationGroupIds): PartDataCollection
    {
        /** @var PositionHelper $positionHelper */
        $positionHelper = $this->hyper['helper']['position'];

        /** @var PlanItemData $planItem */
        foreach ($planItems->items() as $itemKey => $planItem) {
            $part = $positionHelper->getByItemKey($itemKey);
            if ($part instanceof Position && $part->id) {
                if ($part->id === $assemblyKitId) {
                    continue; // skip assembly kit
                }

                if ($part->params->get('unpack_from_processingplan', false, 'bool')) {
                    // try to unpack
                    if ($planItem->option_id) {
                        $option = $this->hyper['helper']['moyskladVariant']->findById($planItem->option_id);
                        $unpackedCaseItemKey = $option->params->get('unpacked_case_position', '');
                        $unpackedCustomizationItemKey = $option->params->get('unpacked_customization_position', '');
                    } else {
                        $unpackedCaseItemKey = $part->params->get('unpacked_case_position', '');
                        $unpackedCustomizationItemKey = $part->params->get('unpacked_customization_position', '');
                    }

                    $unpackedCase = $positionHelper->getByItemKey($unpackedCaseItemKey);
                    if ($unpackedCase instanceof Position && $unpackedCase->id) {
                        $price = (int) $unpackedCase->getSalePrice()->val();

                        if ($unpackedCaseItemKey !== $itemKey) { // set data for calculating product varian total
                            $this->_unpackableCaseItemKey = $itemKey;
                            $this->_unpackedTotal += $price * $planItem->quantity;
                        }

                        $partsData->offsetUnset($planItem->id); // remove original case

                        $partsData->offsetSet($unpackedCase->id, new PartData([ // set unpacked case
                            'id'        => $unpackedCase->id,
                            'price'     => $price,
                            'group_id'  => $unpackedCase->getFolderId(),
                            'quantity'  => $planItem->quantity,
                            'option_id' => ($unpackedCase->get('option')->id ?? null) ?: null
                        ]));
                    }

                    $unpackedCustomization = $positionHelper->getByItemKey($unpackedCustomizationItemKey);
                    if ($unpackedCustomization instanceof Position && $unpackedCustomization->id) {
                        $unpackedCustomization->set('quantity', $planItem->quantity);
                    }
                } else {
                    $folderId = $part->getFolderId();

                    if (in_array($folderId, $customizationGroupIds)) {
                        /** @var PartData $partData */
                        foreach ($partsData->items() as $id => $partData) {
                            if ($partData->group_id === $folderId) { // remove all other customization in the group
                                $partsData->offsetUnset($id);
                            }
                        }
                    }

                    $partsData->offsetSet($planItem->id, new PartData([
                        'id'        => $planItem->id,
                        'price'     => (int) $part->getSalePrice()->val(),
                        'group_id'  => $folderId,
                        'quantity'  => $planItem->quantity,
                        'option_id' => $planItem->option_id
                    ]));
                }
            }
        }

        // Set customization
        if (isset($unpackedCustomization) && $unpackedCustomization instanceof Position && $unpackedCustomization->id) {
            $folderId = $unpackedCustomization->getFolderId();

            $hasAnotherCustomization = false;

            /** @var PartData $partData */
            foreach ($partsData->items() as $id => $partData) {
                if ($partData->group_id === $folderId && $id !== $unpackedCustomization->id) {
                    $hasAnotherCustomization = true;
                    break;
                }
            }

            if (!$hasAnotherCustomization) { // set unpacked customization
                $price = (int) $unpackedCustomization->getSalePrice()->val();
                $quantity = $unpackedCustomization->get('quantity', 1);

                $partsData->offsetSet($unpackedCustomization->id, new PartData([
                    'id'        => $unpackedCustomization->id,
                    'price'     => $price,
                    'group_id'  => $folderId,
                    'quantity'  => $quantity,
                    'option_id' => ($unpackedCustomization->get('option')->id ?? null) ?: null
                ]));

                $this->_unpackedTotal += $price * $quantity;
            }
        }

        return $partsData;
    }

    /**
     * Calculate product variant total
     *
     * @param   PlanItemDataCollection $planItems
     * @param   PartDataCollection $partsData
     *
     * @return  float
     *
     * @since   2.0
     */
    private function _calculateVariantTotal(PlanItemDataCollection $planItems, PartDataCollection $partsData): float
    {
        $total = $this->_unpackedTotal;

        /** @var PlanItemData $planItem */
        foreach ($planItems->items() as $itemKey => $planItem) {
            if ($itemKey === $this->_unpackableCaseItemKey) {
                continue; // skip unpacked case
            }

            if (!$partsData->offsetExists($planItem->id)) {
                continue; // skip non-existent items
            }

            /** @var PartData $partData */
            $partData = $partsData->offsetGet($planItem->id);

            $total += $partData->price * $partData->quantity;
        }

        // reset temp data
        $this->_unpackableCaseItemKey = '';
        $this->_unpackedTotal = 0;

        return (float) $total;
    }

    /**
     * Find added PlanItems
     *
     * @param   PlanItemDataCollection $oldPlanItems
     * @param   PlanItemDataCollection $newPlanItems
     *
     * @return  PlanItemDataCollection
     *
     * @since   2.0
     */
    private function _findAddedPlanItems(PlanItemDataCollection $oldPlanItems, PlanItemDataCollection $newPlanItems): PlanItemDataCollection
    {
        $addedPlanItems = [];
        /** @var PlanItemData $planItem */
        foreach ($newPlanItems->items() as $itemKey => $planItem) {
            if (!$oldPlanItems->offsetExists($itemKey)) {
                $addedPlanItems[$itemKey] = $planItem->toArray();
            }
        }

        return PlanItemDataCollection::create($addedPlanItems);
    }

    /**
     * Find removed PlanItems
     *
     * @param   PlanItemDataCollection $oldPlanItems
     * @param   PlanItemDataCollection $newPlanItems
     *
     * @return  PlanItemDataCollection
     *
     * @since   2.0
     */
    private function _findRemovedPlanItems(PlanItemDataCollection $oldPlanItems, PlanItemDataCollection $newPlanItems): PlanItemDataCollection
    {
        $removedPlanItems = [];
        /** @var PlanItemData $planItem */
        foreach ($oldPlanItems->items() as $itemKey => $planItem) {
            if (!$newPlanItems->offsetExists($itemKey)) {
                $removedPlanItems[$itemKey] = $planItem->toArray();
            }
        }

        return PlanItemDataCollection::create($removedPlanItems);
    }

    /**
     * Remove items from the configuration parts data collection
     *
     * @param   PlanItemDataCollection $planItems
     * @param   PartDataCollection $partsData
     *
     * @return  PartDataCollection
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function _removeItemsFromConfiguration(PlanItemDataCollection $planItems, PartDataCollection $partsData): PartDataCollection
    {
        /** @var PositionHelper $positionHelper */
        $positionHelper = $this->hyper['helper']['position'];

        /** @var PlanItemData $planItem */
        foreach ($planItems->items() as $itemKey => $planItem) {
            $part = $positionHelper->getByItemKey($itemKey);
            if ($part instanceof Position && $part->id) {
                if ($part->params->get('unpack_from_processingplan', false, 'bool')) {
                    // try to unpack
                    if ($planItem->option_id) {
                        $option = $this->hyper['helper']['moyskladVariant']->findById($planItem->option_id);
                        $unpackedCaseItemKey = $option->params->get('unpacked_case_position', '');
                        $unpackedCustomizationItemKey = $option->params->get('unpacked_customization_position', '');
                    } else {
                        $unpackedCaseItemKey = $part->params->get('unpacked_case_position', '');
                        $unpackedCustomizationItemKey = $part->params->get('unpacked_customization_position', '');
                    }

                    $unpackedCase = $positionHelper->getByItemKey($unpackedCaseItemKey);
                    $unpackedCustomization = $positionHelper->getByItemKey($unpackedCustomizationItemKey);

                    // Remove unpacked positions
                    $partsData->offsetUnset($unpackedCase->id ?? 0);
                    $partsData->offsetUnset($unpackedCustomization->id ?? 0);
                }
            }

            // Remove the old part anyway
            $partsData->offsetUnset($planItem->id);
        }

        return $partsData;
    }
}
