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

namespace HYPERPC\ORM\Filter;

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Container;
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use HYPERPC\ORM\Table\Table;
use HyperPcTableProducts_Index;
use HYPERPC\Helper\FilterHelper;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Helper\MoyskladStoreHelper;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Helper\MoyskladFilterHelper;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\Joomla\View\Html\Data\HtmlData;
use HYPERPC\Joomla\View\Html\Data\Product\Filter as FilterData;

/**
 * Class AbstractFilter
 *
 * @property    string  $categoryKey
 * @property    JSON    $_filterData
 * @property    string  $_queryDump
 * @property    string  $_type
 *
 * @package     HYPERPC\ORM\Filter
 *
 * @since       2.0
 */
abstract class AbstractFilter extends Container
{

    const FIND_LOGIC_EQUAL = 'equal';
    const FIND_LOGIC_LIKE = 'like';

    /**
     * Hold context.
     *
     * @var     string
     *
     * @since   2.0
     */
    public string $context;

    /**
     * Hold class name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected string $_class;

    /**
     * Hold count items of field options.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_fieldOptionsCount;

    /**
     * Hold only has count items of field options.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_fieldOptionsHasCount;

    /**
     * Find result of filter query.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_items = [];

    /**
     * AbstractFilter constructor.
     *
     * @param array $values
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->initialize();
        $this->_setType();
    }

    /**
     * Find items from form data.
     *
     * @return  $this
     *
     * @since   2.0
     */
    abstract public function find();

    /**
     * Find property total count.
     *
     * @param   string $alias
     * @param   string $value
     * @param   array $conditions
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function findPropertyCount(string $alias, string $value, array $conditions = []);

    /**
     * Get category list item.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCategoryList()
    {
        $ids = (array) $this->getCategoryIds();
        $db  = $this->hyper['db'];

        if (count($ids) <= 0) {
            return [];
        }

        /** @var ProductFolderHelper $categoryHelper */
        $categoryHelper = $this->getCategoryHelper();

        return $categoryHelper->findAll([
            'conditions' => [
                $db->qn('a.id') . ' IN (' . implode(',', $ids) . ')'
            ]
        ]);
    }

    /**
     * Get category ids.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCategoryIds()
    {
        $db = $this->hyper['db'];

        /** @var \JDatabaseQueryMysqli $query */
        $query = $this->_setHeadQuery(['tProd.' . $this->categoryKey]);

        $result = (array) $db->setQuery($query)->loadAssocList($this->categoryKey);

        if (count($result) > 0) {
            return array_keys($result);
        }

        return [];
    }

    /**
     * Get current filter values.
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCurrentFilters()
    {
        $current        = new JSON();

        /** @var FilterHelper|MoyskladFilterHelper $filterHelper */
        $filterHelper   = $this->getFilterHelper();
        $allowedFilters = (array) $filterHelper->getAllowedFields();

        if (count($allowedFilters) <= 0) {
            return $current;
        }

        /** @var Field $field */
        foreach ($allowedFilters as $field) {
            if ($this->_filterData->has($field->name)) {
                $current->set($field->name, $this->_filterData->get($field->name));
            }
        }

        //  Find price range filter value.
        $priceRangeFieldName = FilterData::PRICE_FIELD_NAME;
        if ($this->_filterData->has($priceRangeFieldName)) {
            $current->set($priceRangeFieldName, $this->_filterData->get($priceRangeFieldName));
        }

        //  Find store filter value.
        $storeFieldName = FilterData::STORE_FIELD_NAME;
        if ($this->_filterData->has($storeFieldName)) {
            $current->set($storeFieldName, $this->_filterData->get($storeFieldName));
        }

        //  Find category filter value.
        $categoryFieldName = FilterData::CATEGORY_FIELD_NAME;
        if ($this->_filterData->has($categoryFieldName)) {
            $current->set($categoryFieldName, $this->_filterData->get($categoryFieldName));
        }

        return $current;
    }

    /**
     * Clear all data for field custom conditions.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getCustomFilterData()
    {
        $data = clone $this->_filterData;

        $data
            ->remove('type')
            ->remove('store')
            ->remove('category')
            ->remove('price-range');

        return $data;
    }

    /**
     * Get max or min price value from product index.
     *
     * @param   string  $marker         Use MAX or MIN values.
     * @param   bool    $isInStock
     * @param   string  $col
     * @param   bool    $returnQuery
     *
     * @return  float
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getExtremePriceValue($marker = 'MAX', $isInStock = false, $col = 'a.price_b', $returnQuery = false)
    {
        /** @var HyperPcTableProducts_Index|\HyperPcTableMoysklad_Products_Index $table */
        $indexTable = $this->getIndexTable();

        $db = $this->hyper['db'];

        $query = $db->getQuery(true)
            ->select([
                strtoupper($marker) . '(' . $col . ')'
            ])
            ->from($db->qn($indexTable->getTableName(), 'a'));

        if ($isInStock === false) {
            $query->where($db->qn('a.in_stock') . ' IS NULL');
        } elseif ($isInStock === true) {
            $query->where($db->qn('a.in_stock') . ' IS NOT NULL');
        }

        if ($returnQuery) {
            return $query;
        }

        return (float) $db->setQuery($query)->loadResult();
    }

    /**
     * Get field options count list.
     *
     * @param   bool  $onlyHasCount
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFieldOptionsCount($onlyHasCount = false)
    {
        if ($onlyHasCount === true) {
            return $this->_fieldOptionsHasCount;
        }

        return $this->_fieldOptionsCount;
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
    abstract public function getFilterData();

    /**
     * Get init filter state.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getInitState()
    {
        $state  = [];
        $counts = $this->getFieldOptionsCount(true);

        $priceKeyName = FilterData::PRICE_FIELD_NAME;
        $state[$priceKeyName] = '';
        if ($this->_filterData->has($priceKeyName)) {
            $state[$priceKeyName] = $this->_filterData->get($priceKeyName);
        }

        $storeKeyName = FilterData::STORE_FIELD_NAME;
        $state[$storeKeyName] = '';
        if ($this->_filterData->has($storeKeyName)) {
            $state[$storeKeyName] = (array) $this->_filterData->get($storeKeyName);
        } else {
            if (empty($this->hyper['input']->get('store'))) {
                $stores = $this->getStores();

                $state[$storeKeyName] = array_map(function ($store) {
                    return (string) $store;
                }, array_keys($stores));
            }
        }

        // TODO check if it is actually needed
        $categoryKeyName = FilterData::CATEGORY_FIELD_NAME;
        if ($this->_filterData->has($categoryKeyName)) {
            $categoryVal = $this->_filterData->get($categoryKeyName);
            $counts[$categoryKeyName] = $categoryVal;
        }

        foreach ($counts as $keyName => $countData) {
            if (!array_key_exists($keyName, $state)) {
                $filterData = $this->_filterData->get($keyName);
                if (is_array($filterData)) {
                    $state[$keyName] = $filterData;
                } else {
                    if (!empty($filterData)) {
                        $state[$keyName] = [$filterData];
                    } else {
                        $state[$keyName] = [];
                    }
                }
            }
        }

        return $state;
    }

    /**
     * Get count item result.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getItemCount()
    {
        return count((array) $this->_items);
    }

    /**
     * Get item list for render.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Get price range values. First is "from" value next is "to" value.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getPriceRange()
    {
        $delimiter = ':';
        $value     = $this->_filterData->get(FilterData::PRICE_FIELD_NAME, $delimiter);

        if (preg_match('/:/', $value)) {
            return (array) explode($delimiter, $value, 2);
        }

        return [0, 0];
    }

    /**
     * Get core query dump.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getQueryDump()
    {
        return $this->_queryDump;
    }

    /**
     * Get filter type.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Setup and get query filter state.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getUrlQuery()
    {
        $query = [];
        $data  = $this->_filterData->getArrayCopy();

        foreach ($data as $name => $value) {
            $isCurrentArrayData  = (is_array($value)  && count($value));
            $isCurrentStringData = (is_string($value) && !empty($value));
            if ($isCurrentArrayData) {
                $qValue = implode(FilterData::QUERY_DATA_SEPARATOR, $value);
                if (!empty($qValue)) {
                    $query[] = $name . '=' . $qValue;
                }
            } elseif ($isCurrentStringData) {
                if ($value === '0:0') {
                    continue;
                }

                $query[] = $name . '=' . $value;
            }
        }

        return implode('&', $query);
    }

    /**
     * Get store helper
     *
     * @return  MoyskladStoreHelper
     *
     * @since   2.0
     */
    abstract public function getStoreHelper();

    /**
     * Get filter helper
     *
     * @return  MoyskladFilterHelper
     *
     * @since   2.0
     */
    abstract public function getFilterHelper();

    /**
     * Get group helper
     *
     * @return  ProductFolderHelper
     *
     * @since   2.0
     */
    abstract public function getGroupHelper();

    /**
     * Get options helper
     *
     * @return  MoyskladVariantHelper
     *
     * @since   2.0
     */
    abstract public function getOptionsHelper();

    /**
     * Get category helper
     *
     * @return  ProductFolderHelper
     *
     * @since   2.0
     */
    abstract public function getCategoryHelper();

    /**
     * Get stock table
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    abstract public function getStockTable();

    /**
     * Get index table
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    abstract public function getProductsTable();

    /**
     * Get index table
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    abstract public function getIndexTable();

    /**
     * Get stores
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    abstract public function getStores();

    /**
     * Check filter by key name.
     *
     * @param   string  $keyName
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function hasFilter($keyName)
    {
        $current = $this->getCurrentFilters();
        return $current->has($keyName);
    }

    /**
     * Initialize filter.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_filterData = new JSON();
        $this->_setUrlFilterData();
    }

    /**
     * Render html filter result.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function render();

    /**
     * Set product category conditions.
     *
     * @param   string  $conditions
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setCategoryConditions(&$conditions)
    {
        /** @var FilterHelper|MoyskladFilterHelper $filterHelper */
        $filterHelper = $this->getFilterHelper();

        if ($filterHelper->enableCategoryFilter()) {
            $db    = $this->hyper['db'];
            $value = (array) $this->_filterData->get('category');
            if (count($value) > 0) {
                $conditions .= ' AND ' .  $db->qn('tProd.' . $this->categoryKey)  . ' IN (' . implode(',', $value) . ')' . PHP_EOL;
            }
        }

        return $this;
    }

    /**
     * Setup custom fields condition by request form data.
     *
     * @param   string  $conditions
     * @param   JSON    $filterData
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setCustomFieldsConditions(&$conditions, JSON $filterData)
    {
        $db = $this->hyper['db'];

        $fieldsData = (array) $filterData->getArrayCopy();

        foreach ($fieldsData as $fi => $data) {
            if (empty($data)) {
                unset($fieldsData[$fi]);
            }
        }

        if (count($fieldsData)) {
            $i = 0;
            $_conditions = '';

            foreach ($fieldsData as $fieldId => $values) {
                if (is_string($values) && empty($values)) {
                    continue;
                }

                $values         = (array) $values;
                $conditionLogic = self::FIND_LOGIC_EQUAL;

                $i++;
                $j = 0;

                $paramLogic  = 'AND';
                $countValues = count($values);

                foreach ($values as $value) {
                    $isFirst = ($j === 0);

                    if ($j !== 0) {
                        $paramLogic = 'OR';
                    }

                    $j++;

                    $isLast = ($j === $countValues);

                    switch ($conditionLogic) {
                        case self::FIND_LOGIC_EQUAL:
                            $_conditions .= ' ' . $paramLogic . ' ';

                            if ($countValues > 1 && $isFirst) {
                                $_conditions .= ' (';
                            }

                            $endCondition = '';

                            if ($countValues > 1 && $isLast) {
                                $endCondition = ')' . PHP_EOL;
                            }

                            if ($countValues === 1) {
                                $endCondition = PHP_EOL;
                            }

                            $_conditions .= $db->qn('tIndex.' . $fieldId) . ' = ' . $db->q($value) . $endCondition;
                            break;

                        case self::FIND_LOGIC_LIKE:
                            $_conditions .= ' ' . $paramLogic . ' ';
                            $_conditions .= $db->qn('tIndex.' . $fieldId) . ' LIKE ' . $db->q('%' . $value . '%') . PHP_EOL;
                            break;
                    }
                }

                $_conditions .= '';
            }

            $conditions .= $_conditions;
        }

        return $this;
    }

    /**
     * Setup field options total count.
     *
     * @return  $this
     *
     * @since   2.0
     */
    abstract public function setFieldOptionsCount();

    /**
     * Set request filter data from form.
     *
     * @param   mixed   $data
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setFilterData($data)
    {
        //  Remove empty data.
        foreach ((array) $data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        $this->_filterData = new JSON((array) $data);
        return $this;
    }

    /**
     * Set items for render.
     *
     * @param   array  $items
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        return $this;
    }

    /**
     * Setup price range conditions.
     *
     * @param   string  $conditions
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function setPriceRangeConditions(&$conditions)
    {
        $db = $this->hyper['db'];

        list ($priceFrom, $priceTo) = $this->getPriceRange();

        $priceFrom = (float) $priceFrom;
        if ($priceFrom <= 0) {
            $priceFrom = $this->getExtremePriceValue('min', true);
        }

        $priceTo = (float) $priceTo;
        if ($priceTo <= 0) {
            $priceTo = $this->getExtremePriceValue('max', true);
        }

        $conditions .= $db->qn('tIndex.price_b')  . ' >= ' . $priceFrom . ' AND ' . $db->qn('tIndex.price_b')  . ' <= ' . $priceTo . PHP_EOL;

        return $this;
    }

    /**
     * Set core query dump.
     *
     * @param   string  $queryDump
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setQueryDump($queryDump)
    {
        $this->_queryDump = $queryDump;
        return $this;
    }

    /**
     * Setup custom conditions.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract protected function _setConditions();

    /**
     * Setup and get head SQL filter query.
     *
     * @param   array  $select
     *
     * @return  \JDatabaseQueryMysqli
     *
     * @since   2.0
     */
    abstract protected function _setHeadQuery(array $select = ['stock.*', 'tIndex.*']);

    /**
     * Set class.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _getClass()
    {
        if (empty($this->_class)) {
            $details = explode('\\', get_class($this));
            $this->_class = end($details);
        }

        return $this->_class;
    }

    /**
     * Set filter type.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setType()
    {
        $this->_type = str_replace(['_filter', 'Filter'], '', Inflector::underscore($this->_getClass()));
    }

    /**
     * Set url filter data.
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setUrlFilterData()
    {
        /** @var FilterHelper|MoyskladFilterHelper $filterHelper */
        $filterHelper = $this->getFilterHelper();

        $allowedFields = $filterHelper->getAllowedFields();
        if (count($allowedFields)) {
            /** @var Field $field */
            foreach ($allowedFields as $field) {
                if ($this->hyper['input']->exists($field->name)) {
                    $this->_filterData->set(
                        $field->name,
                        explode(FilterData::QUERY_DATA_SEPARATOR, $this->hyper['input']->get($field->name, null, 'string'))
                    );
                }
            }

            //  Find and set price filter value.
            $priceRangeName = FilterData::PRICE_FIELD_NAME;
            if ($this->hyper['input']->exists($priceRangeName)) {
                $this->_filterData->set($priceRangeName, $this->hyper['input']->get($priceRangeName, null, 'string'));
            }

            //  Find and set store filter value.
            $storeName = FilterData::STORE_FIELD_NAME;
            if ($this->hyper['input']->exists($storeName)) {
                $this->_filterData->set($storeName, explode(
                    FilterData::QUERY_DATA_SEPARATOR,
                    $this->hyper['input']->get($storeName, null, 'string')
                ));
            }

            //  Find and set category filter value.
            $categoryName = FilterData::CATEGORY_FIELD_NAME;
            if ($this->hyper['input']->exists($categoryName)) {
                $this->_filterData->set($categoryName, explode(
                    FilterData::QUERY_DATA_SEPARATOR,
                    $this->hyper['input']->get($categoryName, null, 'string')
                ));
            }
        }

        return $this;
    }
}
