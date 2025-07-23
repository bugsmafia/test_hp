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

use JBZoo\Data\JSON;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Money\Type\Money;
use MoySklad\Entity\MetaEntity;
use Joomla\CMS\Helper\ModuleHelper;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\Model\ModelAdmin;
use MoySklad\Entity\Product\Product;
use HYPERPC\Helper\Traits\EntitySubtype;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Helper\Traits\MoyskladEntityActions;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HyperPcModelMoysklad_Product as ModelMoyskladProduct;

/**
 * Class MoyskladProductHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladProductHelper extends PositionHelper
{
    use EntitySubtype {
        EntitySubtype::_getFromQuery as _getSubtypeFromQuery;
        EntitySubtype::_getTraitQuery as _getSubtypeQuery;
    }

    use MoyskladEntityActions;

    /**
     * Get query for from condition
     *
     * @return  string
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function _getFromQuery()
    {
        $subtypeQuery = $this->_getSubtypeQuery();
        $parentQuery = parent::_getTraitQuery();

        $parentQuery
            ->clear('from')
            ->from($this->_db->qn($this->_getSupertypeTable()->getTableName(), 'a'));

        $subtypeQuery
            ->clear('join')
            ->join('INNER', "({$parentQuery}) AS t", 'st.id = t.id');
        
        return "({$subtypeQuery}) AS a";
    }

    /**
     * Hold model.
     *
     * @var     ModelMoyskladProduct
     *
     * @since   2.0
     */
    protected static $_model;

    /**
     * Hold buy now form module rendered state
     *
     * @var     boolean
     *
     * @since   2.0
     */
    protected static $_buyNowFormIsRendered = false;

    /**
     * Hold show online form module rendered state
     *
     * @var     boolean
     *
     * @since   2.0
     */
    protected static $_showOnlineFormIsRendered = false;

    /**
     * Hold question form module rendered state
     *
     * @var     boolean
     *
     * @since   2.0
     */
    protected static $_questionFormIsRendered = false;

    /**
     * Hold question modal content rendered state
     *
     * @var     boolean
     *
     * @since   2.0
     */
    protected static $_questionModalContentIsRendered = false;

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
        $table = Table::getInstance('Moysklad_Products');
        $table->setEntity('MoyskladProduct');

        $this->setTable($table);

        $this->_db = $this->_table->getDbo();
    }

    /**
     * Get supertype table name.
     *
     * @return  string
     */
    protected function _getSupertypeTableName(): string
    {
        return 'Positions';
    }

    /**
     * Copies configuration and price fields from the source product to the target product.
     *
     * @param   int $sourceId
     * @param   int $targetId
     *
     * @return  bool
     */
    public function copyConfiguration(int $sourceId, int $targetId): bool
    {
        if ($sourceId < 1 || $targetId < 1 || $sourceId === $targetId) {
            $this->hyper['cms']->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_ITEMID_MISSING'), 'danger');
            return false;
        }

        /** @var MoyskladProduct[] $products */
        $products = $this->findById([$sourceId, $targetId]);

        if (\count($products) !== 2) {
            $this->hyper['cms']->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 'danger');
            return false;
        }

        $source = $products[$sourceId];
        $target = $products[$targetId];

        $target->set('configuration', clone $source->configuration);
        $target->setListPrice($source->getListPrice());
        $target->setSalePrice($source->getSalePrice());

        try {
            $result = $this->getModel()->save($target->getArray());
        } catch (\Throwable $th) {
            $this->hyper['cms']->enqueueMessage($th->getMessage(), 'danger');
            return false;
        }

        return $result;
    }

    /**
     * Count product price.
     *
     * @param   MoyskladProduct $product
     *
     * @return  Money
     *
     * @throws  \RuntimeException
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function countPrice(MoyskladProduct $product)
    {
        /** @var PositionHelper $positionsHelper */
        $positionsHelper = $this->hyper['helper']['position'];

        /** @var Money $totalPrice */
        $totalPrice = $this->hyper['helper']['money']->get(0);
        $defPartIds = (array) $product->configuration->get('default', []);

        //  TODO remove legacy old default saved data.
        if (in_array('on', $defPartIds)) {
            $defPartIds = [];
            foreach ((array) $product->configuration->get('default', []) as $id => $val) {
                $defPartIds[] = (string) $id;
            }
        }

        if (count($defPartIds)) {
            //  Count configuration price by selected parts.
            $positions  = $positionsHelper->getByIds($defPartIds, ['a.*'], 'a.id ASC', 'id', [], false);
            $quantities = $product->configuration->get('quantity', []);
            /** @var Position $position */
            foreach ((array) $positions as $position) {
                $quantity = 1;
                if (array_key_exists($position->id, $quantities)) {
                    $quantity = (int) $quantities[$position->id];
                }

                $priceQuantity = clone $position->list_price;
                $priceQuantity->multiply($quantity);

                $totalPrice->add($priceQuantity->val());
            }
            //  Count configuration price by selected part options.
            $defOptions = (array) $product->configuration->get('option', []);

            if (count($defOptions)) {
                $defOptions = array_values($defOptions);
                foreach ($defOptions as $identifier) {
                    $optionData = $product->configuration->find('part_options.' . $identifier);
                    if ($optionData !== null) {
                        $quantity   = 1;
                        $optionData = new JSON($optionData);
                        $position   = $positionsHelper->getById($optionData->get('part_id'), ['a.*'], [], false);

                        if (array_key_exists($position->id, $quantities)) {
                            $quantity = (int) $quantities[$position->id];
                        }

                        if ($position->getType() === 'part' && $position->id > 0) {
                            $part = $position->getPart();
                            $part->set('quantity', $quantity);

                            $partOptions = $part->getOptions();
                            if (count($partOptions)) {
                                /** @var MoyskladVariant $option */
                                foreach ($partOptions as $option) {
                                    if ((int) $identifier === $option->id) {
                                        $totalPrice->add($option->list_price->val() * $quantity);
                                        break;
                                    }
                                }
                            } else {
                                $totalPrice->add($part->getPrice(false));
                            }
                        } elseif ($part->id > 0) {
                            $totalPrice->add($part->getPrice(false));
                        }
                    }
                }
            }
        }

        return $totalPrice;
    }

    /**
     * Get hyperbox dimensions
     *
     * @param   MoyskladProduct $product
     *
     * @return  MeasurementsData
     *
     * @since   2.0
     */
    public function getHyperboxDimensions(MoyskladProduct $product)
    {
        $boxType = $product->params->get('hyperbox_type', null);

        if (!$boxType) {
            $category = $product->getFolder();
            $boxType  = $category->getHyperboxType();
        }

        return new MeasurementsData($this->hyper['helper']['params']->getHyperboxDimensionsByType($boxType));
    }

    /**
     * Get model.
     *
     * @return  ModelMoyskladProduct
     *
     * @since   2.0
     */
    public function getModel()
    {
        if (!isset(self::$_model)) {
            self::$_model = ModelAdmin::getInstance('Moysklad_Product');
        }
        return self::$_model;
    }

    /**
     * Get mini description for product from specified groups and parts
     *
     * @param   array   $parts [
     *     'groupId' => HYPERPC\Joomla\Model\Entity\Part[]
     * ]
     *
     * @return  array [
     *     'groupTitle' => string[] - part titles
     * ]
     *
     * @since   2.0
     */
    public function getSpecificationWithFieldValues(array $parts)
    {
        $resultParts = [];
        foreach ($parts as $groupId => $groupParts) {
            /** @var ProductFolder */
            $group = $this->hyper['helper']['productFolder']->findById($groupId);
            if (!empty($groupParts)) {
                $platformParts[$group->title] = [];
            }
            foreach ($groupParts as $part) {
                $partName = $part->get('quantity', 1, 'int') > 1 ? $part->quantity . ' x ' : '';

                $fieldForValue = $group->params->get('teaser_table_field');
                if ($fieldForValue && $fieldForValue > 1) {
                    $fieldValue = $part->getFieldValueById($fieldForValue);
                    if (!empty(trim($fieldValue))) {
                        $partName .= $fieldValue;
                        $resultParts[$group->title][] = $partName;
                        continue;
                    }
                }
                $platformParts[$group->title][] = $partName . $part->getName();
            }
        }

        return $resultParts;
    }

    /**
     * Parse item key and create object with their parts
     *
     * @param   string $itemKey
     *
     * @return  Registry
     *
     * @since  2.0
     */
    public function parseItemKey($itemKey)
    {
        $result = new Registry();

        preg_match('/^([a-z]+)-(\d+)-in-stock-(\d+)$/', $itemKey, $stockMatches);
        if (!empty($stockMatches)) {
            $result->set('type', $stockMatches[1]);
            $result->set('product', $stockMatches[2]);
            $result->set('stock', $stockMatches[3]);
            return $result;
        }

        preg_match('/^([a-z]+)-(\d+)-(\d+)$/', $itemKey, $configurationMatches);
        if (!empty($configurationMatches)) {
            $result->set('type', $configurationMatches[1]);
            $result->set('product', $configurationMatches[2]);
            $result->set('configuration', $configurationMatches[3]);
            return $result;
        }

        preg_match('/^([a-z]+)-(\d+)$/', $itemKey, $productMatches);
        if (!empty($productMatches)) {
            $result->set('type', $productMatches[1]);
            $result->set('product', $productMatches[2]);
            return $result;
        }

        return $result;
    }

    /**
     * Get parts list for product teaser
     *
     * @param   MoyskladProduct $product
     * @param   string          $layout
     * @param   bool            $partFormConfig Flag of load parts from saved configuration or product.
     * @param   bool            $loadUnavailableParts Load parts which availability is outofstock or uncontinued.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTeaserParts(MoyskladProduct $product, $layout = 'default', $partFormConfig = false, $loadUnavailableParts = false)
    {
        $result = [];

        $partOrder = $this->hyper['params']->get('product_teaser_parts_order', 'a.product_folder_id ASC');

        $parts = $product->getConfigParts(
            true,
            $partOrder,
            true,
            $partFormConfig,
            $loadUnavailableParts
        );

        $productFolder = $product->getFolder();

        $rootCategory = $this->hyper['params']->get('configurator_root_category', 1);
        $folders    = $this->hyper['helper']['productFolder']->getList(true);

        $model      = ModelList::getInstance('Product_folder');
        $folderTree = $model->buildTree($folders, (int) $rootCategory);

        if ($layout === 'table') {
            /** @var ProductFolder $productFolder */
            $groupsInTable = $productFolder->getGroupsInTeaserTable();
        } elseif ($layout === 'platform') {
            $groupsPlatform = $productFolder->getGroupsPlatform();
        }

        foreach ($folderTree as $parentFolder) {
            if (isset($parentFolder->children)) {
                foreach ($parentFolder->children as $key => $childGroup) {
                    if(isset($parts[$key])) {
                        switch ($layout) {
                            case 'large':
                            case 'complectation':
                                if (!$childGroup->params->get('show_in_teaser_' . $layout, 0)) {
                                    continue 2;
                                }
                                break;
                            case 'table':
                                if (!in_array((string) $childGroup->id, $groupsInTable)) {
                                    continue 2;
                                }
                                break;
                            case 'platform':
                                if (!in_array((string) $childGroup->id, $groupsPlatform)) {
                                    continue 2;
                                }
                                break;
                            default:
                                if (!$childGroup->params->get('show_in_teaser', 0)) {
                                    continue 2;
                                }
                                break;
                        }

                        $result[$key] = $parts[$key];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Render buy now form module
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function renderBuyNowForm()
    {
        if (!self::$_buyNowFormIsRendered) {
            $formModuleId = $this->hyper['params']->get('buy_now_form_module', 0, 'int');

            if ($formModuleId > 0) {
                $module = ModuleHelper::getModuleById((string) $formModuleId);

                if ($module->id && $module->module === 'mod_simpleform2') {
                    self::$_buyNowFormIsRendered = true;
                    return ModuleHelper::renderModule($module);
                } elseif (!$module->id) {
                    throw new \Exception('Can\'t render form module');
                } else {
                    throw new \Exception('Wrong form module type');
                }
            } else {
                throw new \Exception('Form module is not set');
            }
        }

        return '';
    }

    /**
     * Render show online form module
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function renderShowOnlineForm()
    {
        if (!self::$_showOnlineFormIsRendered) {
            $formModuleId = $this->hyper['params']->get('online_show_module', 0, 'int');

            if ($formModuleId > 0) {
                $module = ModuleHelper::getModuleById((string) $formModuleId);

                if ($module->id && $module->module === 'mod_simpleform2') {
                    self::$_showOnlineFormIsRendered = true;
                    return ModuleHelper::renderModule($module);
                } elseif (!$module->id) {
                    throw new \Exception('Can\'t render form module');
                } else {
                    throw new \Exception('Wrong form module type');
                }
            } else {
                throw new \Exception('Form module is not set');
            }
        }

        return '';
    }

    /**
     * Render question form module
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function renderQuestionForm()
    {
        if (!self::$_questionFormIsRendered) {
            $formModulId = $this->hyper['params']->get('product_question_form_module', 0, 'int');

            if ($formModulId > 0) {
                $module = ModuleHelper::getModuleById((string) $formModulId);

                if ($module->id && $module->module === 'mod_simpleform2') {
                    self::$_questionFormIsRendered = true;
                    return ModuleHelper::renderModule($module);
                } elseif (!$module->id) {
                    throw new \Exception('Can\'t render form module');
                } else {
                    throw new \Exception('Wrong form module type');
                }
            } else {
                throw new \Exception('Form module is not set');
            }
        }

        return '';
    }

    /**
     * Render question modal content
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function renderQuestionModalContent()
    {
        if (!self::$_questionModalContentIsRendered) {
            $formModulId = $this->hyper['params']->get('product_question_form_module', 0, 'int');

            if ($formModulId > 0) {
                $module = ModuleHelper::getModuleById((string) $formModulId);

                if ($module->id && $module->module === 'mod_custom') {
                    self::$_questionModalContentIsRendered = true;
                    return ModuleHelper::renderModule($module);
                } elseif (!$module->id) {
                    throw new \Exception('Can\'t render form module');
                } else {
                    throw new \Exception('Wrong form module type');
                }
            } else {
                throw new \Exception('Form module is not set');
            }
        }

        return '';
    }

    /**
     * Prepare data.
     *
     * @param   Product $entity
     *
     * @return  array
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function prepareData(MetaEntity $entity): array
    {
        if (!($entity instanceof Product)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to ' . __METHOD__ . ' must be an instance of ' . Product::class . ', ' . get_class($entity) . ' given'
            );
        }

        return [
            'uuid'              => $entity->id,
            'type_id'           => 3,
            'name'              => $entity->name,
            'product_folder_id' => $this->_getParentFolderId($entity),
            'list_price'        => $this->_getListPriceFromMoyskladEntity($entity),
            'sale_price'        => $this->_getSalePriceFromMoyskladEntity($entity),
            'vat'               => (int) $entity->effectiveVat,
            'vendor_code'       => $entity->article,
            'barcodes'          => json_encode($this->_getBarcodesFromMoyskladEntity($entity), JSON_PRETTY_PRINT)
        ];
    }

    /**
     * Recount of product prices by changed parts from the queue
     *
     * @return  int number of updated products
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function recountPrices()
    {
        /** @var \HyperPcTablePrice_Recount_Queue */
        $priceRecountQueueTable = Table::getInstance('Price_Recount_Queue');
        if (!$priceRecountQueueTable) {
            return 0;
        }

        $productIds = $priceRecountQueueTable->getProductIdsForRecount();
        if (empty($productIds)) {
            return 0;
        }

        $products = $this->findById($productIds);

        $positionsTable = $this->_getSupertypeTable();
        /** @var \HyperPcTableMoysklad_Products_Index */
        $indexTable = Table::getInstance('Moysklad_Products_Index');

        $numberOfUpdated = 0;

        /** @var MoyskladProduct $product */
        foreach ($products as $product) {
            $productParts = $product->getConfigParts(compactByGroup: false, loadUnavailableParts: true);
            $actualPrice = $this->hyper['helper']['money']->get(0);
            foreach ($productParts as $part) {
                $partPrice = $part->getListPrice();
                if (isset($part->option) && $part->option && $part->option->id) {
                    $partPrice = $part->option->getListPrice();
                }
                $actualPrice->add($partPrice->val() * $part->quantity);
            }

            $product->setListPrice($actualPrice);

            $productData = $product->getArray();
            unset($productData['sale_price']); /** @todo manage sale price */

            $positionsTable->save($productData);

            // Update product prices in index table
            $indexTable->updateProductPrice($product->id, $actualPrice->val());

            $numberOfUpdated++;
        }

        /** @todo update prices in MoySklad */

        return $numberOfUpdated;
    }

    /**
     * Create processing plan
     *
     * @param   MoyskladProduct $product
     *
     * @since   2.0
     */
    protected function _createProcessingPlan(MoyskladProduct $product)
    {
        $processingplansTable = $this->hyper['helper']['processingplan']->getTable();

        $saved = $processingplansTable->save([
            'product_id' => $product->id
        ]);

        if (!$saved) {
            /** @todo log it */
            return;
        }

        $processingplanId = $processingplansTable->getDbo()->insertid();
        $product->processingplan_id = $processingplanId;

        $productTable = $this->getTable();
        $saved = $productTable->save($product->getArray());

        if (!$saved) {
            /** @todo log it */
            return;
        }

        /** @todo sync with moysklad */
    }
}
