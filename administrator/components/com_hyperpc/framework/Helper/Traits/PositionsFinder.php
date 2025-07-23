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

namespace HYPERPC\Helper\Traits;

use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\Position;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Trait PositionsFinder
 *
 * @package HYPERPC\Helper\Traits
 */
trait PositionsFinder
{
    use DatabaseAwareTrait;
    use PartsFinder;

    /**
     * Find positions by conditions.
     *
     * @param   array  $data
     * @param   int    $offset
     * @param   int    $limit
     *
     * @return  Position[]
     *
     * @throws  \RuntimeException
     */
    public function findByConditions(array $data, $limit = 0, $offset = 0): array
    {
        $this->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

        $data = $this->normalizeData($data);

        $type = $data->get('type');

        if ($type === 'service') {
            return $this->findServices($data, $limit, $offset);
        } elseif ($type === 'part') {
            return $this->findParts($data, $limit, $offset);
        } elseif ($type === 'product') {
            return $this->findProducts($data, $limit, $offset);
        }

        return [];
    }

    /**
     * Normalize input array.
     *
     * @param   array $data
     *
     * @return  Registry
     */
    public function normalizeData(array $data): Registry
    {
        $data = new Registry($data);

        foreach ($data as $key => $value) {
            $data->offsetSet($key, match ($key) {

                // string
                'order',
                'type',
                'game',
                'layout',
                'priceRange',
                'instock',
                'field'
                    => \is_array($value) ? \array_shift($value) : $value,

                // integer
                'initialAmount',
                'limit',
                'offset'
                    => (int) (\is_array($value) ? \array_shift($value) : $value),

                // boolean
                'loadUnavailable',
                'showFps'
                    => (bool) (\is_array($value) ? \array_shift($value) : $value),

                default => $value
            });
        }

        $defaults = [
            'product' => [
                'config' => [],
                'game' => '',
                'ids' => [],
                'initialAmount' => 0,
                'instock' => 'except',
                'layout' => 'default',
                'limit' => 0,
                'loadUnavailable' => false,
                'offset' => 0,
                'order' => 'price ASC',
                'platform' => [],
                'priceRange' => '',
                'showFps' => true,
                'type' => 'product'
            ],
            'part' => [
                'ids' => [],
                'productFolderIds' => [],
                'initialAmount' => 0,
                'limit' => 0,
                'offset' => 0,
                'order' => 'a.price ASC',
                'field' => '',
                'fieldValue' => [],
                'priceRange' => '',
                'type' => 'part',
                'layout' => 'part'
            ],
            'service' => [
                'ids' => [],
                'productFolderIds' => [],
                'initialAmount' => 0,
                'limit' => 0,
                'offset' => 0,
                'order' => 'a.price ASC',
                'priceRange' => '',
                'type' => 'service',
                'layout' => 'service'
            ]
        ];

        $type = $data->get('type', 'product');
        if ($data->get('config') || $data->get('game')) {
            $type = 'product';
        }

        if (!\key_exists($type, $defaults)) {
            return new Registry($defaults['product']);
        }

        return new Registry(\array_intersect_key(\array_merge($defaults[$type], $data->toArray()), $defaults[$type]));
    }

    /**
     * Find products by conditions.
     *
     * @param   Registry $data
     * @param   int $limit
     * @param   int $offset
     *
     * @return  Position[]
     *
     * @throws  \RuntimeException
     */
    private function findProducts(Registry $data, int $limit, int $offset): array
    {
        $db = $this->getDatabase();

        $order = preg_replace('/a\./', '', (string) current((array) $data->get('order', 'a.id')));

        $query = $db->getQuery(true);
        $query
            ->select([
                $db->quoteName('position.id', 'id'),
                $db->quoteName('position.name', 'name'),
                $db->quoteName('index.in_stock', 'stock_id'),
                $db->quoteName('index.price_a', 'price'),
            ])
            ->from($db->quoteName(HP_TABLE_MOYSKLAD_PRODUCTS_INDEX, 'index'))
            ->leftJoin(
                $db->quoteName(HP_TABLE_POSITIONS, 'position'),
                $db->quoteName('index.product_id') . ' = ' . $db->quoteName('position.id'))
            ->order($order);

        // Stock state
        $stockState = $data->get('instock');
        switch ($stockState) {
            case 'only':
                $query->where($db->quoteName('index.in_stock') . ' IS NOT NULL');
                break;
            case 'except':
                $query->where('(' .
                    $db->quoteName('position.state') . ' = ' . HP_STATUS_PUBLISHED . ' AND ' .
                    $db->quoteName('index.in_stock') . ' IS NULL)'
                );
                break;
            default:
                $query->where('(' .
                    $db->quoteName('position.state') . ' = ' . HP_STATUS_PUBLISHED . ' OR ' .
                    $db->quoteName('index.in_stock') . ' IS NOT NULL)'
                );
                break;
        }

        // Exclude folders
        $excludeFolders = (array) $this->hyper['params']->get('plugin_exclude_folders', []);
        if (count($excludeFolders)) {
            $query->whereNotIn($db->quoteName('position.product_folder_id'), $excludeFolders);
        }

        // Position ids
        $positionIds = $data->get('ids');
        $this->setIdsQuery($query, $positionIds, 'position.id');

        // Load unavailable
        $loadUnavailable = $data->get('loadUnavailable');
        if (!$loadUnavailable) {
            $query
                ->leftJoin(
                    $db->quoteName(HP_TABLE_MOYSKLAD_PRODUCTS, 'product'),
                    $db->quoteName('position.id') . ' = ' . $db->quoteName('product.id')
                )
                ->where('(' .
                    $db->quoteName('product.on_sale') . ' = 1 OR ' .
                    $db->quoteName('index.in_stock') . ' IS NOT NULL)'
                );
        }

        // Price range
        $priceRange = $data->get('priceRange');
        if (!empty($priceRange)) {
            $this->setPriceRangeQuery($query, $priceRange, 'index.price_a');
        }

        // Platform
        $platforms = $data->get('platform');
        if (\is_array($platforms) && \count($platforms)) {
            $platformConditions = \array_map(
                fn($platform) => $db->quoteName('folder.params') . ' LIKE ' . $db->quote("%\"product_type\": \"{$platform}\"%"),
                $platforms
            );

            $query
                ->leftJoin(
                    $db->quoteName(HP_TABLE_PRODUCT_FOLDERS, 'folder'),
                    $db->quoteName('position.product_folder_id') . ' = ' . $db->quoteName('folder.id')
                )
                ->where('(' . \implode(' OR ', $platformConditions) . ')');
        }

        // Config values
        $config = $data->get('config');
        if (\count($config)) {
            $configQuery = $this->getConfigQuery($config);
            $query
                ->innerJoin(
                    "({$configQuery}) AS config",
                    $db->quoteName('position.id') . ' = ' . $db->quoteName('config.product_id') . ' AND ' .
                    '(' .
                        $db->quoteName('index.in_stock') . ' = ' . $db->quoteName('config.stock_id') . ' OR ' .
                        $db->quoteName('index.in_stock') . ' IS NULL AND ' . $db->quoteName('config.stock_id') . ' IS NULL' .
                    ')'
                );
        }

        $game = $data->get('game');
        if (empty($game)) {
            $query->setLimit($limit, $offset); // set limit to the query if filtering by fps is not needed
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();

        $itemKeys = \array_map(
            fn($row) => 'position-' . $row->id . ($row->stock_id ? "-{$row->stock_id}" : ''),
            $results
        );

        $results = \array_combine($itemKeys, $results);

        $regularProductIds = \array_filter(\array_map(
            fn($result) => $result->stock_id ? null : $result->id,
            $results
        ));

        $regularProducts = count($regularProductIds) ? $this->hyper['helper']['moyskladProduct']->findById($regularProductIds) : [];

        $stockConfigIds = \array_filter(\array_map(
            fn($result) => (int) $result->stock_id,
            $results
        ));

        $stockProducts = count($stockConfigIds) ? $this->hyper['helper']['moyskladStock']->getProducts([
            $db->quoteName('storeitems.option_id') . ' IN (' . \implode(', ', $stockConfigIds) . ')'
        ]) : [];

        $allProducts = \array_merge($regularProducts, $stockProducts);
        foreach ($allProducts as $product) {
            $results[$product->getItemKey()] = $product;
        }

        if (!empty($game)) {
            list($gameAlias, $gameResolution) = explode('@', $game);

            /** @var \HYPERPC\Helper\FpsHelper $fpsHelper */
            $fpsHelper = $this->hyper['helper']['fps'];
            foreach ($results as $itemKey => $product) {
                $fps = $fpsHelper->getFps($product, $gameAlias);
                if (!isset($fps[$gameAlias]) || $fps[$gameAlias]['ultra'][$gameResolution] < 60) {
                    unset($results[$itemKey]);
                }
            }

            if ($limit || $offset) {
                $results = array_slice($results, $offset, $limit);
            }
        }

        return $results;
    }

    /**
     * Find parts by conditions.
     *
     * @param   Registry $data
     * @param   int $limit
     * @param   int $offset
     *
     * @return  MoyskladPart[]
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     */
    private function findParts(Registry $data, int $limit, int $offset): array
    {
        $db = $this->getDatabase();

        $query = $this->getAllPartsQuery();

        // Set IDs
        $positionIds = (array) $data->get('ids', []);
        $this->setIdsQuery($query, $positionIds, 'positions.id');

        $productFolderIds = (array) $data->get('productFolderIds', []);
        if (!empty($productFolderIds)) {
            $this->setProductFolderIdsQuery($query, $productFolderIds, 'positions.product_folder_id');
        }

        // Set price range
        $priceRange = $data->get('priceRange');
        if (!empty($priceRange)) {
            $priceRangeQuery = $db->getQuery(true);
            $this->setPriceRangeQuery($priceRangeQuery, $priceRange);
            $bounded = $priceRangeQuery->getBounded();
            if (isset($bounded[':priceFrom']) && isset($bounded[':priceTo'])) {
                $query
                    ->where(
                        "COALESCE({$db->quoteName('options.list_price')}, {$db->quoteName('positions.list_price')}) >= :priceFrom"
                    )
                    ->where(
                        "COALESCE({$db->quoteName('options.list_price')}, {$db->quoteName('positions.list_price')}) <= :priceTo"
                    )
                    ->bind(':priceFrom', $bounded[':priceFrom']->value, $bounded[':priceFrom']->dataType)
                    ->bind(':priceTo', $bounded[':priceTo']->value, $bounded[':priceFrom']->dataType);
            }
        }

        if ($data->get('field') && $data->get('fieldValue')) {
            $field = $data->get('field');
            $regexp = \implode('|', $data->get('fieldValue'));
            $optionRegexp = "\"{$field}\": \"({$regexp})\"";
            $fieldQuery = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName(JOOMLA_TABLE_FIELDS_VALUES, 'fieldvalues'))
                ->leftJoin(
                    $db->quoteName(JOOMLA_TABLE_FIELDS, 'fields'),
                    $db->quoteName('fields.id') . ' = ' . $db->quoteName('fieldvalues.field_id')
                )
                ->where($db->quoteName('fields.context') . ' = ' . $db->quote(HP_OPTION . '.positions'))
                ->where($db->quoteName('fieldvalues.item_id') . ' = ' . $db->quoteName('positions.id'))
                ->where($db->quoteName('fields.name') . ' = ' . $db->quote($field))
                ->where($db->quoteName('fieldvalues.value') . ' REGEXP ' . $db->quote($regexp));

            $fieldMatch = "
                (CASE
                    WHEN
                        {$db->quoteName('options.id')} IS NOT NULL AND
                        {$db->quoteName('options.params')} REGEXP {$db->quote($optionRegexp)}
                        THEN 1
                    WHEN
                        EXISTS ({$fieldQuery})
                        THEN 1
                    ELSE 0
                END)";

            $query->where("{$fieldMatch} = 1");
        }

        // Set limit and offset
        $query->setLimit($limit, $offset);

        // Set order
        $query->order('in_stock DESC'); // Items in stock come first
        $order = $data->get('order', 'a.price ASC');
        \preg_match('/a\.(.+)\s(ASC|DESC)/', $order, $matches);
        [, $orderColumn, $direction] = $matches;
        switch ($orderColumn) {
            case 'price':
            case 'list_price':
                $query->order("list_price {$direction}");
                break;
            case 'name':
                $fullNameField =
                    "CASE 
                        WHEN {$db->quoteName('options.name')} IS NOT NULL
                            THEN CONCAT({$db->quoteName('positions.name')}, ' ', {$db->quoteName('options.name')})
                        ELSE {$db->quoteName('positions.name')}
                    END";

                $query
                    ->select(
                        "{$fullNameField} AS full_name"
                    )
                    ->order("full_name {$direction}");
        }

        $db->setQuery($query);
        try {
            $result = $db->loadObjectList();
        } catch (\Throwable $th) {
            // log it
            return [];
        }

        $itemKeys = \array_map(
            fn($obj) => 'position-' . $obj->id . ($obj->option_id ? '-' . $obj->option_id : ''),
            $result
        );

        $parts = $this->hyper['helper']['moyskladPart']->getByItemKeys($itemKeys);

        return $parts;
    }

    /**
     * Find services by conditions.
     *
     * @param   Registry $data
     * @param   int $limit
     * @param   int $offset
     *
     * @return  Position[]
     *
     * @throws  \RuntimeException
     */
    private function findServices(Registry $data, int $limit, int $offset): array
    {
        $db = $this->getDatabase();

        $order = preg_replace(
            '/a.price/',
            'a.list_price',
            $data->get('order')
        );

        $query = $db->getQuery(true);
        $query
            ->select($db->quoteName('a.id', 'id'))
            ->from($db->quoteName(HP_TABLE_POSITIONS, 'a'))
            ->where($db->quoteName('a.type_id') . ' = 1')
            ->whereIn($db->quoteName('a.state'), [HP_STATUS_PUBLISHED])
            ->setLimit($limit, $offset)
            ->order($order);

        $productFolderIds = (array) $data->get('productFolderIds', []);
        if (!empty($productFolderIds)) {
            $this->setProductFolderIdsQuery($query, $productFolderIds);
        }

        $priceRange = $data->get('priceRange');
        if (!empty($priceRange)) {
            $this->setPriceRangeQuery($query, $priceRange);
        }

        $positionIds = (array) $data->get('ids', []);
        $this->setIdsQuery($query, $positionIds);

        $db->setQuery($query);

        return $this->hyper['helper']['moyskladService']->findById($db->loadColumn(), [
            'order' => $order
        ]);
    }

    /**
     * Get database query for searching by config values.
     *
     * @param   array $values
     *
     * @return  DatabaseQuery
     *
     * @throws  \RuntimeException
     */
    private function getConfigQuery(array $values): DatabaseQuery
    {
        $configData = [];
        foreach ($values as $value) {
            $configFindType = \ctype_digit($value) ? 'part_id' : 'value';

            $configData[$configFindType] ??= [];
            $configData[$configFindType][] = $value;
        }

        $db = $this->getDatabase();
        $configQuery = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('config_values.product_id') . ', ' . $db->quoteName('config_values.stock_id'))
            ->from($db->quoteName(HP_TABLE_PRODUCTS_CONFIG_VALUES, 'config_values'))
            ->where($db->quoteName('config_values.context') . ' = ' . $db->quote(HP_OPTION . '.position'));

        $configConditions = [];
        foreach ($configData as $type => $values) {
            $values = \array_map(
                fn($value) => $db->quote(trim($value)),
                $values
            );
        
            if ($type === 'part_id') {
                $configConditions[] = $db->quoteName('config_values.part_id') . ' IN (' . \implode(', ', $values) . ')';
            } elseif ($type === 'value') {
                $likeConditions = \array_map(fn($value) => $db->quoteName('config_values.value') . ' LIKE ' . $value, $values);
                $configConditions[] = '(' . \implode(' OR ', $likeConditions) . ')';
            }
        }

        if (!empty($configConditions)) {
            $configQuery->where(\implode(' OR ', $configConditions));
        }

        return $configQuery;
    }

    /**
     * Set ids condition to the database query.
     *
     * @param   DatabaseQuery $query
     * @param   array $ids
     * @param   ?string $column
     */
    private function setIdsQuery(DatabaseQuery &$query, array $ids, string $column = 'a.id'): void
    {
        if (count($ids)) {
            $db = $this->getDatabase();
            $query->whereIn($db->quoteName($column), $ids);
        }
    }

    /**
     * Set price range condition to the database query.
     *
     * @param   DatabaseQuery $query
     * @param   string $rangeString
     * @param   ?string $column
     */
    private function setPriceRangeQuery(DatabaseQuery &$query, string $rangeString, string $column = 'a.list_price'): void
    {
        if (preg_match('/-/', $rangeString)) {
            $db = $this->getDatabase();

            list ($from, $to) = \explode('-', $rangeString);
            $query
                ->where($db->quoteName($column) . ' >= :priceFrom')
                ->where($db->quoteName($column) . ' <= :priceTo')
                ->bind(':priceFrom', $from, ParameterType::INTEGER)
                ->bind(':priceTo', $to, ParameterType::INTEGER);
        }
    }

    /**
     * Set product folder ids condition to the database query.
     *
     * @param   DatabaseQuery $query
     * @param   array $ids
     * @param   ?string $column
     */
    private function setProductFolderIdsQuery(DatabaseQuery &$query, array $ids, string $column = 'a.product_folder_id'): void
    {
        if (count($ids)) {
            $db = $this->getDatabase();
            $query->whereIn($db->quoteName($column), $ids);
        }
    }
}
