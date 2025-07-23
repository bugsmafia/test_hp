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

namespace HYPERPC\Helper;

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\View\Html\Data\Product\Filter as DataFilter;

defined('_JEXEC') or die('Restricted access');

/**
 * Class FilterHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class FilterHelper extends AppHelper
{

    const FIELD_TYPE_FIELD_CATEGORY = 'field_cat';
    const FIELD_TYPE_PART_GROUP     = 'part_group';

    const RECOUNT_INDEX_FROM_CATALOG  = 'catalog';
    const RECOUNT_INDEX_FROM_IN_STOCK = 'in_stock';

    /**
     * Hold filter query.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_queryDump;

    /**
     * Create product index table.
     *
     * @param   array   $fields
     * @param   array   $index
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function createTable(array $fields, $index = [])
    {
        if (empty($fields)) {
            return $this;
        }

        $params = array_merge($fields, ['PRIMARY KEY (`id`)' . "\n"], $index);

        $sql   = [];
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . $this->tableName . '`';
        $sql[] = '(' . implode(",\n ", $params) . ')';
        $sql[] = 'COLLATE=\'utf8_general_ci\' ENGINE=MyISAM;';

        $db = $this->hyper['db'];
        $sqlString = implode(' ', $sql);

        $db->setQuery($sqlString)->execute();
        return $this;
    }

    /**
     * Drop product index table.
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function dropTable()
    {
        $db = $this->hyper['db'];
        $db->setQuery('DROP TABLE IF EXISTS `' . $this->tableName . '`')->execute();
        return $this;
    }

    /**
     * Check is enabled filter category.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function enableCategoryFilter()
    {
        return (bool) $this->hyper['params']->get('filter_enabled_category', HP_STATUS_PUBLISHED);
    }

    /**
     * Get allowed filter fields.
     *
     * @return  array|bool
     *
     * @since   2.0
     */
    public function getAllowedFields()
    {
        static $allowedValues;

        if ($allowedValues === null) {
            /** @var FieldsHelper $fieldHelper */
            $fieldHelper = $this->hyper['helper']['fields'];
            $allowedValues = (array) $fieldHelper->getFieldsById($this->getProductAllowedFilterIds(), true);
        }

        return $allowedValues;
    }

    /**
     * Get product allowed filter aliases.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProductAllowedAliasFilters()
    {
        $aliasList = [];

        /** @var Field $field */
        foreach ($this->getProductAllowedFilters() as $field) {
            if (!in_array($field, $aliasList)) {
                $aliasList[] = $field->name;
            }
        }

        return $aliasList;
    }

    /**
     * Get product allowed filter ids.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProductAllowedFilterIds()
    {
        $ids = [];
        foreach ((array) $this->hyper['params']->get(static::PRODUCT_FILTER_FIELD) as $item) {
            $item  = new JSON($item);
            $ids[] = (int) $item->get('id');
        }

        return $ids;
    }

    /**
     * Get product allowed filter.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProductAllowedFilters()
    {
        static $fields = [];
        if (count($fields) === 0) {
            $fields = $this->hyper['helper']['fields']->getFieldsById($this->getProductAllowedFilterIds());
        }

        return $fields;
    }

    /**
     * Get allowed product url filter query custom field.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProductUrlQueryAllowedAliasList()
    {
        $aliasList = $this->getProductAllowedAliasFilters();

        array_unshift(
            $aliasList,
            DataFilter::STORE_FIELD_NAME,
            DataFilter::PRICE_FIELD_NAME,
            DataFilter::CATEGORY_FIELD_NAME
        );

        return $aliasList;
    }

    /**
     * Get filter query.
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
     * Get product index table properties.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getTableProps()
    {
        $tblFields  = [
            '`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            '`product_id` INT(10) NOT NULL',
            '`price_a` FLOAT(12,2) NULL DEFAULT 0.00',
            '`price_b` FLOAT(12,2) NULL DEFAULT 0.0',
            '`in_stock` VARCHAR(70) NULL DEFAULT NULL',
        ];

        $fieldIds = $this->_getFilterFieldIds();
        $fields   = $this->hyper['helper']['fields']->getFieldsById($fieldIds, true);

        $tblIndexes = [
            'INDEX `product_id` (`product_id`)',
            'INDEX `price_a` (`price_a`)',
            'INDEX `price_b` (`price_b`)',
            'INDEX `in_stock` (`in_stock`)'
        ];

        /** @var Field $field */
        foreach ($fields as $field) {
            $tblFields[] = '`' . $field->name . '` VARCHAR(250) NULL DEFAULT NULL';
        }

        foreach ($fields as $field) {
            $tblIndexes[] = 'INDEX `' . $field->name . '` (`' . $field->name . '`)';
        }

        return array_merge($tblFields, $tblIndexes);
    }

    /**
     * Check has filter render.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasFilterRender()
    {
        return (bool) count((array) $this->hyper['params']->get(static::PRODUCT_FILTER_FIELD));
    }

    /**
     * Check is debug mode.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDebugMode()
    {
        return ((bool) $this->hyper['params']->get('filter_debug') === true && $this->hyper['user']->isManager());
    }

    /**
     * Render filter layout.
     *
     * @param   array  $renderElements
     * @param   int    $filterCount
     *
     * @return  string
     *
     * @since   2.0
     */
    public function render(array $renderElements, $filterCount = 0)
    {
        return $this->hyper['helper']['render']->render('filter/default', [
            'filterCount' => $filterCount,
            'elements'    => $renderElements
        ]);
    }

    /**
     * Render Joomla! custom field for filter.
     *
     * @param   string $type
     * @param   mixed  $value
     * @param   mixed  $count
     * @param   string $title
     * @param   array  $attrs
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function renderField($type, $value, $count, $title, array $attrs = [])
    {
        $defaultAttrs = [
            'value' => $value,
            'count' => $count,
            'title' => $title
        ];

        $attrs = array_replace_recursive($attrs, $defaultAttrs);

        return $this->hyper['helper']['render']->render('filter/field/' . strtolower($type), $attrs);
    }

    /**
     * Render filter field list.
     *
     * @param   array  $renderElements
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderFieldList(array $renderElements)
    {
        return $this->hyper['helper']['render']->render('filter/elements/list', [
            'elements' => $renderElements
        ]);
    }

    /**
     * Render filter nav bar.
     *
     * @param   int $count
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderNavBar($count = 0)
    {
        return $this->hyper['helper']['render']->render('filter/elements/nav', [
            'filterCount' => $count
        ]);
    }

    /**
     * Set filter query.
     *
     * @param   $queryDump
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
     * Update product index.
     *
     * @param   ProductMarker  $product
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function updateProductIndex(ProductMarker $product)
    {
        if (!$product->get('id')) {
            return;
        }

        $productId = $product->get('id');

        $defaultParts   = $product->get('configuration')?->get('default') ?? [];
        $defaultOptions = $product->get('configuration')?->get('option') ?? [];

        if (empty($defaultParts) && empty($defaultOptions)) {
            return;
        }

        $isFromStock = $product->isFromStock();

        $configParts = $product->getConfigParts(
            compactByGroup:true,
            partFormConfig:$isFromStock,
            loadUnavailableParts:$isFromStock
        );

        // Set index row
        $productPrice = $product->getListPrice()->val();
        $indexData = new Registry([
            'product_id' => $productId,
            'price_a'    => $productPrice,
            'price_b'    => $productPrice
        ]);

        $this->_setInstock($product, $indexData);

        $fieldCatIds = $this->_getFilterFieldCategoryIds();
        $fieldsForIndexing = (array) $this->hyper['params']->get(static::PRODUCT_INDEX_FIELD, []);
        $fieldsMap = \array_column($fieldsForIndexing, 'group_id', 'id');

        $indexParts = \array_intersect_key($configParts, \array_flip($fieldCatIds));
        $indexParts = \array_merge(...$indexParts);
        foreach ($indexParts as $part) {
            $groupId = $part->getFolderId();
            $partFieldIds = \array_keys(
                \array_filter(
                    $fieldsMap,
                    fn($fieldCategoryId) => $groupId === (int) $fieldCategoryId
                )
            );

            $partFields = $this->_getPartFieldList($part, $partFieldIds);

            foreach ($partFields as $fieldKey => $field) {
                if (empty($field->value) || $field->value === 'none') {
                    continue;
                }

                $indexData->set($fieldKey, $field->value);
            }
        }

        $indexTable = $this->_getIndexTable();
        $indexTable->deleteByProductId($productId, $indexData->get('in_stock'));
        $indexTable->write([$indexData->toArray()]);

        // Set config values
        $configValues = [];

        $commonRowData = [
            'context' => $this->_getContext(),
            'product_id' => $productId
        ];

        $stockId = $indexData->get('in_stock');
        if (!empty($stockId)) {
            $commonRowData['stock_id'] = $stockId;
        }

        foreach ($configParts as $folderId => $parts) {
            foreach ($parts as $part) {
                $rowData = [
                    'part_id' => $part->id,
                    'option_id' => $part->option?->id ?? 0,
                    'value' => $part->getName()
                ];

                $configValues[] = \array_merge($commonRowData, $rowData);
            }
        }

        /** @var \HyperPcTableProducts_Config_Values $valuesTable */
        $valuesTable = Table::getInstance('Products_Config_Values');
        $valuesTable->deleteProductData($productId, $stockId);
        $valuesTable->write($configValues);
    }

    /**
     * Get product filter category ids.
     *
     * @return  array|null
     *
     * @since   2.0
     */
    protected function _getFilterFieldCategoryIds()
    {
        static $ids;
        if ($ids === null) {
            $ids = [];
            $fieldIndex = (array) $this->hyper['params']->get(static::PRODUCT_INDEX_FIELD, []);
            foreach ($fieldIndex as $data) {
                $data    = new JSON($data);
                $groupId = (int) $data->get('group_id');
                if (!in_array($groupId, $ids)) {
                    $ids[] = $groupId;
                }
            }
        }

        return $ids;
    }

    /**
     * Get product filter field ids.
     *
     * @return  array|null
     *
     * @since   2.0
     */
    protected function _getFilterFieldIds()
    {
        static $ids;
        if ($ids === null) {
            $ids = [];
            $fieldIndex = (array) $this->hyper['params']->get(static::PRODUCT_INDEX_FIELD, []);

            foreach ($fieldIndex as $data) {
                $data  = new JSON($data);
                $ids[] = (int) $data->get('id');
            }
        }

        return $ids;
    }

    /**
     * Get part field list.
     *
     * @param   PartMarker  $part
     * @param   array       $fieldIds       Is ids from filter_product_allowed component setting.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getPartFieldList(PartMarker $part, $fieldIds)
    {
        /** @var \JDatabaseDriver $db */
        $db = $this->hyper['db'];

        $query = $db
            ->getQuery(true)
            ->select([
                'a.*', 'b.value', 'GROUP_CONCAT(c.category_id SEPARATOR ",") as category_ids'
            ])
            ->join(
                'LEFT',
                $db->qn(JOOMLA_TABLE_FIELDS_VALUES, 'b') .
                ' ON (' . $db->qn('a.id') . ' = ' . $db->qn('b.field_id') . ')'
            )
            ->join(
                'LEFT',
                $db->qn(JOOMLA_TABLE_FIELDS_CATEGORIES, 'c') .
                ' ON (' . $db->qn('a.id') . ' = ' . $db->qn('c.field_id') . ')'
            )
            ->from($db->qn(JOOMLA_TABLE_FIELDS, 'a'))
            ->group([
                $db->quoteName('a.id'),
                $db->quoteName('b.value'),
            ]);

        $groupId = $part->getFolderId();
        $context = $this->_getContext();

        $conditions = [
            $db->qn('b.item_id')     . ' = ' . $db->q($part->id),
            $db->qn('c.category_id') . ' = ' . $db->q($groupId),
            $db->qn('a.context')     . ' = ' . $db->q($context),
            $db->qn('a.id')          . ' IN (' . implode(', ', $fieldIds) . ') '
        ];

        $optionReloadFields = [];
        $reloadedFieldsIds  = (array) $part->params->get('option_fields');
        foreach ($reloadedFieldsIds as $key => $reloadedFieldsId) {
            if (!in_array((int) $reloadedFieldsId, $fieldIds)) {
                unset($reloadedFieldsIds[$key]);
            }
        }

        $class = Field::class;
        if (count($reloadedFieldsIds)) {
            $_query = $db->getQuery(true)
                ->select([
                    'a.*', 'GROUP_CONCAT(c.category_id SEPARATOR ",") as category_ids'
                ])
                ->from($db->qn(JOOMLA_TABLE_FIELDS, 'a'))
                ->join(
                    'LEFT',
                    $db->qn(JOOMLA_TABLE_FIELDS_CATEGORIES, 'c') .
                    ' ON (' . $db->qn('a.id') . ' = ' . $db->qn('c.field_id') . ')'
                )
                ->where([
                    $db->qn('c.category_id')  . ' = ' . $db->q($groupId),
                    $db->qn('a.context')      . ' = ' . $db->q($context),
                    $db->qn('a.id') . ' IN (' . implode(', ', $reloadedFieldsIds) . ')'
                ])
                ->group([
                    $db->quoteName('a.id'),
                ]);

            $_optionReloadFields = $db->setQuery($_query)->loadAssocList('name');

            $optionReloadFields = [];
            foreach ($_optionReloadFields as $id => $item) {
                $optionReloadFields[$id] = new $class($item);
            }
        }

        $query->where($conditions);

        $_partFields = $db->setQuery($query)->loadAssocList('name');

        $partFields  = [];
        foreach ($_partFields as $id => $item) {
            $partFields[$id] = new $class($item);
        }

        return array_merge($optionReloadFields, $partFields);
    }
}
