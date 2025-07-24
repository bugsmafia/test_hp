<?php
namespace HYPERPC\Filters;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use HYPERPC\ORM\Filter\AbstractFilter;
use HYPERPC\App;

/**
 * Class MoyskladProductIndexFilter
 *
 * @package HYPERPC\Filters
 * @since   2.0
 */
class MoyskladProductIndexFilter extends AbstractFilter
{
    /**
     * Database table name for product index.
     *
     * @var string
     */
    public $tableName = '#__hp_moysklad_products_index';

    /**
     * Context for filter.
     *
     * @var string
     */
    public string $context = 'com_hyperpc.position'; // Явно указан тип string и видимость public

    /**
     * Category key for filter.
     *
     * @var string
     */
    public $categoryKey = 'product_folder_id';

    /**
     * Filter data.
     *
     * @var \Joomla\Registry\Registry
     */
    protected $_filterData;

    /**
     * Query dump.
     *
     * @var string
     */
    protected $_queryDump = '';

    /**
     * Filter type.
     *
     * @var string
     */
    protected $_type = 'moysklad_product_index';

    /**
     * Items array.
     *
     * @var array
     */
    protected $items = [];

    
    /**
     * Constructor.
     *
     * @param array $hyper Application instance
     */
    public function __construct($hyper)
    {
        if (!$hyper instanceof App) {
            Log::add('Invalid hyper parameter in MoyskladProductIndexFilter', Log::ERROR, 'com_hyperpc');
            throw new \InvalidArgumentException('Invalid hyper parameter');
        }

        $this->hyper = $hyper;
        $this->_filterData = new Registry();
        Log::add('MoyskladProductIndexFilter initialized', Log::DEBUG, 'com_hyperpc');
    }

    /**
     * Find property count.
     *
     * @param string $alias
     * @param string $value
     * @param array $conditions
     * @return int
     */
    public function findPropertyCount(string $alias, string $value, array $conditions = []): int
    {
        dump(__LINE__.__DIR__." --- MoyskladProductIndexFilter::findPropertyCount() --- alias: {$alias}, value: {$value}, conditions: " . print_r($conditions, true));

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(DISTINCT ' . $db->quoteName('id') . ')')
            ->from($db->quoteName($this->tableName))
            ->where($db->quoteName($alias) . ' = ' . $db->quote($value));

        foreach ($conditions as $key => $values) {
            if (!empty($values) && $key !== $alias) {
                if ($key === 'price_a') {
                    if (!empty($values['min'])) {
                        $query->where($db->quoteName('price_a') . ' >= ' . (float)$values['min']);
                    }
                    if (!empty($values['max'])) {
                        $query->where($db->quoteName('price_a') . ' <= ' . (float)$values['max']);
                    }
                } else {
                    $quotedValues = array_map([$db, 'quote'], (array)$values);
                    $query->where($db->quoteName($key) . ' IN (' . implode(',', $quotedValues) . ')');
                }
            }
        }

        try {
            dump(__LINE__.__DIR__." --- Executing query: " . $query->dump());
            $count = (int)$db->setQuery($query)->loadResult();
            Log::add("Property count for {$alias} = {$value}: {$count}", Log::DEBUG, 'com_hyperpc');
            return $count;
        } catch (\Throwable $e) {
            Log::add("Error in findPropertyCount for {$alias} = {$value}: " . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return 0;
        }
    }

    /**
     * Get store helper.
     *
     * @return mixed
     */
    public function getStoreHelper()
    {
        return $this->hyper['helper']['store'] ?? null;
    }

    /**
     * Get category helper.
     *
     * @return mixed
     */
    public function getCategoryHelper()
    {
        return $this->hyper['helper']['productFolder'] ?? null;
    }

    /**
     * Get stock table name.
     *
     * @return string
     */
    public function getStockTable(): string
    {
        return $this->tableName;
    }

    /**
     * Get products table name.
     *
     * @return string
     */
    public function getProductsTable(): string
    {
        return 'p6wjk_hp_positions';
    }

    /**
     * Get index table name.
     *
     * @return string
     */
    public function getIndexTable(): string
    {
        return $this->tableName;
    }

    /**
     * Get stores.
     *
     * @return array
     */
    public function getStores(): array
    {
        return [];
    }

    /**
     * Render filter output.
     *
     * @return string
     */
    public function render(): string
    {
        return '';
    }

    /**
     * Set query conditions (override to maintain compatibility with AbstractFilter).
     *
     * @return void
     */
    protected function _setConditions()
    {
        // Пустая реализация или вызов родительского метода
        parent::_setConditions();
    }

    /**
     * Set query conditions for MoyskladProductIndexFilter.
     *
     * @param \JDatabaseQuery $query
     * @return void
     */
    protected function _setMoyskladConditions($query)
    {
        $db = Factory::getDbo();
        $filters = $this->filters ? json_decode($this->getCurrentFilters()->getRaw(), true) : [];

        Log::add('Filters in _setMoyskladConditions: ' . json_encode($filters), Log::DEBUG, 'com_hyperpc');
        Log::add('Hyper params in _setMoyskladConditions: ' . json_encode($this->hyper['params']->toArray()), Log::DEBUG, 'com_hyperpc');

        $allowedFilters = $this->hyper['params'] ? $this->hyper['params']->get('filter_product_allowed_moysklad', []) : [];
        if (empty($allowedFilters)) {
            Log::add('No allowed filters in MoyskladProductIndexFilter, trying filter_product_allowed', Log::WARNING, 'com_hyperpc');
            $allowedFilters = $this->hyper['params'] ? $this->hyper['params']->get('filter_product_allowed', []) : [];
        }

        Log::add('Allowed filters in _setMoyskladConditions: ' . json_encode($allowedFilters), Log::DEBUG, 'com_hyperpc');

        $productFolderId = 116;
        $query->where($db->quoteName('pos.product_folder_id') . ' = ' . (int)$productFolderId);

        foreach ($allowedFilters as $filter) {
            $fieldId = $filter['id'] ?? null;
            if (!$fieldId) {
                continue;
            }

            $fieldQuery = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__fields'))
                ->where($db->quoteName('id') . ' = ' . (int)$fieldId);
            $fieldName = $db->setQuery($fieldQuery)->loadResult();

            if ($fieldName && !empty($filters[$fieldName])) {
                $values = (array)$filters[$fieldName];
                $quotedValues = array_map([$db, 'quote'], $values);
                $query->where($db->quoteName('p.' . $fieldName) . ' IN (' . implode(',', $quotedValues) . ')');
            }
        }

        if (!empty($filters['price_a']['min']) && is_numeric($filters['price_a']['min'])) {
            $query->where($db->quoteName('p.price_a') . ' >= ' . (float)$filters['price_a']['min']);
        }
        if (!empty($filters['price_a']['max']) && is_numeric($filters['price_a']['max'])) {
            $query->where($db->quoteName('p.price_a') . ' <= ' . (float)$filters['price_a']['max']);
        }
    }

    /**
     * Set head query.
     *
     * @param array $select
     * @return void
     * 
     * abstract protected function _setHeadQuery(array $select = ['stock.*', 'tIndex.*']);
     */
    protected function _setHeadQuery(array $select = [])
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Default select fields if not provided
        $select = !empty($select) ? $select : [
            'p.product_id',
            'pos.name',
            'pos.alias',
            'pos.images',
            'p.in_stock',
            'p.price_a'
        ];

        $query->select(array_map([$db, 'quoteName'], $select))
            ->from($db->quoteName($this->tableName, 'p'))
            ->join('LEFT', $db->quoteName('#__hp_positions', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('p.product_id'));
        dump(__LINE__.__DIR__." --- MoyskladProductIndexFilter::_setHeadQuery() --- " . $query->dump());
        
        return $query;
    }



    /**
     * Get filter data for products in stock.
     *
     * @param array $filters Current filter parameters
     * @return array
     */
    public function getFilterData(array $filters = []): array
    {
        $db = Factory::getDbo();
        $allowedFilters = !empty($this->hyper['params']) && $this->hyper['params'] instanceof Registry
            ? $this->hyper['params']->get('filter_product_allowed_moysklad', [])
            : [];

        if (empty($allowedFilters)) {
            Log::add('No allowed filters in MoyskladProductIndexFilter', Log::WARNING, 'com_hyperpc');
        }

        // Prepare filter conditions
        $conditions = [];
        foreach ($filters as $key => $values) {
            if (!empty($values) && $key !== 'price_a') {
                $quotedValues = array_map([$db, 'quote'], (array)$values);
                $conditions[] = $db->quoteName($key) . ' IN (' . implode(',', $quotedValues) . ')';
            }
        }

        // Handle price filter
        if (!empty($filters['price_a']['min'])) {
            $conditions[] = $db->quoteName('price_a') . ' >= ' . (float)$filters['price_a']['min'];
        }
        if (!empty($filters['price_a']['max'])) {
            $conditions[] = $db->quoteName('price_a') . ' <= ' . (float)$filters['price_a']['max'];
        }

        // Get unique filter values and counts
        $filterData = ['available' => [], 'current' => $filters];
        $factory = new FilterFactory($this->hyper);

        foreach ($allowedFilters as $filter) {
            $fieldName = $this->getFieldName($filter['id']);
            if (!$fieldName) {
                Log::add('Field name not found for ID ' . $filter['id'], Log::WARNING, 'com_hyperpc');
                continue;
            }

            try {
                $fieldFilter = $factory->create($this->context, ['field' => $fieldName]);
                $fieldOptions = $this->getFieldOptions($fieldFilter, $filters, $fieldName);
            } catch (\Throwable $e) {
                Log::add('Error creating filter for field ' . $fieldName . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
                continue;
            }

            foreach ($fieldOptions as $value => $data) {
                $filterData['available'][$fieldName][$value] = [
                    'value' => $value,
                    'name' => $data['name'],
                    'count' => $data['count']
                ];
            }
        }

        // Get min and max prices
        $priceQuery = $db->getQuery(true)
            ->select([
                'MIN(' . $db->quoteName('price_a') . ') AS min_price',
                'MAX(' . $db->quoteName('price_a') . ') AS max_price'
            ])
            ->from($db->quoteName($this->tableName))
            ->where($conditions);

        try {
            $prices = $db->setQuery($priceQuery)->loadObject();
            $filterData['prices'] = [
                'min' => $prices ? (float)$prices->min_price : 0,
                'max' => $prices ? (float)$prices->max_price : 0
            ];
        } catch (\Throwable $e) {
            Log::add('Error fetching prices: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            $filterData['prices'] = ['min' => 0, 'max' => 0];
        }

        $this->_filterData->set('filters', $filterData);
        Log::add('Filter data generated: ' . print_r($filterData, true), Log::DEBUG, 'com_hyperpc');
        return $filterData;
    }


    /**
     * Get filtered items.
     *
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getItems(array $filters = [], int $offset = 0, int $limit = 10): array
    {
        $db = Factory::getDbo();
        $query = $this->_setHeadQuery();
        $this->setCurrentFilters($filters);
        $this->_setMoyskladConditions($query);
        $query->setLimit($limit, $offset);

        try {
            $results = $db->setQuery($query)->loadObjectList();
            Log::add('Query executed in getItems: ' . $query->dump(), Log::DEBUG, 'com_hyperpc');
        } catch (\Throwable $e) {
            Log::add('Error fetching items: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }

        $products = [];
        foreach ($results as $result) {
            try {
                $product = new \stdClass();
                $product->id = $result->product_id;
                $product->name = $result->name;
                $product->alias = $result->alias;
                $product->images = $result->images;
                $product->saved_configuration = $result->in_stock;
                $product->list_price = $result->price_a;

                // Данные о сборке
                if ($result->in_stock) {
                    $configQuery = $db->getQuery(true)
                        ->select('parts')
                        ->from($db->quoteName('#__hp_saved_configurations'))
                        ->where($db->quoteName('id') . ' = ' . (int)$result->in_stock);
                    $config = $db->setQuery($configQuery)->loadResult();
                    $product->configuration_parts = $config ? json_decode($config, true) : [];
                } else {
                    $product->configuration_parts = [];
                }

                // Данные о комплектации
                $configValuesQuery = $db->getQuery(true)
                    ->select(['value', 'part_id', 'option_id'])
                    ->from($db->quoteName('#__hp_products_config_values'))
                    ->where($db->quoteName('product_id') . ' = ' . (int)$result->product_id)
                    ->where($db->quoteName('stock_id') . ' = ' . (int)$result->in_stock);
                $product->config_values = $db->setQuery($configValuesQuery)->loadObjectList() ?: [];

                $products[] = $product;
            } catch (\Throwable $e) {
                Log::add('Error processing product ID ' . $result->product_id . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            }
        }

        return $products;
    }

    /**
     * Get filter state.
     *
     * @return array
     */
    public function getState(): array
    {
        try {
            $state = [];
            $filters = $this->getCurrentFilters()->toArray();
            dump(__LINE__.__DIR__." --- filters --- ");
            dump($filters);


            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $state[$key] = (array)$value;
                }
            }

            Log::add('Filter state in MoyskladProductIndexFilter: ' . print_r($state, true), Log::DEBUG, 'com_hyperpc');
            return $state;
        } catch (\Throwable $e) {
            Log::add('Error getting filter state in MoyskladProductIndexFilter: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }
    }

    /**
     * Check if filters are applied.
     *
     * @return bool
     */
    public function hasFilters(): bool
    {
        $filters = $this->getState();
        foreach ($filters as $values) {
            if (!empty($values)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if items exist for current filters.
     *
     * @param array $filters
     * @return bool
     */
    public function hasItems(array $filters = []): bool
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($this->tableName));
        $this->_setConditions($query, $filters);
        dump(__LINE__.__DIR__." --- Executing hasItems query: " . $query->dump());
        try {
            return (int)$db->setQuery($query)->loadResult() > 0;
        } catch (\Throwable $e) {
            Log::add('Error checking items: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return false;
        }
    }

    /**
     * Get field name from Joomla fields.
     *
     * @param int $fieldId
     * @return string|null
     */
    protected function getFieldName($fieldId)
    {
        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('p6wjk_fields'))
                ->where($db->quoteName('id') . ' = ' . (int)$fieldId);
            $db->setQuery($query);
            $fieldName = $db->loadResult();
            Log::add('Field name for ID ' . $fieldId . ': ' . $fieldName, Log::DEBUG, 'com_hyperpc');
            return $fieldName;
        } catch (\Throwable $e) {
            Log::add('Error fetching field name for ID ' . $fieldId . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return null;
        }
    }

    /**
     * Get display name for filter value.
     *
     * @param array $filter
     * @param string $value
     * @return string
     */
    protected function getFilterName(array $filter, string $value): string
    {
        $fieldId = $filter['id'];
        try {
            $field = $this->hyper['helper']['fields'] ? $this->hyper['helper']['fields']->getFieldById($fieldId) : null;
            if ($field) {
                $options = $field->fieldparams->get('options', []);
                foreach ($options as $option) {
                    if ($option['value'] === $value) {
                        return $this->hyper['helper']['string'] 
                            ? $this->hyper['helper']['string']->filterLanguage($option['name']) 
                            : $value;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::add('Error getting filter name for field ID ' . $fieldId . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
        }
        return $value;
    }

    /**
     * Get field options with counts.
     *
     * @param AbstractFilter $fieldFilter
     * @param array $filters
     * @param string $fieldName
     * @return array
     */
    protected function getFieldOptions($fieldFilter, array $filters, string $fieldName): array
    {
        $db = Factory::getDbo();
        $conditions = [];
        foreach ($filters as $key => $values) {
            if (!empty($values) && $key !== $fieldName && $key !== 'price_a') {
                $quotedValues = array_map([$db, 'quote'], (array)$values);
                $conditions[] = $db->quoteName($key) . ' IN (' . implode(',', $quotedValues) . ')';
            }
        }

        if (!empty($filters['price_a']['min'])) {
            $conditions[] = $db->quoteName('price_a') . ' >= ' . (float)$filters['price_a']['min'];
        }
        if (!empty($filters['price_a']['max'])) {
            $conditions[] = $db->quoteName('price_a') . ' <= ' . (float)$filters['price_a']['max'];
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName($fieldName),
                'COUNT(DISTINCT ' . $db->quoteName('id') . ') AS count'
            ])
            ->from($db->quoteName($this->tableName))
            ->where($conditions)
            ->group($db->quoteName($fieldName));

        try {
            $results = $db->setQuery($query)->loadObjectList();
            Log::add('Field options query for ' . $fieldName . ': ' . $query->dump(), Log::DEBUG, 'com_hyperpc');
        } catch (\Throwable $e) {
            Log::add('Error fetching field options for ' . $fieldName . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }

        $options = [];
        foreach ($results as $result) {
            $value = $result->$fieldName;
            if (!empty($value)) {
                $options[$value] = [
                    'value' => $value,
                    'name' => $this->getFilterName(['id' => $this->getFieldId($fieldName)], $value),
                    'count' => (int)$result->count
                ];
            }
        }

        return $options;
    }

    /**
     * Get field ID from field name.
     *
     * @param string $fieldName
     * @return int|null
     */
    protected function getFieldId(string $fieldName): ?int
    {
        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__fields'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($fieldName));
            $db->setQuery($query);
            $fieldId = (int)$db->loadResult();
            Log::add('Field ID for name ' . $fieldName . ': ' . $fieldId, Log::DEBUG, 'com_hyperpc');
            return $fieldId;
        } catch (\Throwable $e) {
            Log::add('Error fetching field ID for ' . $fieldName . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return null;
        }
    }

    /**
     * Get filter data as JSON.
     *
     * @return Registry
     */
    public function getFilterDataJson(): Registry
    {
        try {
            $filters = $this->hyper['input'] ? $this->hyper['input']->get('filters', [], 'array') : [];
            $this->getFilterData($filters);
        } catch (\Throwable $e) {
            Log::add('Error getting filter data JSON: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
        }
        return $this->_filterData;
    }

    /**
     * Get filter type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }


    /**
     * Find items.
     *
     * @return void
     */
    public function find()
    {
        try {
            $db = Factory::getDbo();
            $query = $this->_setHeadQuery();
            $this->_setMoyskladConditions($query);
            $db->setQuery($query);
            $this->items = $db->loadObjectList();
            Log::add('Query executed in find: ' . $query->dump(), Log::DEBUG, 'com_hyperpc');

            foreach ($this->items as $item) {
                $item->id = $item->product_id;
                $item->saved_configuration = $item->in_stock;
                $item->list_price = $item->price_a;

                if ($item->in_stock) {
                    $configQuery = $db->getQuery(true)
                        ->select('parts')
                        ->from($db->quoteName('#__hp_saved_configurations'))
                        ->where($db->quoteName('id') . ' = ' . (int)$item->in_stock);
                    $config = $db->setQuery($configQuery)->loadResult();
                    $item->configuration_parts = $config ? json_decode($config, true) : [];
                } else {
                    $item->configuration_parts = [];
                }

                $configValuesQuery = $db->getQuery(true)
                    ->select(['value', 'part_id', 'option_id'])
                    ->from($db->quoteName('#__hp_products_config_values'))
                    ->where($db->quoteName('product_id') . ' = ' . (int)$item->product_id)
                    ->where($db->quoteName('stock_id') . ' = ' . (int)$item->in_stock);
                $item->config_values = $db->setQuery($configValuesQuery)->loadObjectList() ?: [];
            }
        } catch (\Throwable $e) {
            Log::add('Error in find: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), Log::ERROR, 'com_hyperpc');
            $this->items = [];
        }
    }

    /**
     * Get items (non-paginated).
     *
     * @return array
     */
    public function getItemsNonPaginated(): array
    {
        $filters = $this->getCurrentFilters()->toArray();
        return $this->getItems($filters, 0, 0);
    }

    /**
     * Get current filters.
     *
     * @return Registry
     */
    public function getCurrentFilters(): Registry
    {
        return new Registry($this->hyper['input'] ? $this->hyper['input']->get('filter', [], 'array') : []);
    }

    /**
     * Set field options count.
     *
     * @return void
     */
    public function setFieldOptionsCount()
    {
        $filters = $this->getCurrentFilters()->toArray();
        $this->getFilterData($filters);
        Log::add('Field options count set', Log::DEBUG, 'com_hyperpc');
    }

    /**
     * Get initial state for filters.
     *
     * @return array
     */
    public function getInitState(): array
    {
        $allowedFields = $this->hyper['params'] instanceof Registry 
            ? $this->hyper['params']->get('filter_product_allowed_moysklad', []) 
            : [];
        $initState = [];
        dump(__LINE__.__DIR__." --- Initializing filter state with allowed fields: " . print_r($allowedFields, true));

        foreach ($allowedFields as $field) {
            $fieldName = $this->getFieldName($field['id']);
            if ($fieldName) {
                $initState[$fieldName] = [
                    'title' => $field['title'],
                    'id' => $field['id'],
                    'group_id' => $field['group_id'],
                    'from' => $field['from'],
                    'options' => $this->getFieldOptions(new Filter($this->hyper), [], $fieldName)
                ];
            }
        }

        Log::add('Initial filter state: ' . print_r($initState, true), Log::DEBUG, 'com_hyperpc');
        return $initState;
    }

    /**
     * Get query dump.
     *
     * @return string
     */
    public function getQueryDump(): string
    {
        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $this->_setHeadQuery($query);
            $filters = $this->getCurrentFilters()->toArray();
            $this->_setConditions($query, $filters);

            $dump = $query->dump();
            Log::add('Query dump: ' . $dump, Log::DEBUG, 'com_hyperpc');
            return $dump;
        } catch (\Throwable $e) {
            Log::add('Error in getQueryDump: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return '';
        }
    }

    /*
    * Set filter data.
    *
    * @param mixed $data
    * @return void
    */
    public function setFilterData($data)
    {
        $this->_filterData = $data instanceof Registry ? $data : new Registry($data);
        Log::add('Filter data set: ' . print_r($this->_filterData->toArray(), true), Log::DEBUG, 'com_hyperpc');
    }

    /**
     * Get filter helper.
     *
     * @return mixed
     */
    public function getFilterHelper()
    {
        dump(__LINE__.__DIR__." --- MoyskladProductIndexFilter::getFilterHelper() --- ");
        return $this->hyper['helper']['filter'] ?? null;
    }

    /**
     * Get group helper.
     *
     * @return mixed
     */
    public function getGroupHelper()
    {
        dump(__LINE__.__DIR__." --- MoyskladProductIndexFilter::getGroupHelper() --- ");
        return $this->hyper['helper']['productFolder'] ?? null;
    }

    /**
     * Get options helper.
     *
     * @return mixed
     */
    public function getOptionsHelper()
    {
        dump(__LINE__.__DIR__." --- MoyskladProductIndexFilter::getOptionsHelper() --- ");
        return $this->hyper['helper']['options'] ?? null;
    }


}