<?php
namespace HYPERPC\Filters;

use HYPERPC\App;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseDriver;
use HYPERPC\Filters\Filter;
use HYPERPC\Filters\FilterFactory;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use Joomla\CMS\Log\Log;

/**
 * Class MoyskladProductIndexFilter
 *
 * @package HYPERPC\Filters
 * @since   2.0
 */
class MoyskladProductIndexFilter extends Filter
{
    /**
     * Database table name for product index.
     *
     * @var string
     */
    protected $tableName = 'p6wjk_hp_moysklad_products_index';
    protected $context = 'com_hyperpc.position';
    protected $categoryKey = 'product_folder_id';
    protected $_filterData;
    protected $_type = 'moysklad_product_index';

    /**
     * Constructor.
     *
     * @param array $hyper Application instance
     */
    public function __construct($hyper)
    {
        if (!is_array($hyper) && !is_object($hyper)) {
            Log::add('Invalid hyper parameter in MoyskladProductIndexFilter', Log::ERROR, 'com_hyperpc');
            throw new \InvalidArgumentException('Invalid hyper parameter');
        }
        parent::__construct($hyper);
        $this->_filterData = new Registry();
        dump(__LINE__.__DIR__." --- MoyskladProductIndexFilter hyper --- ");
        dump($hyper);
        exit;
        Log::add('MoyskladProductIndexFilter initialized with hyper: ' . print_r($hyper, true), Log::DEBUG, 'com_hyperpc');
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

        // Initialize filter factory
        $factory = new FilterFactory($this->hyper);
        
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
        foreach ($allowedFilters as $filter) {
            $fieldName = $this->getFieldName($filter['id']);
            if (!$fieldName) {
                continue;
            }

            // Use FilterFactory to create field-specific filter
            try {
                $fieldFilter = $factory->create($this->context, ['field' => $fieldName]);
                $fieldOptions = $this->getFieldOptions($fieldFilter, $filters, $fieldName);
            } catch (\Throwable $e) {
                Log::add('Error creating filter for field ' . $fieldName . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
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
        return $filterData;
    }

    /**
     * Get filtered items.
     *
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return ProductMarker[]
     */
    public function getItems(array $filters = [], int $offset = 0, int $limit = 10): array
    {
        $db = Factory::getDbo();
        $conditions = [];
        foreach ($filters as $key => $values) {
            if (!empty($values)) {
                if ($key === 'price_a') {
                    if (!empty($values['min'])) {
                        $conditions[] = $db->quoteName('price_a') . ' >= ' . (float)$values['min'];
                    }
                    if (!empty($values['max'])) {
                        $conditions[] = $db->quoteName('price_a') . ' <= ' . (float)$values['max'];
                    }
                } else {
                    $quotedValues = array_map([$db, 'quote'], (array)$values);
                    $conditions[] = $db->quoteName($key) . ' IN (' . implode(',', $quotedValues) . ')';
                }
            }
        }

        $query = $db->getQuery(true)
            ->select('a.*, p.name, p.alias, p.images, p.product_folder_id, p.type_id, p.state, p.ordering')
            ->from($db->quoteName($this->tableName, 'a'))
            ->join('INNER', $db->quoteName('p6wjk_hp_positions', 'p') . ' ON a.product_id = p.id')
            ->where($conditions)
            ->order($db->quoteName('p.ordering') . ' ASC')
            ->setLimit($limit, $offset);

        try {
            $results = $db->setQuery($query)->loadObjectList();
        } catch (\Throwable $e) {
            Log::add('Error fetching items: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }
        $products = [];

        foreach ($results as $result) {
            try {
                $product = $this->hyper['helper']['moyskladProduct'] ? $this->hyper['helper']['moyskladProduct']->findBy('id', $result->product_id) : null;
                if ($product instanceof ProductMarker) {
                    $product->set('saved_configuration', $result->in_stock);
                    $product->set('list_price', $this->hyper['helper']['money'] ? $this->hyper['helper']['money']->get($result->price_a) : $result->price_a);
                    $products[] = $product;
                }
            } catch (\Throwable $e) {
                Log::add('Error processing product ID ' . $result->product_id . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            }
        }
        dump(__LINE__.__DIR__." --- getItems --- ");
        exit;

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
            return $this->hyper['input'] ? $this->hyper['input']->get('filters', [], 'array') : [];
        } catch (\Throwable $e) {
            Log::add('Error getting filter state: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
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
        $conditions = [];
        foreach ($filters as $key => $values) {
            if (!empty($values)) {
                if ($key === 'price_a') {
                    if (!empty($values['min'])) {
                        $conditions[] = $db->quoteName('price_a') . ' >= ' . (float)$values['min'];
                    }
                    if (!empty($values['max'])) {
                        $conditions[] = $db->quoteName('price_a') . ' <= ' . (float)$values['max'];
                    }
                } else {
                    $quotedValues = array_map([$db, 'quote'], (array)$values);
                    $conditions[] = $db->quoteName($key) . ' IN (' . implode(',', $quotedValues) . ')';
                }
            }
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($this->tableName))
            ->where($conditions);

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
    protected function getFieldName(int $fieldId): ?string
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('p6wjk_fields'))
            ->where($db->quoteName('id') . ' = ' . (int)$fieldId);

        try {
            return $db->setQuery($query)->loadResult();
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
                        return $this->hyper['helper']['string'] ? $this->hyper['helper']['string']->filterLanguage($option['name']) : $value;
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
     * @param Filter $fieldFilter
     * @param array $filters
     * @param string $fieldName
     * @return array
     */
    protected function getFieldOptions(Filter $fieldFilter, array $filters, string $fieldName): array
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
                'COUNT(*) AS count'
            ])
            ->from($db->quoteName($this->tableName))
            ->where($conditions)
            ->group($db->quoteName($fieldName));

        try {
            $results = $db->setQuery($query)->loadObjectList();
        } catch (\Throwable $e) {
            Log::add('Error fetching field options for ' . $fieldName . ': ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
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
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('p6wjk_fields'))
            ->where($db->quoteName('name') . ' = ' . $db->quote($fieldName));

        try {
            return (int)$db->setQuery($query)->loadResult();
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
}