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

use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\MoySkladHelper;
use HYPERPC\Object\Product\StockData;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\ORM\Entity\MoyskladStoreItem;
use HYPERPC\MoySklad\Entity\StockCurrentItem;
use HYPERPC\ORM\Entity\MoyskladProductVariant;
use MoySklad\Util\Exception\ApiClientException;
use HYPERPC\Object\SavedConfiguration\PartData;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Object\SavedConfiguration\PartDataCollection;

/**
 * Class MoyskladStockHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladStockHelper extends EntityContext
{

    /**
     * Hold products instances.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_products = [];

    /**
     * @var     MoyskladProductVariant[]
     *
     * @since   2.0
     */
    private $_productVariantsToUpdate = [];

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
        $this->setTable(Table::getInstance('Moysklad_Store_Items'));
        parent::initialize();
    }

    /**
     * Update stocks by store
     *
     * @return  void
     *
     * @throws \Exception
     * @throws ApiClientException
     * @throws RuntimeException
     *
     * @since   2.0
     *
     */
    public function updateStocks()
    {
        /** @var MoyskladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];
        $stocks = $moyskladHelper->getFreeStocks();

        $stores = $this->hyper['helper']['moyskladStore']->getList();
        $storeIds = array_combine(array_map(function ($store) {
            return $store->uuid;
        }, $stores), array_keys($stores));

        // filter stocks
        $stocks = array_filter($stocks, function ($item) use ($storeIds) {
            return array_key_exists($item->storeId, $storeIds) && $item->freeStock > 0; // only positive stocks in published stores
        });

        $db = $this->hyper['db'];
        $assortmentIds = join(',', array_map(function ($item) use ($db) {
            return $db->quote($item->assortmentId);
        }, $stocks));

        $conditions = [$db->quoteName('a.uuid') . ' IN (' . $assortmentIds . ')'];

        $positions = $this->hyper['helper']['position']->findAll([
            'conditions' => $conditions
        ]);

        $variants = $this->hyper['helper']['moyskladVariant']->findAll([
            'conditions' => $conditions
        ]);

        $productVariants = $this->hyper['helper']['moyskladProductVariant']->findAll([
            'conditions' => $conditions
        ]);

        $storeItems = [];

        /** @var Position $position */
        foreach ($positions as $position) {
            $positionStocks = array_filter($stocks, function ($item) use ($position) {
                /** @var StockCurrentItem $item */
                return $item->assortmentId === $position->uuid;
            });

            foreach ($positionStocks as $item) {
                $moyskladStoreItem = new MoyskladStoreItem([
                    'item_id' => $position->id,
                    'store_id' => $storeIds[$item->storeId],
                    'balance' => $item->freeStock
                ]);

                $storeItems[] = $moyskladStoreItem;
            }
        }

        /** @var MoyskladVariant $variant */
        foreach ($variants as $variant) {
            $variantStocks = array_filter($stocks, function ($item) use ($variant) {
                /** @var StockCurrentItem $item */
                return $item->assortmentId === $variant->uuid;
            });

            foreach ($variantStocks as $item) {
                $moyskladStoreItem = new MoyskladStoreItem([
                    'item_id' => $variant->part_id,
                    'option_id' => $variant->id,
                    'store_id' => $storeIds[$item->storeId],
                    'balance' => $item->freeStock
                ]);

                $storeItems[] = $moyskladStoreItem;
            }
        }

        $pcInsockStores = $this->hyper['params']->get('moysklad_pc_instock_stores', []);
        $stockProductIds = [];
        $newProductArrivals = [];
        /** @var MoyskladProductVariant $productVariant */
        foreach ($productVariants as $productVariant) {
            $productVariantStocks = array_filter($stocks, function ($item) use ($productVariant) {
                /** @var StockCurrentItem $item */
                return $item->assortmentId === $productVariant->uuid;
            });

            foreach ($productVariantStocks as $item) {
                $storeId = $storeIds[$item->storeId];
                if (!in_array($storeId, $pcInsockStores)) {
                    continue; // skip stores not for products in stock
                }

                $moyskladStoreItem = new MoyskladStoreItem([
                    'item_id' => $productVariant->product_id,
                    'option_id' => $productVariant->id,
                    'store_id' => $storeId,
                    'balance' => $item->freeStock
                ]);

                $stockProductIds[] = $productVariant->id;

                if (!count($this->getItems([
                    'itemIds' => [$moyskladStoreItem->item_id],
                    'optionIds' => [$moyskladStoreItem->option_id]
                ]))) {
                    $newProductArrivals[$moyskladStoreItem->option_id] = $moyskladStoreItem;
                }

                $storeItems[] = $moyskladStoreItem;
            }
        }

        /** @var \HyperPcTableMoysklad_Store_Items */
        $storeItemsTable = $this->getTable();
        $storeItemsTable->clear();
        $storeItemsTable->write($storeItems);

        /** @var \HyperPcTableMoysklad_Products_Index $indexesTable */
        $indexesTable = Table::getInstance('Moysklad_Products_Index');
        $indexesTable->clearOutdatedStocks($stockProductIds);

        /** @var \HyperPcTableProducts_Config_Values $valuesTable */
        $valuesTable = Table::getInstance('Products_Config_Values');
        $valuesTable->clearOutdatedStocks($stockProductIds);

        if (count($newProductArrivals)) {
            // Actualize and indexing new products
            $filterHelper = $this->hyper['helper']['moyskladFilter'];

            foreach (array_keys($newProductArrivals) as $configId) {
                $product = $this->getProductsByConfigurationId($configId);
                if (count($product)) {
                    $product = array_shift($product);

                    $this->_actualizeConfigurationPrice($product->getConfiguration());
                    $filterHelper->updateProductIndex($product);
                }
            }

            $this->_updateProductVariantsInMoysklad();
        }
    }

    /**
     * Get stocks
     *
     * @param   array $filter [
     *  'itemIds'   => [],
     *  'storeIds'  => [],
     *  'optionIds' => []
     * ]
     *
     * @return  MoyskladStoreItem[]
     *
     * @since   2.0
     */
    public function getItems($filter = [])
    {
        $filter = array_replace_recursive([
            'itemIds'    => [],
            'storeIds'   => [],
            'optionIds'  => [],
            'optionNull' => false,
        ], $filter);

        $db = $this->hyper['db'];

        /** @var \HyperPcTableMoysklad_Store_Items */
        $storeItemsTable = $this->getTable();

        $conditions = [];

        $itemIds = (array) $filter['itemIds'];
        if (!empty($itemIds)) {
            $conditions[] = $db->quoteName('a.item_id') . ' IN (' . join(',', $itemIds) . ')';
        }

        $storeIds = (array) $filter['storeIds'];
        if (!empty($storeIds)) {
            $conditions[] = $db->quoteName('a.store_id') . ' IN (' . join(',', $storeIds) . ')';
        }

        $optionIds  = (array) $filter['optionIds'];
        $optionNull = (bool) $filter['optionNull'];
        if (!empty($optionIds) && $optionNull === true) {
            $conditions[] = ('(' . $db->qn('a.option_id') . ' IN (' . join(',', $optionIds) . ') OR ' . $db->qn('a.option_id') . ' IS NULL)');
        } elseif (!empty($optionIds)) {
            $conditions[] = $db->qn('a.option_id') . ' IN (' . join(',', $optionIds) . ')';
        }

        $query = $db
            ->getQuery(true)
            ->select(['a.*'])
            ->from($db->quoteName($storeItemsTable->getTableName(), 'a'));

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        $_list = $db->setQuery($query)->loadAssocList('id');

        $class = $storeItemsTable->getEntity();
        $list  = [];
        foreach ($_list as $id => $item) {
            $list[$id] = new $class($item);
        }

        return $list;
    }

    /**
     * Get stock item
     *
     * @param   int $storeId
     * @param   int $itemId
     * @param   int $optionId
     *
     * @return  MoyskladStoreItem
     *
     * @since   2.0
     */
    public function getItem(int $storeId, int $itemId, $optionId = 0)
    {
        $db = $this->hyper['db'];

        /** @var \HyperPcTableMoysklad_Store_Items */
        $storeItemsTable = $this->getTable();

        $conditions = [
            $db->qn('a.store_id') . ' = ' . $db->q($storeId),
            $db->qn('a.item_id') . ' = ' . $db->q($itemId)
        ];

        if ($optionId) {
            $conditions[] = $db->qn('a.option_id') . ' = ' . $db->q($optionId);
        }

        $query = $db
            ->getQuery(true)
            ->select(['a.*'])
            ->from($db->quoteName($storeItemsTable->getTableName(), 'a'))
            ->where($conditions);

        $class = $storeItemsTable->getEntity();
        $stock = $db->setQuery($query)->loadAssoc();

        return new $class(is_array($stock) ? $stock : []);
    }

    /**
     * Get items count by store
     *
     * @param array $items
     * @param array $storeIds
     *
     * @return int
     *
     * @since 2.0
     */
    public function getItemsCount(array $items, array $storeIds)
    {
        if (!count($items)) {
            return 0;
        }

        $itemIds      = [];
        $optionsIds[] = 0;

        $filter = [];
        foreach ($items as $item) {
            if (in_array($item->id, $itemIds)) {
                continue;
            }

            $itemIds[] = $item->id;
            if ($item->getOptions()) {
                foreach ($item->getOptions() as $option) {
                    $filter['optionIds'][] = $option->id;
                    $filter['optionNull']  = true;
                }
            }
        }

        $filter['itemIds']  = $itemIds;
        $filter['storeIds'] = $storeIds;

        return count($this->getItems($filter));
    }

    /**
     * Get product list in stock.
     *
     * @param   array  $conditions
     * @param   array  $allowedGroups
     *
     * @return  MoyskladProduct[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProducts(array $conditions = [], $allowedGroups = [])
    {
        $hash = md5((new Registry(array_merge($conditions, $allowedGroups)))->toString());

        if (!array_key_exists($hash, self::$_products)) {
            $products = [];

            /** @todo move to own method for use with HYPERPC\ORM\Filter\MoySkladProductInStockFilter::find() */
            $storeItems = $this->getProductStoreItems($conditions, $allowedGroups);
            if (count($storeItems)) {
                $configurations = $this->hyper['helper']['configuration']->findById(array_map(function ($storeItem) {
                    /** @var MoyskladStoreItem $storeItem */
                    return $storeItem->option_id;
                }, $storeItems));

                foreach ($storeItems as $storeItem) {
                    if (!isset($configurations[$storeItem->option_id])) {
                        continue;
                    }

                    $configuration = $configurations[$storeItem->option_id];

                    $product = $configuration->prepareProductConfiguration();
                    $product->setListPrice(clone $configuration->price);
                    $product->setSalePrice(clone $configuration->price);
                    $product->params->set('stock', new StockData([
                        'balance' => (int) $storeItem->balance,
                        'storeId' => (int) $storeItem->store_id
                    ]));

                    $product->getRender()->setEntity($product);

                    $products[] = $product;
                }
            }

            self::$_products[$hash] = $products;
        }

        return self::$_products[$hash];
    }

    /**
     * Get instock product list by configuration id
     *
     * @param   int $configId
     *
     * @return  MoyskladProduct[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProductsByConfigurationId($configId)
    {
        $db = $this->getDbo();

        return $this->getProducts([
            $db->qn('storeitems.option_id') . ' = ' . $db->q($configId)
        ]);
    }

    /**
     * Get store items whose type is product
     *
     * @param   array  $conditions
     * @param   array  $allowedGroups
     *
     * @return  MoyskladStoreItem[]
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getProductStoreItems(array $conditions = [], $allowedGroups = [])
    {
        $db = $this->getDbo();

        if (empty($conditions)) {
            $conditions[] = $db->qn('storeitems.balance') . ' > 0';
            $conditions[] = $db->qn('storeitems.option_id') . ' > 0';
            $conditions[] = $db->qn('positions.type_id') . ' = 3';
        }

        if (!empty($allowedGroups)) {
            $conditions[] = $db->qn('positions.product_folder_id') . ' IN (' . implode(', ', $allowedGroups) . ')';
        }

        $query = $db
            ->getQuery(true)
            ->select([
                'positions.id',
                'storeitems.option_id',
                'storeitems.store_id',
                'storeitems.balance',
            ])
            ->from(
                $db->qn(HP_TABLE_POSITIONS, 'positions')
            )
            ->join('LEFT', $db->qn(HP_TABLE_MOYSKLAD_STORE_ITEMS, 'storeitems') . ' ON positions.id = storeitems.item_id')
            ->where($conditions);

        $_list = $db->setQuery($query)->loadAssocList();

        $class = MoyskladStoreItem::class;
        $list  = [];
        foreach ($_list as $id => $item) {
            $list[$id] = new $class($item);
        }

        /** @var MoyskladStoreItem[] $storeItems */
        return $list;
    }

    /**
     * Normalize in stock products for render after filter.
     *
     * @param   array  $inStockProducts
     *
     * @return  array
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function normalizeForFilter(array $inStockProducts)
    {
        return $inStockProducts;
    }

    /**
     * Recount instock product prices
     *
     * @return  int number of updated configurations
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function recountProductPrices()
    {
        /** @var \HyperPcTablePrice_Recount_Queue */
        $priceRecountQueueTable = Table::getInstance('Price_Recount_Queue');
        if (!$priceRecountQueueTable) {
            return 0;
        }

        $db = $this->getDbo();
        $query = $db
            ->getQuery(true)
            ->select(['DISTINCT a.part_id'])
            ->from(
                $db->qn($priceRecountQueueTable->getTableName(), 'a')
            );

        $changedPartIds = $db->setQuery($query)->loadColumn();

        $numberOfUpdated = 0;

        /** @var MoyskladStoreItem[] $stockItems */
        $storeItems = $this->getProductStoreItems();
        if (count($storeItems)) {
            $configurationHelper = $this->hyper['helper']['configuration'];
            $configurations = $configurationHelper->findById(array_map(function ($storeItem) {
                /** @var MoyskladStoreItem $storeItem */
                return $storeItem->option_id;
            }, $storeItems));

            /** @var SaveConfiguration $configuration */
            foreach ($configurations as $configuration) {
                $configurationPartsData = PartDataCollection::create($configuration->parts->getArrayCopy());
                $partIds = array_keys($configurationPartsData->toArray());
                if (empty(array_intersect($partIds, $changedPartIds))) { /** @todo consider options */
                    continue;
                }

                if ($this->_actualizeConfigurationPrice($configuration)) {
                    $numberOfUpdated++;
                }
            }

            $this->_updateProductVariantsInMoysklad();
        }

        return $numberOfUpdated;
    }

    /**
     * Actualize configuration price
     *
     * @param   SaveConfiguration $configuration
     *
     * @return  bool true if price have updated, false if update not needed
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _actualizeConfigurationPrice(SaveConfiguration $configuration)
    {
        $result = false;
        $oldTotal = $configuration->price->getClone();

        /** @var ConfigurationHelper $configurationHelper */
        $configurationHelper = $this->hyper['helper']['configuration'];

        $actualPriceData = $configurationHelper->actualizePrice($configuration);

        $productVariantHelper = $this->hyper['helper']['moyskladProductVariant'];
        /** @var MoyskladProductVariant $productVariant */
        $productVariant = $productVariantHelper->findById($configuration->id);
        if ($productVariant->list_price->val() !== $actualPriceData->product->val()) {
            $productVariant->list_price = $actualPriceData->product;
            $productVariantHelper->getTable()->save($productVariant->toArray());
            $this->_productVariantsToUpdate[] = $productVariant;

            $result = true;
        }

        // Update configuration index
        if ($oldTotal->val() !== $actualPriceData->total->val()) {
            /** @var \HyperPcTableMoysklad_Products_Index */
            $indexTable = Table::getInstance('Moysklad_Products_Index');
            // Update product price in index table
            $indexTable->updateProductPrice(
                $productVariant->product_id,
                $actualPriceData->total->val(),
                $configuration->id
            );

            $result = true;
        }

        return $result;
    }

    /**
     * Update product variants in MoySklad
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function _updateProductVariantsInMoysklad()
    {
        if (count($this->_productVariantsToUpdate)) {
            $moyskladEntities = array_map(function ($productVariant) {
                return $productVariant->toMoyskladProductEntity();
            }, $this->_productVariantsToUpdate);

            /** @var MoyskladHelper $moyskladHelper */
            $moyskladHelper = $this->hyper['helper']['moysklad'];
            try {
                $moyskladHelper->massUpdateProducts($moyskladEntities);
            } catch (\Exception $e) {
                $moyskladHelper->log('Can\'t update products from _updateProductVariantsInMoysklad: ' . $e->getMessage());
            }

            $this->_productVariantsToUpdate = [];
        }
    }
}
