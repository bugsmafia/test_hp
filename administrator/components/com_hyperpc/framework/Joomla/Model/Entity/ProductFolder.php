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

namespace HYPERPC\Joomla\Model\Entity;

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;
use HYPERPC\Helper\GoogleHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * Class ProductFolder
 *
 * @package HYPERPC\Joomla\Model\Entity
 *
 * @since   2.0
 */
class ProductFolder extends Entity implements CategoryMarker
{

    const DEFAULT_GRID_COLS = 4;
    const INDEX_TABLE       = HP_TABLE_MOYSKLAD_PRODUCTS_INDEX;

    /**
     * Folder alias.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $alias;

    /**
     * Folder datetime.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $created_time;

    /**
     * Full description.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $description;

    /**
     * Primary key.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Folder level.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $level;

    /**
     * Tree left point.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $lft;

    /**
     * Category meta data.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $metadata;

    /**
     * Modified datetime.
     *
     * @var     Date
     *
     * @since   2.0
     */
    public $modified_time;

    /**
     * Folder params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Folder params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $translatable_params;

    /**
     * Parent folder id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $parent_id;

    /**
     * Tree path.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $path;

    /**
     * Published status.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $published = 0;

    /**
     * Tree right point.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $rgt;

    /**
     * Folder title.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $title;

    /**
     * Folder uuid.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $uuid;

    /**
     * Can buy flag.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $retail;

    /**
     * Hold loaded parts.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_parts = [];

    /**
     * Get edit folder link url.
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getEditUrl()
    {
        return $this->hyper['route']->build([
            'id'     => $this->id,
            'view'   => 'product_folder',
            'layout' => 'edit'
        ]);
    }

    /**
     * Get folder children.
     *
     * @param   string  $order
     * @param   int     $published
     *
     * @return  array
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     *
     * @todo    In the Category entity this method is wrongly named getParents
     */
    public function getSubfolders($published = 1, $order = 'a.id ASC')
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->quoteName('parent_id') . ' = ' . $db->quote($this->id)
        ];

        if ($published === 1) {
            $conditions[] = $db->quoteName('a.published') . ' = ' . $db->quote(HP_STATUS_PUBLISHED);
        }

        return $this->hyper['helper']['productFolder']->findAll([
            'order'      => $order,
            'conditions' => $conditions
        ]);
    }

    /**
     * Get site view folder url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return $this->hyper['helper']['route']->url(array_replace($query, [
            'view' => 'product_folder',
            'id'   => (string) $this->id
        ]));
    }

    /**
     * Get array of group ids for teaser table
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGroupsInTeaserTable()
    {
        $groupsInTable = $this->getParams()->get('groups_in_teaser_table', []);
        if (empty($groupsInTable)) {
            $groupsInTable = $this->hyper['params']->get('product_folders_in_teaser_table', []);
        }

        return $groupsInTable;
    }

    /**
     * Get array of groups of platform components
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGroupsPlatform()
    {
        $groupsPlatform = $this->getParams()->get('folders_of_platform_components', []);
        if (empty($groupsPlatform)) {
            $groupsPlatform = $this->hyper['params']->get('folders_of_platform_components', []);
        }

        return $groupsPlatform;
    }

    /**
     * Get hyperbox type
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getHyperboxType()
    {
        return $this->getParams()->get('hyperbox_type', null);
    }

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams()
    {
        static $result = [];

        if (!key_exists($this->id, $result)) {
            $params = $this->params->getArrayCopy();

            $translatableParams = $this->translatable_params?->getArrayCopy();

            $result[$this->id] = new JSON(array_merge($params, (array) $translatableParams));
        }

        return $result[$this->id];
    }

    /**
     * Get parent id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Get parent folder
     *
     * @return  CategoryMarker
     *
     * @since   2.0
     */
    public function getParent()
    {
        return $this->hyper['helper']['productFolder']->findById($this->parent_id);
    }

    /**
     * Get folder parts list.
     *
     * @param array     $conditions
     * @param string    $order
     * @param string    $key
     * @param bool      $cached
     * @return  array
     *
     * @throws \Exception
     * @since   2.0
     */
    public function getParts($conditions = [], $order = 'a.list_price ASC', $key = 'id', $cached = false)
    {
        $db = $this->hyper['db'];

        $hashKey = [$order, $key, $cached];
        if (empty($conditions)) {
            $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
            $conditions[] = $db->quoteName('a.state') . ' IN (' . implode(', ', $publishStatuses) . ')';
        }

        $conditions[] = $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($this->id);

        $hashKey[]    = implode('|||', $conditions);

        $showInactive = (bool) $this->getParams()->get('show_inactive', 1);
        $hashKey[]    = $showInactive;

        $hashKey = md5(implode('///', $hashKey));
        if (!array_key_exists($hashKey, $this->_parts)) {
            if (!$showInactive) {
                if ((bool) $this->getParams()->get('retail', 0) === true) {
                    $conditions[] = $db->quoteName('a.retail') . ' <> 0';
                } else {
                    $conditions[] = $db->quoteName('a.retail') . ' = 1';
                }
            }

            $parts = $this->hyper['helper']['moyskladPart']->findList(['a.*'], $conditions, $order, $key, $cached);

            if (count($parts)) {
                $query = $db
                    ->getQuery(true)
                    ->select(['v.*', 'f.*', 'c.category_id'])
                    ->from($db->quoteName('#__fields_values', 'v'))
                    ->join('LEFT', $db->quoteName('#__fields', 'f') . ' ON v.field_id = f.id')
                    ->join('LEFT', $db->quoteName('#__fields_categories', 'c') . ' ON c.field_id = f.id')
                    ->where([
                        $db->quoteName('f.state')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED),
                        $db->quoteName('f.context') . ' = ' . $db->quote(HP_OPTION . '.position'),
                        $db->quoteName('v.item_id') . ' IN (' . implode(', ', array_keys($parts)) . ')',
                    ])
                    ->order($db->quoteName('f.ordering') . ' ASC');

                $fields = $db->setQuery($query)->loadAssocList();

                if (count($fields)) {
                    foreach ($fields as $field) {
                        $partId = (int) $field['item_id'];
                        if (array_key_exists($partId, $parts)) {
                            $fValues = (isset($parts[$partId]->fields)) ? (array) $parts[$partId]->fields : [];
                            $fValues[] = new Field($field);

                            $parts[$partId]->fields = $fValues;
                        }
                    }
                }
            }

            $this->_parts[$hashKey] = $parts;
        }

        return $this->_parts[$hashKey];
    }

    /**
     * Get folder services list.
     *
     * @param   array   $conditions
     * @param   string  $order
     *
     * @return  array
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getServices($conditions = [], $order = 'a.name ASC')
    {
        $db = $this->hyper['db'];

        if (empty($conditions)) {
            $publishStatuses = [HP_STATUS_PUBLISHED];
            $conditions[] = $db->quoteName('a.state') . ' IN (' . implode(', ', $publishStatuses) . ')';
        }

        $conditions[] = $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($this->id);

        return $this->hyper['helper']['moyskladService']->findAll([
            'order'      => $order,
            'conditions' => $conditions
        ]);
    }

    /**
     * Get folder products list.
     *
     * @param   array   $conditions
     * @param   string  $order group_id and price will be automatically replaced with product_folder_id and list_price
     * @param   bool    $published only published products
     *
     * @return  MoyskladProduct[]
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getProducts($conditions = [], $order = 'a.name ASC', $published = true)
    {
        $db = $this->hyper['db'];

        $order = preg_replace('/group_id/', 'product_folder_id', $order);
        $order = preg_replace('/\.price\s/', '.list_price ', $order);

        $conditions[] = $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($this->id);

        if ($published === true) {
            $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
            $conditions[] = $db->quoteName('a.state') . ' IN (' . implode(', ', $publishStatuses) . ')';
        }

        return $this->hyper['helper']['moyskladProduct']->findAll([
            'order'      => $order,
            'conditions' => $conditions
        ]);
    }

    /**
     * Get product google category id
     *
     * @return int
     *
     * @since  2.0
     */
    public function getGoogleId()
    {
        $googleId = $this->getParams()->get('google_category_id', 0, 'int');
        if ($googleId === 0) {
            $category = $this->getParent();

            if (empty($category->id)) {
                return GoogleHelper::ELECTRONICS_CATEGORY_ID;
            }

            $googleId = $category->getGoogleId();
        }

        return $googleId;
    }

    /**
     * Get group grid columns number.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getColumns()
    {
        $cols = $this->getParams()->get('group_cols', self::DEFAULT_GRID_COLS);
        if ($cols === '__global__') {
            return (int) $this->hyper['params']->get('cat_cols', self::DEFAULT_GRID_COLS);
        }

        return (int) $cols;
    }

    /**
     * Get heading for table of teasers
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getHeadingForTable()
    {
        $heading = trim($this->getParams()->get('heading_in_teasers_table', ''));
        if (empty($heading)) {
            return $this->title;
        }

        return $heading;
    }

    /**
     * Get allowed mini part fields values.
     *
     * @return  \HYPERPC\Data\JSON
     *
     * @since   2.0
     */
    public function getAllowedMiniPartFieldValues()
    {
        $db = $this->hyper['db'];
        $fieldIds = (array) $this->getParams()->get('mini_allowed_part_fields');

        if (!count($fieldIds)) {
            return new JSON();
        }

        $query = $db
            ->getQuery(true)
            ->select([
                'a.*',
                'b.fieldparams'
            ])
            ->from($db->quoteName('#__fields_values', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__fields', 'b') . ' ON (' . $db->quoteName('a.field_id') . ' = ' . $db->quoteName('b.id') . ')'
            )
            ->where([
                $db->quoteName('a.field_id') . ' IN (' . implode(', ', array_values($fieldIds)) . ')'
            ]);

        $values = [];
        foreach ($db->setQuery($query)->loadObjectList() as $value) {
            $params  = new JSON($value->fieldparams);
            $options = (array) $params->get('options');

            $data = $value;
            unset($data->fieldparams);
            if (count($options)) {
                foreach ($options as $option) {
                    if ($option['value'] === $value->value) {
                        $data->value = $option['name'];
                    }
                }
            }

            $values[$value->item_id][$value->field_id] = $value;
        }

        return new JSON($values);
    }

    /**
     * Get group allowed mini part fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getAllowedMiniPartFields()
    {
        $db = $this->hyper['db'];
        $fieldIds = (array) $this->getParams()->get('mini_allowed_part_fields');

        if (!count($fieldIds)) {
            return [];
        }

        $query = $db
            ->getQuery(true)
            ->select(['a.*'])
            ->from($db->quoteName('#__fields', 'a'))
            ->where([
                $db->quoteName('a.context') . ' = ' . $db->quote(HP_OPTION . '.position'),
                $db->quoteName('a.state')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED),
                $db->quoteName('a.id')      . ' IN (' . implode(', ', array_values($fieldIds)) . ')'
            ])
            ->order('FIELD (a.id, ' . implode(', ', array_values($fieldIds)) . ')');

        $_fields = $db->setQuery($query)->loadAssocList('id');
        $fields  = [];
        foreach ($_fields as $id => $field) {
            $fields[$id] = new Field($field);
        }

        return $fields;
    }

    /**
     * Get group mini parts by product.
     *
     * @todo change the entity when it is created
     *
     * @param   ProductMarker $product
     * @param   bool          $published
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMiniConfiguratorParts(ProductMarker $product, $published = true)
    {
        $partIds    = (array) $product->configuration->get('parts_mini');
        $optionIds  = (array) $product->configuration->get('options_mini');
        $quantities = (array) $product->configuration->get('quantity');

        $db = $this->hyper['db'];

        // todo find how empty parts installed to position
        $partIds = array_diff($partIds, array(''));

        $conditions = [
            $db->qn('a.product_folder_id') . ' = ' . $db->q($this->id),
            $db->qn('a.id') . ' IN (' . implode(', ', array_values($partIds)) . ')'
        ];

        $partCondition[]    = 'NOT (' . $db->qn('a.state') . ' = ' . HP_STATUS_ARCHIVED . ' AND ' . $db->qn('a.balance') . ' = 0)';
        $serviceCondition[] = 'NOT ' . $db->qn('a.state') . ' = ' . HP_STATUS_ARCHIVED;

        if ($published) {
            $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
            $conditions[] = $db->qn('a.state') . ' in (' . implode(',', $publishStatuses) . ')';
        }

        $parts = $this->hyper['helper']['moyskladPart']->findAll([
            'conditions' => array_merge($conditions, $partCondition)
        ]);

        $services = $this->hyper['helper']['moyskladService']->findAll([
            'conditions' => array_merge($conditions, $serviceCondition)
        ]);

        $items = $parts + $services;
        if (empty($items)) {
            return [];
        }

        $options = [];
        if (!empty($optionIds)) {
            $options = $this->hyper['helper']['moyskladVariant']->findAll([
                'conditions' => [
                    $db->qn('a.id') . ' IN (' . implode(', ', array_keys($optionIds)) . ')',
                    'NOT (' . $db->qn('a.state') . ' = ' . HP_STATUS_ARCHIVED . ' AND ' . $db->qn('a.balance') . ' = 0)',
                    $db->qn('a.part_id')  . ' IN (' . implode(', ', array_keys($items)) . ')'
                ]
            ]);
        }

        $inConfigParts = [];
        /** @var Position $part */
        foreach ($parts as $part) {
            $isInConfig = $this->hyper['helper']['configurator']->isPartInConfigurator($product, $part);
            if ($isInConfig) {
                $inConfigParts[$part->id] = $part;
            }
        }
        $parts = $inConfigParts;

        $optionByParts = [];
        /** @var MoyskladVariant $option */
        foreach ($options as $option) {
            if ($option instanceof MoyskladVariant) {
                $isInConfig = $this->hyper['helper']['configurator']->isOptionInConfigurator($product, $option);
                if (!$isInConfig) {
                    continue;
                }

                if (isset($parts[$option->part_id])) {
                    $optionByParts[$option->part_id][$option->id] = $option;
                }
            }
        }

        /** @var MoyskladPart $part */
        foreach ($parts as $part) {
            if ($part->hasOptions()) {
                $part->set('options', (isset($optionByParts[$part->id]) ? $optionByParts[$part->id] : []));
            }

            $part->set('quantity', (isset($quantities[$part->id]) ? $quantities[$part->id] : 1));
        }

        return $items;
    }

    /**
     * Get group order part.
     *
     * @todo add param for moysklad position default order
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPartOrder()
    {
        if (!in_array($this->getParams()->get('parts_order'), [null, 'global'])) {
            return $this->getParams()->get('parts_order');
        }

        return $this->hyper['params']->get('moysklad_positions_order', 'a.list_price ASC');
    }

    /**
     * Get count archive parts.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function hasArchiveParts()
    {
        $showInactive = (bool) $this->getParams()->get('show_inactive', 1);
        $hashKey[]    = $showInactive;
        $db           = $this->hyper['db'];

        $conditions = [
            $db->quoteName('a.state')               . ' = ' . $db->quote(HP_STATUS_ARCHIVED),
            $db->quoteName('a.type_id')             . ' = ' . $db->quote(2),
            $db->quoteName('p.balance')             . ' = ' . $db->quote(0),
            $db->quoteName('a.product_folder_id')   . ' = ' . $db->quote($this->id)
        ];

        if (!$showInactive) {
            $conditions[] = $db->quoteName('p.retail') . ' = ' . $db->quote(1);
        }

        $partQuery = $db
            ->getQuery(true)
            ->select('COUNT(*)')
            ->join('LEFT', $db->qn(HP_TABLE_MOYSKLAD_PARTS, 'p') . ' ON p.id = a.id')
            ->from($db->qn(HP_TABLE_POSITIONS, 'a'))
            ->where($conditions);

        $partCount = (int) $db->setQuery($partQuery)->loadResult();

        $conditions = [
            $db->quoteName('a.state')               . ' = ' . $db->quote(HP_STATUS_ARCHIVED),
            $db->quoteName('v.balance')             . ' = ' . $db->quote(0),
            $db->quoteName('a.product_folder_id')   . ' = ' . $db->quote($this->id),
        ];

        $optionQuery = $db
            ->getQuery(true)
            ->select('COUNT(*)')
            ->join('LEFT', $db->qn(HP_TABLE_MOYSKLAD_PARTS, 'p') . ' ON p.id = a.id')
            ->join('LEFT', $db->qn(HP_TABLE_MOYSKLAD_VARIANTS, 'v') . ' ON v.part_id = p.id')
            ->from($db->qn(HP_TABLE_POSITIONS, 'a'))
            ->where($conditions);

        $optionCount = (int) $db->setQuery($optionQuery)->loadResult();

        return $partCount + $optionCount;
    }

    /**
     * Get view archive url.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrlArchive()
    {
        return $this->hyper['helper']['route']->url([
            'id'   => $this->id,
            'view' => 'group_archive'
        ]);
    }

    /**
     * Get selected group custom fields.
     *
     * @param   array   $params
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getCustomFields(array $params = [])
    {
        $params = new JSON(array_replace_recursive([
            'context'     => 'moysklad_art',
            'fieldSelect' => ['f.*']
        ], $params));

        $hash = md5($params->write());

        $customFields = new JSON([]);
        if (!$customFields->get($hash)) {
            $db  = $this->hyper['db'];
            $ids = (array) $this->getParams()->get('part_fields', []);

            if (count($ids)) {
                $query = $db
                    ->getQuery(true)
                    ->select((array) $params->get('fieldSelect'))
                    ->from($db->quoteName('#__fields', 'f'))
                    ->where([
                        $db->quoteName('f.context') . ' = ' . $db->quote(HP_OPTION . '.' . $params->get('context')),
                        $db->quoteName('f.id') . ' IN (' . implode(', ', $ids) . ')'
                    ])
                    ->order('FIELD (f.id, ' . implode(', ', $ids) . ')');

                $_list = $db->setQuery($query)->loadAssocList('id');

                $class = Field::class;
                $list  = [];
                foreach ($_list as $id => $item) {
                    $list[$id] = new $class($item);
                }

                $customFields->set($hash, $list);
            }
        }

        return (array) $customFields->get($hash);
    }

    /**
     * Get selected group fields for render on part page.
     *
     * @param   int     $partId     Part unique record id.
     * @param   array   $params
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getPartFields($partId, array $params = [])
    {
        $params = new Data(array_replace_recursive([
            'context'     => 'position',
            'fieldSelect' => ['f.*']
        ], $params));

        $db  = $this->hyper['db'];
        $ids = $params->get('part_fields', []) ? (array) $params->get('part_fields', []) : (array) $this->getParams()->get('part_fields', []);
        if (count($ids)) {
            $vQuery = $db
                ->getQuery(true)
                ->select(['b.id', 'a.value', 'b.context'])
                ->join('LEFT', $db->quoteName('#__fields', 'b') . ' ON a.field_id = b.id')
                ->from($db->quoteName('#__fields_values', 'a'))
                ->where([
                    $db->quoteName('a.item_id') . ' = ' . $db->quote($partId),
                    $db->quoteName('b.context') . ' = ' . $db->quote(HP_OPTION . '.' . $params->get('context'))
                ]);

            $query = $db
                ->getQuery(true)
                ->select((array) $params->get('fieldSelect'))
                ->from($db->quoteName('#__fields', 'f'))
                ->where([
                    $db->quoteName('f.context') . ' = ' . $db->quote(HP_OPTION . '.' . $params->get('context')),
                    $db->quoteName('f.id') . ' IN (' . implode(', ', $ids) . ')'
                ])
                ->order('FIELD (f.id, ' . implode(', ', $ids) . ')');

            $values = $db->setQuery($vQuery)->loadObjectList('id');
            $_items = $db->setQuery($query)->loadAssocList('id');

            $class = Field::class;
            $items = [];
            if (is_array($_items)) {
                foreach ($_items as $id => $item) {
                    $items[$id] = new $class($item);
                }
            }

            if (count($values)) {
                foreach ($values as $data) {
                    if (isset($items[$data->id])) {
                        $items[$data->id]->set('value', $data->value);
                    }
                }
            }

            return $items;
        }

        return [];
    }

    /**
     * Get current name for XML file.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getYandexMarketXmlName()
    {
        $yandexName = $this->getYandexMarketName();
        return ($yandexName) ? $yandexName : $this->title;
    }

    /**
     * Get name for Yandex Market.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getYandexMarketName()
    {
        return $this->metadata->get('yandex_m_name');
    }

    /**
     * Get item types
     *
     * @return string
     *
     * @since 2.0
     */
    public function getItemsType()
    {
        return $this->getParams()->get('product_type', 'pc');
    }

    /**
     * Check if reviews is general
     *
     * @return bool
     *
     * @since 2.0
     */
    public function isGeneralReview()
    {
        return $this->getParams()->get('general_review', '0', 'bool');
    }

    /**
     * Check folder can buy flag for items default value.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isForRetailSale()
    {
        return $this->getParams()->get('retail', '0', 'bool');
    }

    /**
     * Check show in config.
     *
     * @return  bool
     *
     * @since   2.0
     *
     * @todo    chassis for product folder
     */
    public function showInConfig()
    {
        return true;
    }

    /**
     * Check is service folder.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isService()
    {
        return in_array($this->id, $this->hyper['params']->get('product_cart_service_folders', [], 'arr'));
    }

    /**
     * Check if parts selling only for upgrade.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOnlyForUpgrade()
    {
        return (bool) $this->getParams()->get('only_upgrade', 0, 'int');
    }

    /**
     * Check is root category.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isRoot()
    {
        return ($this->alias === 'root');
    }

    /**
     * Check folder is archived
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isArchived()
    {
        return (int) $this->published === HP_STATUS_ARCHIVED;
    }

    /**
     * Does the part have any non-empty properties?
     *
     * @param   int $positionId
     *
     * @return  bool
     */
    public function partHasProperties(int $positionId): bool
    {
        /** @var \Joomla\Database\DatabaseInterface $db */
        $db = $this->hyper['db'];
        $query = $db->getQuery(true);

        $fieldIds = $this->getParams()->get('part_fields', [], 'arr');
        if (empty($fieldIds)) {
            return false;
        }

        $query->select('1')
              ->from($db->quoteName('#__fields_values', 'fv'))
              ->join('INNER', $db->quoteName('#__fields', 'f') . ' ON ' . $db->quoteName('fv.field_id') . ' = ' . $db->quoteName('f.id'))
              ->where($db->quoteName('fv.item_id') . ' = ' . $db->quote($positionId))
              ->where($db->quoteName('f.context') . ' = ' . $db->quote(HP_OPTION . '.position'))
              ->whereIn($db->quoteName('f.id'), $fieldIds)
              ->where('TRIM(' . $db->quoteName('fv.value') . ') <> ' . $db->quote(''))
              ->where('LOWER(TRIM(' . $db->quoteName('fv.value') . ')) <> ' . $db->quote('none'))
              ->setLimit(1);

        $db->setQuery($query);

        return (bool) $db->loadResult();
    }

    /**
     * Fields of boolean data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldBoolean()
    {
        return [];
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        $parentFields = parent::_getFieldJsonData();
        return array_merge(
            ['translatable_params'],
            $parentFields
        );
    }

    /**
     * Fields of string.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldString()
    {
        return ['description'];
    }
}
