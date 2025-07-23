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

namespace HYPERPC\Filters;

use HYPERPC\App;
use HYPERPC\Helper\Traits\PartsFinder;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\ORM\Entity\Store;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class ProductFolderPartsFilter extends Filter
{
    use DatabaseAwareTrait;
    use PartsFinder;

    protected const DEFAULT_SHOW_COUNT = 12;
    protected const PRICE_RANGE_FILTER_NAME = 'price-range';
    protected const STORE_FILTER_NAME = 'store';
    protected const DEFAULT_PRICE_STEP = 100;

    protected App $hyper;

    protected array $allParts;

    protected ProductFolder $productFolder;

    protected array $filterFieldIds = [];

    protected bool $usePriceFilter = true;

    protected bool $useStoresFilter = true;

    protected Registry $requestVars;

    private array $fieldsCache = [];

    private array $storeAvailabilityCache;

    private array $filteredParts;

    protected $stateCache;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $app = Factory::getApplication();
        $this->hyper = App::getInstance();

        $input = $app->getInput();

        $productFolderId = $input->getInt('id');

        /** @var ProductFolder $productFolder */
        $productFolder = $this->hyper['helper']['productFolder']->findById($productFolderId);
        if (!$productFolder->id) {
            throw new \Exception(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
        }

        $this->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

        $this->productFolder = $productFolder;

        $folderParams = $productFolder->getParams();
        $this->filterFieldIds = $folderParams->get('allowed_filters', [], 'arr');
        $this->usePriceFilter = $folderParams->get('parts_allow_filter_by_price', false, 'bool');
        $this->useStoresFilter = $folderParams->get('parts_allow_filter_by_shops', false, 'bool');
        $this->requestVars = new Registry($input->getInputForRequestMethod()->getArray());
    }

    /**
     * Retrieves the list of items after applying all active filters.
     *
     * @return  array The list of filtered item identifiers.
     */
    public function getItems(): array
    {
        $filteredParts = $this->getFilteredParts();

        return array_values(array_map(fn($part) => $this->formatItemKey($part), $filteredParts));
    }

    /**
     * Retrieves the state of all filters, including available options and current selections.
     *
     * @return  array The filter state, including available options and active filters.
     */
    public function getState(): array
    {
        if (isset($this->stateCache)) {
            return $this->stateCache;
        }

        $currentFilters = $this->getFilteredRequestVars();
        $defaultFilters = $this->getDefaultFiltersState();

        if (empty($currentFilters)) {
            return [
                'available' => $this->postprocessAvailableFilters($defaultFilters),
                'current' => [],
                'url' => $this->getUrl([])
            ];
        }

        $_allFilters = array_combine(
            array_column($defaultFilters, 'key'),
            $defaultFilters
        );

        $currentFiltersKeys = array_keys($currentFilters);

        $emptyFilterGroups = array_filter(
            $_allFilters,
            fn($filterGroup) =>
                !in_array($filterGroup['key'], $currentFiltersKeys)
        );
        $checkedFilterGroups = array_intersect_key($_allFilters, $currentFilters);

        // populate checked filter groups
        if (count($checkedFilterGroups) > 1) {
            $allParts = $this->getAllParts();

            foreach ($checkedFilterGroups as $filterKey => $filterData) {
                if ($filterData['type'] !== 'checkboxes') {
                    continue;
                }

                $_currentFilters = $currentFilters;

                unset($_currentFilters[$filterKey]);

                $_parts = array_filter(
                    $allParts,
                    fn($part) => $this->applyFilters($part, $_currentFilters)
                );

                $options = $filterData['options'];
                $this->resetOptionsCount($options);
                if ($filterKey === static::STORE_FILTER_NAME) {
                    $this->populateStoreFilterValues($options, $_parts);
                } else {
                    $this->populateFieldFilterValues($options, $filterKey, $_parts);
                }

                $checkedFilterGroups[$filterKey]['options'] = $options;
            }
        }

        // populate empty filter groups
        $filteredParts = $this->getFilteredParts();

        foreach ($emptyFilterGroups as $key => $filterData) {
            switch ($filterData['type']) {
                case 'range':
                    if ($key === static::PRICE_RANGE_FILTER_NAME) {
                        $options = $filterData['options'];
                        $this->populatePriceRangeOptions($options, $filteredParts);

                        $emptyFilterGroups[$key]['options'] = $options;
                    }
                    break;
                case 'checkboxes':
                    $options = $filterData['options'];

                    $this->resetOptionsCount($options);
                    if ($key === static::STORE_FILTER_NAME) {
                        $this->populateStoreFilterValues($options, $filteredParts);
                    } else {
                        $this->populateFieldFilterValues($options, $key, $filteredParts);
                    }

                    $emptyFilterGroups[$key]['options'] = $options;
                    break;
            }
        }

        foreach ($_allFilters as $key => $value) {
            if (key_exists($key, $emptyFilterGroups)) {
                $_allFilters[$key] = $emptyFilterGroups[$key];
            } elseif (key_exists($key, $checkedFilterGroups)) {
                $_allFilters[$key] = $checkedFilterGroups[$key];
            }
        }

        $this->stateCache = [
            'available' => $this->postprocessAvailableFilters($_allFilters),
            'current' => $currentFilters,
            'url' => $this->getUrl($currentFilters)
        ];

        return $this->stateCache;
    }

    /**
     * Checks if there are any filters available for the current category.
     *
     * @return  bool True if filters are available, otherwise false.
     */
    public function hasFilters(): bool
    {
        if (
            $this->productFolder->getParams()->get('use_parts_filter', false, 'bool') &&
            ($this->useStoresFilter || $this->usePriceFilter || !empty($this->filterFieldIds))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if there are any items in the current category.
     *
     * @return  bool True if items exist, otherwise false.
     */
    public function hasItems(): bool
    {
        return count($this->getAllParts()) > 0;
    }

    /**
     * Applies the active filters to a given part and checks if it passes.
     *
     * @param   array $part The part to check against filters.
     * @param   array $filters The active filters to apply.
     *
     * @return  bool True if the part passes the filters, otherwise false.
     */
    protected function applyFilters(array $part, array $filters): bool
    {
        foreach ($filters as $key => $values) {
            switch ($key) {
                
                case static::STORE_FILTER_NAME: // filter by shop
                    if (!is_array($part['stores'])) {
                        return false;
                    }

                    if (!count(array_intersect($values, $part['stores']))) {
                        return false;
                    }

                    break;
                case static::PRICE_RANGE_FILTER_NAME: // filter by price
                    if ($part['list_price'] < $values['min'] || $part['list_price'] > $values['max']) {
                        return false;
                    }

                    break;
                default: // filter by fields
                    $partFields = $part['fields'] ?? [];

                    if (!key_exists($key, $partFields)) {
                        return false;
                    }

                    if (!in_array($partFields[$key], $values)) {
                        return false;
                    }

                    break;
            }
        }

        return true;
    }

    /**
     * Ceil numeric value to nearest by step.
     *
     * @param  int $value
     * @param  int $step
     *
     * @return int
     */
    protected function ceilRangeValue(int $value, int $step): int
    {
        return (int) (ceil($value / $step) * $step);
    }

    /**
     * Floor numeric value to nearest by step.
     *
     * @param  int $value
     * @param  int $step
     *
     * @return int
     */
    protected function floorRangeValue(int $value, int $step): int
    {
        return (int) (floor($value / $step) * $step);
    }

    /**
     * Retrieves the list of allowed filter keys for the current category.
     *
     * @return  array The list of allowed filter keys.
     */
    protected function getAllowedFilterKeys(): array
    {
        $keys = [];

        if ($this->useStoresFilter) {
            $keys[] = self::STORE_FILTER_NAME;
        }

        if ($this->usePriceFilter) {
            $keys[] = self::PRICE_RANGE_FILTER_NAME;
        }

        $keys = array_merge($keys, $this->getFilterFieldAliases());

        return $keys;
    }

    /**
     * Retrieves all parts for the current category, including their associated data.
     *
     * @return  array The list of parts.
     */
    protected function getAllParts(): array
    {
        if (isset($this->allParts)) {
            return $this->allParts;
        }

        $query = $this->getAllPartsQuery();
        $productFolderId = $this->productFolder->id;

        $db = $this->getDatabase();
        $query
            ->where($db->quoteName('positions.product_folder_id') . ' = :productFolderId')
            ->bind(':productFolderId', $productFolderId);

        // Exclude no retail parts
        if (!$this->productFolder->getParams()->get('show_inactive', true, 'bool')) {
            $folderRetail = $this->productFolder->getParams()->get('retail', false, 'bool');

            if ($folderRetail) {
                $query
                    ->whereIn($db->quoteName('parts.retail'), [HP_STATUS_PUBLISHED, HP_INHERIT_VALUE]);
            } else {
                $publishState = HP_STATUS_PUBLISHED;
                $query
                    ->where($db->quoteName('parts.retail') . ' = :retail')
                    ->bind(':retail', $publishState);
            }
        }

        $query
            ->order('in_stock DESC') // Items in stock come first
            ->order('list_price ASC'); // Then sort by price in ascending order

        $this->allParts = $db->setQuery($query)->loadAssocList();

        $this->setFieldValuesToParts($this->allParts);

        if ($this->useStoresFilter) {
            $this->setStoresToParts($this->allParts);
        }

        return $this->allParts;
    }

    /**
     * Retrieves the default state of all filters for the current category.
     *
     * @return  array The default state of filters.
     */
    protected function getDefaultFiltersState(): array
    {
        $filters = [];

        $allParts = $this->getAllParts();

        // filter by shops
        if ($this->useStoresFilter) {
            $options = [];

            /** @var Store[] */
            $shops = $this->hyper['helper']['store']->findAll();
            foreach ($shops as $id => $shop) {
                $name = $shop->getParam('city', $shop->name);

                $options[] = [
                    'name' => $name,
                    'value' => $id,
                    'count' => 0
                ];
            }

            $this->populateStoreFilterValues($options, $allParts);

            $options = array_filter($options, fn($option) => $option['count'] > 0);

            if (!empty($options)) {
                $filters[] = [
                    'key' => self::STORE_FILTER_NAME,
                    'title' => Text::_('COM_HYPERPC_FILTER_STORE_TOGGLE_TITLE'),
                    'type' => 'checkboxes',
                    'options' => $options
                ];
            }
        }

        // filter by price
        if ($this->usePriceFilter) {
            $moneyHelper = $this->hyper['helper']['money'];
            $currencySymbol = $moneyHelper->getCurrencySymbol($moneyHelper->get(0));

            $options = [];

            $this->populatePriceRangeOptions($options, $allParts);

            $filters[] = [
                'key' => self::PRICE_RANGE_FILTER_NAME,
                'title' => Text::sprintf('COM_HYPERPC_FILTER_PRICE_RANGE_TITLE', $currencySymbol),
                'type' => 'range',
                'options' => $options
            ];
        }

        // filter by fields
        foreach ($this->getFilterFields() as $field) {
            $options = (array) (new Registry($field['fieldparams']))->get('options', []);

            $options = array_map(function($option) {
                $option = (array) $option;
                return [
                    'value' => $option['value'],
                    'name' => $option['value'] !== 'none' ? $option['name'] : Text::_('COM_HYPERPC_NOT_DEFINED'),
                    'count' => 0
                ];
            }, $options);

            $options = array_combine(
                array_column($options, 'value'),
                $options
            );

            $this->populateFieldFilterValues($options, $field['name'], $allParts);

            $options = array_filter($options, fn($option) => $option['count'] > 0);

            if (!empty($options)) {
                $filters[] = [
                    'key' => $field['name'],
                    'title' => $field['title'],
                    'type' => 'checkboxes',
                    'options' => $options
                ];
            }
        }

        return $filters;
    }

    /**
     * Extracts and normalizes the filter-related request variables.
     *
     * @return  array The normalized request variables.
     */
    protected function getFilteredRequestVars(): array
    {
        $allowedKeys = $this->getAllowedFilterKeys();

        $filteredVars = array_filter(
            $this->requestVars->toArray(),
            fn($key) => in_array($key, $allowedKeys, true),
            ARRAY_FILTER_USE_KEY
        );

        $this->normalizeRequestVars($filteredVars);

        return $filteredVars;
    }

    /**
     * Retrieves the aliases for filter fields configured in the current category.
     *
     * @return  array The list of filter field aliases.
     */
    protected function getFilterFieldAliases(): array
    {
        return array_column($this->getFilterFields(), 'name');
    }

    /**
     * Retrieves the configured filter fields for the current category.
     *
     * @return  array The list of filter fields.
     */
    protected function getFilterFields(): array
    {
        if (!empty($this->fieldsCache)) {
            return $this->fieldsCache;
        }

        if (empty($this->filterFieldIds)) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('title'),
                $db->quoteName('fieldparams')
            ])
            ->from($db->quoteName(JOOMLA_TABLE_FIELDS))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $query->bindArray($this->filterFieldIds)) . ')')
            ->order($db->quoteName('ordering') . ' ASC');

        $this->fieldsCache = $db->setQuery($query)->loadAssocList();

        return $this->fieldsCache;
    }

    /**
     * Retrieves parts filtered based on the active filters.
     *
     * @return  array The list of filtered parts.
     */
    protected function getFilteredParts(): array
    {
        if (isset($this->filteredParts)) {
            return $this->filteredParts;
        }

        $allParts = $this->getAllParts();
        $currentFilters = $this->getFilteredRequestVars();

        $this->filteredParts = array_filter($allParts, fn($part) => $this->applyFilters($part, $currentFilters));

        return $this->filteredParts;
    }

    /**
     * Retrieves availability information for stores based on parts.
     *
     * @param   array|null $parts Optional list of parts to filter the availability by.
     *
     * @return  array Store availability data.
     */
    protected function getStoreAvailability(array $parts = null): array
    {
        if (isset($this->storeAvailabilityCache)) {
            return $this->storeAvailabilityCache;
        }

        if (!isset($parts)) {
            $parts = $this->getAllParts();
        }

        $db = $this->getDatabase();

        $itemIds = [];
        $optionConditions = [];

        foreach ($parts as $part) {
            if (!$part['in_stock']) {
                break;
            }

            if (empty($part['id'])) {
                continue;
            }

            if (!empty($part['option_id'])) {
                $optionConditions[] = 
                    '(' .
                        $db->quoteName('item_id') . ' = ' . (int) $part['id'] .
                        ' AND ' .
                        $db->quoteName('option_id') . ' = ' . (int) $part['option_id'] .
                    ')';
            } else {
                $itemIds[] = (int) $part['id'];
            }
        }

        if (empty($itemIds) && empty($optionConditions)) {
            $this->storeAvailabilityCache = [];
            return $this->storeAvailabilityCache;
        }

        $whereConditions = [];
        if (!empty($itemIds)) {
            $whereConditions[] =
                $db->quoteName('item_id') . ' IN (' . implode(',', $itemIds) . ')' . ' AND ' .
                $db->quoteName('option_id') . ' IS NULL';
        }

        if (!empty($optionConditions)) {
            $whereConditions[] = implode(' OR ', $optionConditions);
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('store_id'),
                $db->quoteName('item_id', 'id'),
                $db->quoteName('option_id')
            ])
            ->from($db->quoteName(HP_TABLE_MOYSKLAD_STORE_ITEMS))
            ->where('(' . implode(' OR ', $whereConditions) . ')');

        $results = $db->setQuery($query)->loadAssocList();

        $availabilityByShop = [];
        foreach ($results as $result) {
            $shopId = $result['shop_id'] ?? 1; /** @todo get shop id from warehouse */
            $itemKey = $this->formatItemKey($result);

            if (!key_exists($itemKey, $availabilityByShop)) {
                $availabilityByShop[$itemKey] = [$shopId];
            } elseif (!in_array($shopId, $availabilityByShop[$itemKey])) {
                $availabilityByShop[$itemKey][] = $shopId;
            }
        }

        $this->storeAvailabilityCache = $availabilityByShop;
        return $this->storeAvailabilityCache;
    }

    /**
     * Get relative url for current results.
     *
     * @param array $currentFilters
     *
     * @return string
     */
    protected function getUrl(array $currentFilters): string
    {
        $query = [];

        foreach ($currentFilters as $key => $values) {
            switch ($key) {
                case static::PRICE_RANGE_FILTER_NAME:
                    $query[$key] = implode(':', array_values($values));
                    break;
                default:
                    $query[$key] = implode('|', array_map(
                        fn($value) => urlencode($value),
                        $values
                    ));
                    break;
            }
        }

        $uri = Uri::getInstance();
        $uri->setQuery($query);

        return $uri->toString(['path', 'query']);
    }

    /**
     * Formats the unique key for an item based on its data.
     *
     * @param   array $data The item data.
     *
     * @return  string The formatted item key.
     */
    protected function formatItemKey(array $data): string
    {
        return 'position-' . $data['id'] . ($data['option_id'] ? '-' . $data['option_id'] : '');
    }

    /**
     * Normalizes request variables to ensure consistency.
     *
     * @param   array $vars The request variables to normalize.
     *
     * @return  void
     */
    protected function normalizeRequestVars(array &$vars): void
    {
        foreach ($vars as $key => $value) {
            switch ($key) {
                case static::PRICE_RANGE_FILTER_NAME:
                    $result = preg_match('/^(\d+):(\d+)$/', $value, $matches);
                    if (!$result) {
                        unset($vars[$key]);
                        break;
                    }

                    $parts = $this->getAllParts();
                    $prices = array_column($parts, 'list_price');
                    $step = static::DEFAULT_PRICE_STEP;

                    $minLimit = !empty($prices) ? $this->floorRangeValue(min($prices), $step) : 0;
                    $maxLimit = !empty($prices) ? $this->ceilRangeValue(max($prices), $step) : $minLimit + $step;

                    list(, $min, $max) = $matches;
                    $min = max($this->floorRangeValue((int) $min, $step), $minLimit);
                    $max = min($this->ceilRangeValue((int) $max, $step), $maxLimit);

                    if ($max < $min) {
                        $max = $maxLimit;
                    }

                    $vars[$key] = [
                        'min' => $min,
                        'max' => $max
                    ];
                    break;
                default:
                    if ($value === '') {
                        unset($vars[$key]);
                        break;
                    }

                    $vars[$key] = explode('|', $value);
                    break;
            }
        }
    }

    /**
     * Populates the options for the store filter based on the given parts.
     *
     * @param   array $options The options array to populate.
     * @param   array $parts The parts to calculate store options from.
     *
     * @return  void
     */
    protected function populateStoreFilterValues(array &$options, array $parts): void
    {
        $availbilityList = $this->getStoreAvailability();

        $itemKeys = array_map(
            fn($part) => $this->formatItemKey($part),
            $parts
        );

        $availbilityList = array_intersect_key($availbilityList, array_flip($itemKeys));

        $counts = [];
        foreach ($availbilityList as $shopIds) {
            foreach ($shopIds as $shopId) {
                if (!key_exists($shopId, $counts)) {
                    $counts[$shopId] = 1;
                } else {
                    $counts[$shopId]++;
                }
            }
        }

        foreach ($options as &$option) {
            $shopId = $option['value'];
            if (key_exists($shopId, $counts)) {
                $option['count'] = $counts[$shopId];
            }
        }
    }

    /**
     * Populates the options for the price range filter based on the given parts.
     *
     * @param   array $options The options array to populate.
     * @param   array $parts The parts to calculate price range options from.
     *
     * @return  void
     */
    protected function populatePriceRangeOptions(array &$options, array $parts): void
    {
        $step = $options['step'] ?? static::DEFAULT_PRICE_STEP;

        $prices = array_column($parts, 'list_price');

        $minPrice = !empty($prices) ? $this->floorRangeValue(min($prices), $step) : 0;
        $maxPrice = !empty($prices) ? $this->ceilRangeValue(max($prices), $step) : 0;

        $options['min'] = key_exists('min', $options) ? min($options['min'], $minPrice) : $minPrice;
        $options['max'] = key_exists('max', $options) ? max($options['max'], $maxPrice) : $maxPrice;

        $currentFilters = $this->getFilteredRequestVars();
        if (key_exists(static::PRICE_RANGE_FILTER_NAME, $currentFilters)) {
            $currentRange = $currentFilters[static::PRICE_RANGE_FILTER_NAME];

            $options['minValue'] = $currentRange['min'];
            $options['maxValue'] = $currentRange['max'];
        } else {
            $options['minValue'] = $minPrice;
            $options['maxValue'] = $maxPrice;
        }
    }

    /**
     * Populates the options for a specific field filter based on the given parts.
     *
     * @param   array $options The options array to populate.
     * @param   string $fieldName The name of the field to calculate options for.
     * @param   array $parts The parts to calculate field options from.
     *
     * @return void
     */
    protected function populateFieldFilterValues(array &$options, string $fieldName, array $parts): void
    {
        foreach ($parts as $part) {
            if (empty($part['fields'][$fieldName])) {
                continue;
            }

            $value = $part['fields'][$fieldName];

            if (key_exists($value, $options)) {
                $options[$value]['count']++;
            }
        }
    }

    /**
     * Performs post-processing on the available filters to finalize their options.
     *
     * @param   array $filters The filters to process.
     *
     * @return  array The processed filters.
     */
    protected function postprocessAvailableFilters($filters): array
    {
        $filters = array_map(function($filter) {
            if ($filter['type'] !== 'checkboxes' || !is_array($filter['options'])) {
                return $filter;
            }

            $filter['options'] = array_values($filter['options']);

            return $filter;
        }, $filters);

        return array_values($filters);
    }

    /**
     * Resets the count for all options in the given options array.
     *
     * @param   array $options The options array to reset.
     *
     * @return  void
     */
    protected function resetOptionsCount(array &$options): void
    {
        foreach ($options as &$option) {
            if (key_exists('count', $option)) {
                $option['count'] = 0;
            }
        }
    }

    /**
     * Sets field values to the given parts for easier filtering.
     *
     * @param   array $parts The parts to set field values to.
     *
     * @return  void
     */
    protected function setFieldValuesToParts(array &$parts): void
    {
        if (empty($parts)) {
            return;
        }

        $fields = array_column($this->getFilterFields(), 'name', 'id');

        if (empty($fields)) {
            foreach ($parts as &$part) {
                $part['fields'] = [];
            }
            return;
        }

        $itemIds = array_column($parts, 'id');
        $fieldIds = array_keys($fields);

        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([$db->quoteName('field_id'), $db->quoteName('item_id'), $db->quoteName('value')])
            ->from($db->quoteName(JOOMLA_TABLE_FIELDS_VALUES))
            ->where($db->quoteName('field_id') . ' IN (' . implode(',', $fieldIds) . ')')
            ->where($db->quoteName('item_id') . ' IN (' . implode(',', $itemIds) . ')');

        $results = $db->setQuery($query)->loadAssocList();

        $fieldValues = [];
        foreach ($results as $result) {
            $itemId = $result['item_id'];
            $fieldId = $result['field_id'];
            $value = $result['value'];

            $fieldName = $fields[$fieldId] ?? null;
            if ($fieldName) {
                $fieldValues[$itemId][$fieldName] = $value;
            }
        }

        $optionIds = array_column(array_filter(
            $parts,
            fn($part) => isset($part['option_id'])
        ), 'option_id');

        if (!empty($optionIds)) {
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('params')])
                ->from($db->quoteName(HP_TABLE_MOYSKLAD_VARIANTS))
                ->whereIn($db->quoteName('id'), $optionIds);

            $overrides = $db->setQuery($query)->loadAssocList('id', 'params');
            $overrides = array_map(
                fn($params) => (array) (new Registry($params))->get('options', []),
                $overrides
            );
        }

        foreach ($parts as &$part) {
            $partId = $part['id'];
            if (isset($fieldValues[$partId])) {
                $part['fields'] = $fieldValues[$partId];
            } else {
                $part['fields'] = [];
            }

            if (isset($part['option_id']) && key_exists($part['option_id'], $overrides)) {
                $override = $overrides[$part['option_id']];

                foreach ($override as $fieldKey => $fieldValue) {
                    if (key_exists($fieldKey, $part['fields']) && !in_array($fieldValue, ['', 'none'])) {
                        $part['fields'][$fieldKey] = $fieldValue;
                    }
                }
            }
        }
    }

    /**
     * Associates store availability data with the given parts.
     *
     * @param   array $parts The parts to associate store data with.
     *
     * @return  void
     */
    protected function setStoresToParts(array &$parts): void
    {
        $availability = $this->getStoreAvailability($parts);

        foreach ($parts as &$part) {
            if (!$part['in_stock']) {
                break;
            }

            $itemKey = $this->formatItemKey($part);
            $part['stores'] = $availability[$itemKey] ?? [];
        }
    }
}
