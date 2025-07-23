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

use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use MoySklad\Entity\MetaEntity;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Helper\Context\EntityContext;
use MoySklad\Entity\Product\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Helper\Traits\MoyskladEntityActions;
use HYPERPC\Helper\Traits\TranslatableProperties;
use HyperPcModelProduct_Folder as ModelProductFolder;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\ProductFolder as hpProductFolder;

/**
 * Class ProductFolderHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ProductFolderHelper extends EntityContext
{

    public const ITEMS_TYPE_PC = 'pc';
    public const ITEMS_TYPE_PC_ID = 2;
    public const ITEMS_TYPE_CONCEPT = 'concept';
    public const ITEMS_TYPE_CONCEPT_ID = 5;
    public const ITEMS_TYPE_NOTEBOOK = 'notebook';
    public const ITEMS_TYPE_NOTEBOOK_ID = 3;
    public const ITEMS_TYPE_WORKSTATION = 'workstation';
    public const ITEMS_TYPE_WORKSTATION_ID = 4;

    use MoyskladEntityActions;
    use TranslatableProperties;

    /**
     * Hold data from getByIdsWithFields method.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_idsWithFieldsData = [];

    /**
     * Hold min prices by categories.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_minPrices = [];

    /**
     * Holds model.
     *
     * @var     ModelProductFolder
     *
     * @since   2.0
     */
    protected static $_model;

    /**
     * Holds the folder uuid for order's products.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected static $_ordersProductFolderUuid;

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
        $table = Table::getInstance('Product_Folders');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Get translations table name.
     *
     * @return  string
     */
    public function getTranslationsTableName(): string
    {
        return 'Product_Folders_Translations';
    }

    /**
     * Get array of translatable fields.
     *
     * @return  array
     */
    public function getTranslatableFields(): array
    {
        return ['description', 'metadata', 'translatable_params'];
    }

    /**
     * Get folders by ids with com_field field.
     *
     * @param   int[]  $ids         List of folders ids.
     * @param   bool   $loadFields  Flag of load fields.
     *
     * @return  ProductFolder[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getConfiguratorFields(array $ids = [], $loadFields = true)
    {
        $db = $this->hyper['db'];

        if (!count($ids)) {
            return [];
        }

        $data = new Registry(['ids' => $ids, 'loadFields' => $loadFields]);
        $hash = md5($data->toString());

        if (!array_key_exists($hash, self::$_idsWithFieldsData)) {
            $folders = $this->findById($ids, [
                'select'     => ['a.*'],
                'order'      => 'a.lft ASC',
                'conditions' => [$db->qn('a.published') . ' = ' . HP_STATUS_PUBLISHED]
            ]);

            if ($loadFields) {
                $query = $db
                    ->getQuery(true)
                    ->select(['c.category_id', 'f.*'])
                    ->from($db->qn(JOOMLA_TABLE_FIELDS_CATEGORIES, 'c'))
                    ->join('LEFT', $db->qn(JOOMLA_TABLE_FIELDS, 'f') . ' ON c.field_id = f.id')
                    ->where([
                        $db->qn('f.state') . ' = ' . HP_STATUS_PUBLISHED,
                        $db->qn('c.category_id') . ' IN (' . implode(', ', $ids) . ')',
                        $db->qn('f.context')  . ' = ' . $db->q(HP_OPTION . '.position')
                    ])
                    ->order($db->qn('f.ordering') . ' ASC');

                $fields = $db->setQuery($query)->loadObjectList('id');

                if (count($fields)) {
                    /** @var ProductFolder $folder */
                    foreach ($folders as $folder) {
                        $fieldId = $folder->params->get('configurator_filters', 0, 'int');
                        if (isset($fields[$fieldId])) {
                            $folder->set('field', new Field($fields[$fieldId]));
                        }
                    }
                }
            }

            self::$_idsWithFieldsData[$hash] = $folders;
        }

        return self::$_idsWithFieldsData[$hash];
    }

    /**
     * Get category product minimal price by category id
     *
     * @param   int $categoryId
     * @param   bool $onSaleOnly
     *
     * @return  Money|null
     *
     * @since 2.0
     */
    public function getMinCategoryPrice(int $categoryId, $onSaleOnly = true)
    {
        $cacheKey = $categoryId . ':' . ($onSaleOnly ? '1' : '0');
        if (isset(self::$_minPrices[$cacheKey])) {
            return self::$_minPrices[$cacheKey];
        }

        $db = $this->hyper['db'];

        $conditions = [
            $db->qn('positions.type_id') . ' = ' . $db->q(3),
            $db->qn('positions.product_folder_id') . ' = ' . $db->q($categoryId),
            $db->qn('positions.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED)
        ];

        if ($onSaleOnly) {
            $conditions[] = $db->qn('products.on_sale') . ' = ' . $db->q(HP_STATUS_PUBLISHED);
        }

        $query = $db
            ->getQuery(true)
            ->select(['positions.list_price'])
            ->from($db->qn(HP_TABLE_POSITIONS, 'positions'))
            ->join('LEFT', $db->qn(HP_TABLE_MOYSKLAD_PRODUCTS, 'products') . ' ON products.id = positions.id')
            ->where($conditions)
            ->order('positions.list_price ASC')
            ->setLimit(1);

        $db->setQuery($query);
        $price = $db->loadResult();

        $stockProducts = $this->hyper['helper']['moyskladStock']->getProducts([], [$categoryId]);
        foreach ($stockProducts as $stockProduct) {
            $price = empty($price) ? $stockProduct->getListPrice()->val() : min($stockProduct->getListPrice()->val(), (int) $price);
        }

        if ($price === null) {
            if ($onSaleOnly) {
                return $this->getMinCategoryPrice($categoryId, false);
            }

            return null;
        }

        self::$_minPrices[$cacheKey] = $this->hyper['helper']['money']->get($price);

        return self::$_minPrices[$cacheKey];
    }

    /**
     * Get model.
     *
     * @return  ModelProductFolder
     *
     * @since   2.0
     */
    public function getModel()
    {
        if (!isset(self::$_model)) {
            self::$_model = ModelAdmin::getInstance('Product_Folder');
        }

        return self::$_model;
    }

    /**
     * Get all groups.
     *
     * @param   bool  $published
     *
     * @return  array
     *
     * @throws  \RuntimeException|\Exception
     *
     * @since   2.0
     */
    public function getList($published = true)
    {
        $db         = $this->hyper['db'];
        $conditions = ['NOT ' . $db->quoteName('a.alias') . ' = ' . $db->quote('root')];

        if ($published === true) {
            $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
            $conditions[] = $db->quoteName('a.published') . ' in (' . implode(', ', $publishStatuses) . ')';
        }

        return $this->findAll([
            'conditions' => $conditions,
            'order'      => $db->quoteName('a.lft') . ' ASC'
        ]);
    }

    /**
     * Get the folder uuid for order's products
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOrderProductsFolderUuid()
    {
        if (!isset(self::$_ordersProductFolderUuid)) {
            $folderId = $this->hyper['params']->get('moysklad_order_products_folder', 0);
            $folder = $this->findById($folderId);

            self::$_ordersProductFolderUuid = $folder->uuid;
        }

        return self::$_ordersProductFolderUuid;
    }

    /**
     * Prepare data array for folders
     *
     * @param   ProductFolder $entity
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function prepareData(MetaEntity $entity): array
    {
        if (!($entity instanceof ProductFolder)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to ' . __METHOD__ . ' must be an instance of ' . ProductFolder::class . ', ' . get_class($entity) . ' given'
            );
        }

        $folderLevel    = 1;
        $parentFolderId = 1;
        $parentFolder = $entity->productFolder;
        if ($parentFolder instanceof ProductFolder) {
            $parentFolderUuid = $this->hyper['helper']['moysklad']->getEntityUuidFromHref($parentFolder->getMeta()->href);

            $hpParentFolder = $this->findBy('uuid', $parentFolderUuid);
            if ($hpParentFolder instanceof hpProductFolder && $hpParentFolder->id) {
                $parentFolderId = $hpParentFolder->id;
                $folderLevel    = $hpParentFolder->level + 1;
            }
        }

        return [
            'uuid'          => $entity->id,
            'path'          => $entity->pathName,
            'title'         => $entity->name,
            'level'         => $folderLevel,
            'parent_id'     => $parentFolderId
        ];
    }

    /**
     * Render products by tag snippet.
     *
     * @param   object $article
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function renderBySnippet(&$article)
    {
        $regex = '/{(folderstartprice|folderstartcredit)(.*?)}/i';
        preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $match) {
                $output           = '';
                $categoryId       = (int) $match[2];
                $minCategoryPrice = $this->getMinCategoryPrice($categoryId);
                if ($minCategoryPrice && (int) $minCategoryPrice->val() !== 0) {
                    switch ($match[1]) {
                        case 'folderstartcredit':
                            $minCreditPrice = $this->hyper['helper']['credit']->getMonthlyPayment($minCategoryPrice->val());
                            $output = $minCreditPrice->text();
                            break;
                        case 'folderstartprice':
                            $output = $minCategoryPrice->text();
                            break;
                    }
                }

                $article->text = preg_replace(
                    "|$match[0]|",
                    addcslashes($output, '\\$'),
                    $article->text,
                    1
                );
            }
        }
    }

    /**
     * Get items type id
     *
     * @param   string $type
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getItemsTypeId($type)
    {
        $typeId = 0;
        switch ($type) {
            case self::ITEMS_TYPE_PC:
                $typeId = self::ITEMS_TYPE_PC_ID;
                break;
            case self::ITEMS_TYPE_CONCEPT:
                $typeId = self::ITEMS_TYPE_CONCEPT_ID;
                break;
            case self::ITEMS_TYPE_NOTEBOOK:
                $typeId = self::ITEMS_TYPE_NOTEBOOK_ID;
                break;
            case self::ITEMS_TYPE_WORKSTATION:
                $typeId = self::ITEMS_TYPE_WORKSTATION_ID;
                break;
        }

        return $typeId;
    }

    /**
     * Get parts with options.
     *
     * @param   array  $items
     *
     * @return  array
     *
     * @throws  \RuntimeException|\Exception
     *
     * @since   2.0
     *
     * @todo    rename to divideByOptions
     */
    public function getParts($items)
    {
        $parts = [];

        /** @var MoyskladPart $item */
        foreach ($items as $item) {
            if ($item->hasOptions()) {
                $options  = $item->getOptions();
                $partName = $item->name;

                /** @var MoyskladVariant $option */
                foreach ($options as $option) {
                    if ($option->isTrashed() || $option->isUnpublished()) {
                        continue;
                    }

                    $item->set('option', $option);
                    $item->set('name', $partName . ' ' . $option->name);
                    $item->set('balance', $option->getFreeStocks());

                    $item->setListPrice($option->getListPrice());
                    $item->setSalePrice($option->getSalePrice());

                    $parts[] = clone $item;
                }

                continue;
            }

            $parts[] = clone $item;
        }

        return $parts;
    }

    /**
     * Sort parts by availability.
     *
     * @param   array           $_parts
     * @param   hpProductFolder $productFolder
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function sortParts(array $_parts, hpProductFolder $productFolder)
    {
        $inStockParts      = [];
        $preorderParts     = [];
        $outOfStockParts   = [];
        $discontinuedParts = [];

        $_parts = $this->getParts($_parts);

        /** @var MoyskladPart $part */
        foreach ($_parts as $part) {
            if (isset($productFolder->stores)) {
                $storeIds = array_keys($productFolder->stores);
                $optionId = isset($part->option->id) ? (int) $part->option->id : 0;
                $stocks = $this->hyper['helper']['moyskladStock']->getItems([
                    'storeIds'  => $storeIds,
                    'optionIds' => [$optionId],
                    'partIds'   => [$part->id]
                ]);

                $balance = 0;
                if (!empty($stocks)) {
                    foreach ($stocks as $stock) {
                        $balance = $balance + $stock->balance;
                    }
                }

                $part->balance = $balance;
                if ($optionId) {
                    $part->option->balance = $balance;
                }
            }

            $availability = $part->getAvailability();
            if ($part->option instanceof MoyskladVariant) {
                $availability = $part->option->getAvailability();
            }

            if ($availability === Stockable::AVAILABILITY_PREORDER) {
                $preorderParts[$part->getItemKey()] = $part;
                continue;
            } elseif ($availability === Stockable::AVAILABILITY_OUTOFSTOCK) {
                $outOfStockParts[$part->getItemKey()] = $part;
                continue;
            } elseif ($availability === Stockable::AVAILABILITY_DISCONTINUED) {
                $discontinuedParts[$part->getItemKey()] = $part;
                continue;
            }

            $inStockParts[$part->getItemKey()] = $part;
        }

        $parts[] = $inStockParts + $preorderParts;
        $parts[] = $discontinuedParts;

        $sorting = $this->hyper['input']->get('sorting', 'availability_asc', 'string');
        $sorting = explode('_', $sorting);
        if (count($sorting) === 2) {
            list($sort, $direction) = $sorting;

            if ($sort !== 'price') {
                $parts[0] = $parts[0] + $outOfStockParts;
            }

            if ($sort === 'availability' && $direction === 'desc') {
                $parts[0] = array_reverse($parts[0]);
            } elseif ($sort === 'price' || $sort === 'name') {
                usort($parts[0], function ($a, $b) use ($sort, $direction) {

                    $val1 = $val2 = null;

                    if ($sort === 'price') {
                        $val1 = $a->price->val();
                        $val2 = $b->price->val();
                    }

                    if ($sort === 'name') {
                        $val1 = $a->name;
                        $val2 = $b->name;
                    }

                    if ($val1 === $val2) {
                        return 1;
                    } elseif ($direction === 'desc') {
                        return $val1 < $val2 ? 1 : -1;
                    }

                    return $val1 > $val2 ? 1 : -1;
                });

                if ($sort === 'price') {
                    $parts[0] = $parts[0] + $outOfStockParts;
                }
            }
        } else {
            $parts[0] = $parts[0] + $outOfStockParts;
        }

        return $parts;
    }

    public function sortServices(array $_services)
    {
        $sorting = $this->hyper['input']->get('sorting', 'price_asc', 'string');
        $sorting = explode('_', $sorting);

        foreach ($_services as $service) {
            $services[$service->getItemKey()] = $service;
        }

        if (count($sorting) !== 2) {
            return $services;
        }

        list($sort, $direction) = $sorting;

        uasort($services, function ($a, $b) use ($sort, $direction) {

            $val1 = $val2 = null;

            if ($sort === 'price') {
                $val1 = $a->getListPrice()->val();
                $val2 = $b->getListPrice()->val();
            }

            if ($sort === 'name') {
                $val1 = $a->name;
                $val2 = $b->name;
            }

            if ($val1 === $val2) {
                return 1;
            } elseif ($direction === 'desc') {
                return $val1 < $val2 ? 1 : -1;
            }

            return $val1 > $val2 ? 1 : -1;
        });

        return $services;
    }

    /**
     * Filter group parts
     *
     * @param   array $_parts
     * @param   array $fields
     * @param   null $param
     * @param   null $value
     * @param   bool $checkStores
     *
     * @return  array
     *
     * @since   2.0
     */
    public function filterParts($_parts, $fields, $param = null, $value = null, $checkStores = false)
    {
        $parts = [];

        if (isset($fields['store']) && $checkStores) {
            foreach ($_parts as $key => $item) {
                $stockFilter = [
                    'storeIds'  => $fields['store'],
                    'itemIds'   => [$item->id]
                ];

                if (isset($item->option->id)) {
                    $stockFilter['optionIds'][] = (int) $item->option->id;
                }

                $stocks = $this->hyper['helper']['MoyskladStock']->getItems($stockFilter);

                if (empty($stocks)) {
                    unset($_parts[$key]);
                }
            }
        }

        unset($fields['store']);

        foreach ($_parts as $item) {
            $sortedFields = [];

            if (!$item instanceof MoyskladPart || !isset($item->fields)) {
                continue;
            }

            foreach ($item->fields as $field) {
                if (isset($sortedFields[$field->id])) {
                    continue;
                }

                if (isset($item->option) &&
                    $item->option instanceof MoyskladVariant &&
                    !empty($item->option->id) &&
                    $item->option->params->find('options.' . $field->name)
                ) {
                    $field->value = $item->option->params->find('options.' . $field->name);
                }

                if ($param == $field->name && $value != $field->value) {
                    continue;
                }

                if (!isset($fields[$field->name]) || !$fields[$field->name] || !in_array($field->value, $fields[$field->name])) {
                    continue;
                }

                $sortedFields[$field->id] = $field;
            }

            $itemKey = $item->getItemKey();

            if (count($sortedFields) == count($fields) && !array_key_exists($itemKey, $parts)) {
                $parts[$itemKey] = $item;
            }
        }

        return $parts;
    }

    /**
     * Get filter init state json
     *
     * @param   array $parts
     * @param   array $allowedFilters
     * @param   array $fields
     * @param   bool $names
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function getFilterJson(array $parts, $allowedFilters = [], $fields = [], $names = false)
    {
        if (!$parts) {
            return false;
        }

        $filters  = [];
        $_filters = [];

        foreach ($parts as $part) {
            $optionFields = [];
            if (isset($part->option) && $part->option instanceof MoyskladVariant && !empty($part->option->id)) {
                $optionFields = (array) $part->option->params->get('options');
            }

            if (!isset($part->fields)) {
                continue;
            }

            $partFields = $part->fields;
            foreach ($partFields as $field) {
                if (isset($optionFields[$field->name]) && !empty($optionFields[$field->name])) {
                    $field->value = $optionFields[$field->name];
                }

                if (!in_array((string) $field->id, $allowedFilters)) {
                    continue;
                }

                if (isset($_filters[$field->name]) && in_array($field->value, $_filters[$field->name])) {
                    continue;
                }

                $options = $field->fieldparams->get('options');
                if (!$options) {
                    continue;
                }

                $name = '';

                foreach ($options as $option) {
                    if ($option['value'] == $field->value) {
                        $name = $option['name'];
                    }
                }

                $_filters[$field->name][$field->value] = [
                    'value' => $field->value,
                    'name'  => $name,
                ];
            }
        }

        foreach ($_filters as $param => $filter) {
            foreach ($filter as $item) {
                $value = $item['value'];
                $name  = $item['name'];

                $data['value'] = $value;

                if ($names) {
                    $data['name']  = $name;
                }

                if (isset($fields[$param]) && in_array($value, $fields[$param])) {
                    $_parts = $this->filterParts($parts, $fields, $param, $value, true);
                } else {
                    $_fields = $fields;
                    $_fields[$param][] = $value;
                    $_parts = $this->filterParts($parts, $_fields, $param, $value, true);
                }

                $data['count'] = count($_parts);

                $filters[$param][$value] = $data;
            }
        }

        return [
            'available' => $filters,
            'current'   => $fields
        ];
    }
}
