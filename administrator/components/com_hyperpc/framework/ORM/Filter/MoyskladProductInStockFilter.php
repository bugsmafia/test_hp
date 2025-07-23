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
 */

namespace HYPERPC\ORM\Filter;

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;
use HYPERPC\ORM\Entity\Store;
use HYPERPC\Object\Product\StockData;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Helper\MoyskladStoreHelper;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Helper\MoyskladFilterHelper;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\ORM\Entity\MoyskladStoreItem;
use HYPERPC\Joomla\View\Html\Data\Manager;
use HYPERPC\Joomla\View\Html\Data\HtmlData;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\ORM\Filter\Manager as FilterManager;
use HYPERPC\Joomla\View\Html\Data\Product\Filter as FilterData;

defined('_JEXEC') or die('Restricted access');

/**
 * Class MoyskladProductInStockFilter
 *
 * @package HYPERPC\ORM\Filter
 *
 * @since   2.0
 */
class MoyskladProductInStockFilter extends AbstractFilter
{
    /**
     * Hold category key
     *
     * @var string
     *
     * @since 2.0
     */
    public string $categoryKey = 'product_folder_id';

    /**
     * Hold context
     *
     * @var string
     *
     * @since 2.0
     */
    public string $context = 'com_hyperpc.position';

    /**
     * Find items form form data.
     *
     * @return  $this|AbstractFilter
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function find()
    {
        $db    = $this->hyper['db'];
        $query = $this->_setHeadQuery();

        $query
            ->group($db->qn('stock.id'))
            ->order($db->qn('tIndex.price_b') . ' ASC');

        $query->where($this->_setConditions());

        $this->setQueryDump($query->dump());

        /** @todo move to own method for use with HYPERPC\Helper\MoyskladStockHelper::getProducts() */
        /** @var MoyskladStoreItem[] $storeItems */
        $_storeItems = $db->setQuery($query)->loadAssocList('id');

        $class      = MoyskladStoreItem::class;
        $storeItems = [];
        foreach ($_storeItems as $id => $item) {
            $storeItems[$id] = new $class($item);
        }

        $products = [];
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

                $products[$storeItem->option_id] = $product;
            }
        }

        $this->setItems(
            (array) $this->hyper['helper']['moyskladStock']->normalizeForFilter($products)
        );

        return $this;
    }

    /**
     * Find property total count.
     *
     * @param   string $alias
     * @param   string|array $value
     * @param   array $conditions
     *
     * @return  \Exception|int|mixed
     *
     * @since   2.0
     */
    public function findPropertyCount(string $alias, $value, array $conditions = [])
    {
        try {
            $db = $this->hyper['db'];
            /** @var \JDatabaseQueryMysqli $query */
            $query = $this->_setHeadQuery(['DISTINCT stock.option_id as id']);

            if (!preg_match('/\./', $alias)) {
                if (in_array($alias, ['category', 'product_folder_id'])) {
                    $alias = 'tProd.' . strtolower($alias);
                } else {
                    $alias = 'tIndex.' . strtolower($alias);
                }
            }

            if (array_key_exists('request', $conditions) && $conditions['request'] === true) {
                unset($conditions['request']);
                $query->where($this->_setConditions());
            }

            if (!empty($value)) {
                $valueCondition = [];

                if (is_string($value)) {
                    $valueCondition[] = $db->qn($alias) . ' = ' . $db->q($value);
                } elseif (is_array($value)) {
                    $valueCondition[] = $db->qn($alias) . ' IN (' . implode(',', array_map(function ($value) use ($db) {
                        return $db->q($value);
                    }, $value)) . ')';
                }

                $conditions = array_merge($conditions, $valueCondition);
            }

            $query->where($conditions);

            $db->setQuery($query);

            $output  = [];
            $records = (array) $db->loadObjectList();

            if (count($records) === 0) {
                return 0;
            }

            foreach ($records as $item) {
                if (!in_array($item->id, $output)) {
                    $output[] = $item->id;
                }
            }

            return count($output);
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Render html filter result.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function render()
    {
        return $this->hyper['helper']['render']->render('product/teaser/2024/default', [
            'showFps'  => true,
            'products' => $this->getItems(),
            'groups'   => $this->hyper['helper']['productFolder']->getList(),
        ], 'renderer', false);
    }

    /**
     * Setup field options total count.
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function setFieldOptionsCount()
    {
        $filterHelper = $this->getFilterHelper();

        $allowedFilters      = [];
        $hasCountFilters     = [];
        $allowedFilterFields = $filterHelper->getAllowedFields();

        $customData = $this->getCustomFilterData()->getArrayCopy();
        $conditions = '';

        $db = $this->hyper['db'];

        $this
            ->setPriceRangeConditions($conditions)
            ->setCategoryConditions($conditions)
            ->setStoreConditions($conditions);

        $filterTotalCount = $this->_filterData->count();

        /** @var Field $allowedFilterField */
        foreach ($allowedFilterFields as $allowedFilterField) {
            $name = $allowedFilterField->name;
            if (!array_key_exists($name, $allowedFilters)) {
                $allowedOptions  = [];
                $hasCountOptions = [];
                $fieldOptions    = (array) $allowedFilterField->fieldparams->get('options', []);

                foreach ($fieldOptions as $optionName => $optionData) {
                    $optionData  = new JSON((array) $optionData);
                    $optionValue = $optionData->get('value');

                    if ($optionValue === 'none') {
                        continue;
                    }

                    if (!array_key_exists($optionValue, $allowedOptions)) {
                        if (($this->_filterData->find($name) && $filterTotalCount <= 1) ||
                            $filterTotalCount === 0
                        ) {
                            $countValue = $this->findPropertyCount(
                                $name,
                                $optionValue
                            );
                        } else {
                            /** @var \JDatabaseQueryMysqli $query */
                            $query = $this->_setHeadQuery(['DISTINCT stock.option_id as id']);

                            $query->where($conditions);
                            $optionCondition = '';

                            $newData = new JSON($customData);

                            $newData->set($name, [$optionValue]);

                            $this->setCustomFieldsConditions($optionCondition, $newData);
                            $query->where(preg_replace('/^ AND /', '', $optionCondition));

                            $db->setQuery($query);

                            $output  = [];
                            $records = (array) $db->loadObjectList();

                            if (count($records) > 0) {
                                foreach ($records as $item) {
                                    if (!in_array($item->id, $output)) {
                                        $output[] = $item->id;
                                    }
                                }
                            }

                            $countValue = count($output);
                        }

                        if ($countValue instanceof \Exception) {
                            $this->hyper['cms']->enqueueMessage($countValue->getMessage(), 'error');
                            break;
                        } else {
                            $optionCountValue = [
                                'count' => $countValue,
                                'value' => $optionValue
                            ];

                            $allowedOptions[$optionValue] = $optionCountValue;

                            if ($countValue > 0) {
                                $hasCountOptions[$optionValue] = $optionCountValue;
                            }
                        }
                    }
                }

                $allowedFilters[$name]  = $allowedOptions;
                $hasCountFilters[$name] = $hasCountOptions;
            }
        }

        $this->_fieldOptionsCount    = new JSON($allowedFilters);
        $this->_fieldOptionsHasCount = new JSON($hasCountFilters);

        $this
            ->_setCategoriesCount()
            ->_setStoreCount();

        return $this;
    }

    /**
     * Set store conditions.
     *
     * @param   string  $conditions
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setStoreConditions(&$conditions)
    {
        $db = $this->hyper['db'];

        $storeHelper   = $this->getStoreHelper();
        $legacyStoreIds = (array) $this->_filterData->get('store', []);

        $storeIds = [];
        foreach ($legacyStoreIds as $legacyStoreId) {
            $ids = $storeHelper->convertFromLegacyId(intval($legacyStoreId));

            foreach ($ids as $id) {
                if (!in_array($id, $storeIds)) {
                    $storeIds[] = $id;
                }
            }
        }

        if (!empty($storeIds) && count($storeIds) > 0) {
            $ids = array_map(function ($id) use ($db) {
                return $db->q($id);
            }, $storeIds);

            $conditions .= ' AND ' .  $db->qn('stock.store_id') . ' IN (' . implode(', ', $ids) . ')' . PHP_EOL;
        }

        return $this;
    }

    /**
     * Set categories total count.
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setCategoriesCount()
    {
        $filterHelper = $this->getFilterHelper();

        //  Use static instance
        $requestConditions = $this->_setConditions();
        $detailsConditions = explode(PHP_EOL, $requestConditions);

        //  Remove all query with category_id for get current count value.
        $newRequestConditions = [];
        foreach ($detailsConditions as $detail) {
            if (preg_match('/product_folder_id/', $detail)) {
                continue;
            }

            $newRequestConditions[] = $detail;
        }

        $newRequestConditions = implode(PHP_EOL, $newRequestConditions);

        if ($filterHelper->enableCategoryFilter()) {
            $hasCount         = [];
            $allowedCount     = [];
            $categoryList     = $this->getCategoryList();
            $filterTotalCount = $this->_filterData->count();

            if (count($categoryList) >= 1) {
                /** @var ProductFolder $category */
                foreach ($categoryList as $category) {
                    if ($this->_filterData->find('category') && $filterTotalCount === 1) {
                        $countValue = $this->findPropertyCount(
                            'product_folder_id',
                            (string) $category->id
                        );
                    } else {
                        $countValue = $this->findPropertyCount(
                            'product_folder_id',
                            (string) $category->id,
                            [$newRequestConditions]
                        );
                    }

                    if ($countValue instanceof \Exception) {
                        $this->hyper['cms']->enqueueMessage($countValue->getMessage(), 'error');
                        break;
                    } else {
                        $countValue = [
                            'count' => $countValue,
                            'value' => $category->id
                        ];

                        $allowedCount[$category->id] = $countValue;

                        if ($countValue > 0) {
                            $hasCount[$category->id] = $countValue;
                        }
                    }
                }
            }

            $this->_fieldOptionsCount->set('category', $allowedCount);
            $this->_fieldOptionsHasCount->set('category', $hasCount);
        }

        return $this;
    }

    /**
     * Get filter data object.
     *
     * @return  HtmlData|FilterData
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getFilterData()
    {
        return Manager::getInstance()->get('Product.Filter', [
            'filter' => FilterManager::getInstance()->get('MoyskladProductInStock')
        ]);
    }

    /**
     * Get store helper
     *
     * @return  MoyskladStoreHelper
     *
     * @since   2.0
     */
    public function getStoreHelper()
    {
        return $this->hyper['helper']['moyskladStore'];
    }

    /**
     * Get filter helper
     *
     * @return  MoyskladFilterHelper
     *
     * @since   2.0
     */
    public function getFilterHelper()
    {
        return $this->hyper['helper']['moyskladFilter'];
    }

    /**
     * Get product folder helper
     *
     * @return  ProductFolderHelper
     *
     * @since   2.0
     */
    public function getGroupHelper()
    {
        return $this->hyper['helper']['productFolder'];
    }

    /**
     * Get category helper
     *
     * @return  ProductFolderHelper
     *
     * @since   2.0
     */
    public function getCategoryHelper()
    {
        return $this->getGroupHelper();
    }

    /**
     * Get variants helper
     *
     * @return  MoyskladVariantHelper
     *
     * @since   2.0
     */
    public function getOptionsHelper()
    {
        return $this->hyper['helper']['moyskladVariant'];
    }

    /**
     * Get stock table
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getStockTable()
    {
        return Table::getInstance('Moysklad_Store_Items');
    }

    /**
     * Get products table
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProductsTable()
    {
        return Table::getInstance('Positions');
    }

    /**
     * Get index table
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getIndexTable()
    {
        return Table::getInstance('Moysklad_Products_Index');
    }

    /**
     * Get stories
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getStores()
    {
        $storeHelper = $this->getStoreHelper();

        $db = $this->hyper['db'];
        $conditions = [
            $db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED,
            $db->quoteName('a.name') . ' <> "ROOT"'
        ];

        $stores = $storeHelper->findAll(['conditions' => $conditions]);

        $legacyStores = $this->hyper['helper']['store']->findAll();

        $_stores = [];
        foreach ($stores as $storeId => $store) {
            $stockStoreId = $storeHelper->convertToLagacyId((int) $storeId);
            if (isset($_stores[$stockStoreId])) {
                continue;
            }

            $_stores[$stockStoreId] = $legacyStores[$stockStoreId];
        }

        return $_stores;
    }

    /**
     * Setup custom conditions.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _setConditions()
    {
        $conditions = '';

        $this
            ->setPriceRangeConditions($conditions)
            ->setCategoryConditions($conditions)
            ->setStoreConditions($conditions)
            ->setCustomFieldsConditions($conditions, $this->getCustomFilterData());

        return $conditions;
    }

    /**
     * Setup and get head SQL filter query.
     *
     * @param   array  $select
     *
     * @return  \JDatabaseQuery
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _setHeadQuery(array $select = ['stock.*'])
    {
        /** @var \JDatabaseDriverMysqli $db */
        $db = $this->hyper['db'];

        /** @var \HyperPcTableMoysklad_Products_Index $table */
        $indexTable = $this->getIndexTable();

        /** @var \HyperPcTableMoysklad_Products_Index $table */
        $inStockTable = $this->getStockTable();

        /** @var \HyperPcTablePositions $productsTable */
        $productsTable = $this->getProductsTable();

        $filterHelper = $this->getFilterHelper();

        $conditions = [
            $db->qn('stock.balance')   . ' > 0',
            $db->qn('tIndex.in_stock') . ' IS NOT NULL' . PHP_EOL
        ];

        $query = $db->getQuery(true)
            ->select($select)
            ->from($db->qn($inStockTable->getTableName(), 'stock'))
            ->join(
                'RIGHT',
                $db->qn($indexTable->getTableName(), 'tIndex') .
                ' ON ' .
                'stock.item_id = tIndex.product_id' .
                ' AND ' .
                'stock.option_id = tIndex.in_stock'
            );

        if ($filterHelper->enableCategoryFilter()) {
            $query->join(
                'RIGHT',
                $db->qn($productsTable->getTableName(), 'tProd') .
                ' ON ' .
                'stock.item_id = tProd.id'
            );
        }

        $query->where($conditions);

        return $query;
    }

    /**
     * Set store total count.
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setStoreCount()
    {
        $storeHelper = $this->getStoreHelper();

        $db = $this->hyper['db'];
        $conditions = [
            $db->quoteName('a.published') . ' = ' . HP_STATUS_PUBLISHED,
            $db->quoteName('a.name') . ' != ' . $db->q('ROOT')
        ];

        $storeList = $storeHelper->findAll(['conditions' => $conditions]);

        if (count($storeList) >= 1) {
            $hasCount          = [];
            $allowedCount      = [];
            $requestConditions = $this->_setConditions();
            $filterTotalCount  = $this->_filterData->count();
            $detailsConditions = explode(PHP_EOL, $requestConditions);

            //  Remove all query with store_id for get current count value.
            $newRequestConditions = [];
            foreach ($detailsConditions as $detail) {
                if (preg_match('/store_id/', $detail)) {
                    continue;
                }

                $newRequestConditions[] = $detail;
            }

            $legacyStores = $this->getStores();

            $storeIds = [];
            /** @var Store $store */
            foreach (array_keys($storeList) as $storeId) {
                $legacyStoreId = $storeHelper->convertToLagacyId($storeId);
                if (array_key_exists($legacyStoreId, $legacyStores)) {
                    $storeIds[$storeHelper->convertToLagacyId($storeId)][] = $storeId;
                }
            }

            $newRequestConditions = implode(PHP_EOL, $newRequestConditions);

            foreach ($storeIds as $stockStoreId => $ids) {
                if ($this->_filterData->find('store') && $filterTotalCount === 1) {
                    $countValue = $this->findPropertyCount(
                        'stock.store_id',
                        $ids
                    );
                } else {
                    $countValue = $this->findPropertyCount(
                        'stock.store_id',
                        $ids,
                        [$newRequestConditions]
                    );
                }

                if ($countValue instanceof \Exception) {
                    $this->hyper['cms']->enqueueMessage($countValue->getMessage(), 'error');
                    break;
                } else {
                    $count = isset($allowedCount[$stockStoreId]['count']) ? $allowedCount[$stockStoreId]['count'] : 0;

                    $countValue = [
                        'count' => $count + $countValue,
                        'value' => $stockStoreId
                    ];

                    $allowedCount[$stockStoreId] = $countValue;

                    if ($countValue > 0) {
                        $hasCount[$stockStoreId] = $countValue;
                    }
                }
            }

            $this->_fieldOptionsCount->set('store', $allowedCount);
            $this->_fieldOptionsHasCount->set('store', $hasCount);
        }

        return $this;
    }
}
