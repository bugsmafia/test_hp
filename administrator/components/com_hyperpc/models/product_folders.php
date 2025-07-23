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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcModelProduct_Folders
 *
 * @since   2.0
 */
class HyperPcModelProduct_Folders extends ModelList
{

    public function __construct($config = array())
    {
        $config['filter_fields'] = array('a.lft', 'a.published', 'published', 'a.title', 'a.id');
        parent::__construct($config);
    }

    /**
     * Get table object.
     *
     * @param   string $type
     * @param   string $prefix
     * @param   array $config
     *
     * @return  Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Product_Folders', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Get all folders.
     *
     * @param   bool $loadRoot
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getFolders($loadRoot = true)
    {
        $table = $this->getTable();
        $db    = $this->_db;

        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName($table->getTableName(), 'a'));

        if ($loadRoot) {
            $query->where(['NOT ' . $db->quoteName('a.alias') . ' = ' . $db->quote('root')]);
        }

        $query->order($db->quoteName('a.lft') . ' ASC');

        $entity = $table->getEntity();
        $list   = $db->setQuery($query)->loadAssocList();

        return $this->hyper['helper']['object']->createList($list, $entity);
    }

    /**
     * Get categories by parent id.
     *
     * @param   int $parent
     * @param   string $order
     * @return  mixed
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getByParent($parent = 1, $order = 'ASC')
    {
        $db    = $this->_db;
        $query = $db->getQuery(true);

        /** @var HyperPcTableCategories $table */
        $table     = $this->getTable();
        $tableName = $table->getTableName();

        $query
            ->select('a.*')
            ->from($db->quoteName($tableName, 'a'))
            ->order($db->quoteName('a.lft') . ' ' . $db->escape($order))
            ->where([
                'NOT ' . $db->quoteName('a.alias') . ' = ' . $db->quote('root'),
                $db->quoteName('a.parent_id') . ' = ' . $db->quote($parent)
            ]);

        $return      = [];
        $entityClass = $table->getEntity();
        $entities    = $db->setQuery($query)->loadAssocList('id', $entityClass);

        foreach ($entities as $entity) {
            $return[$entity['id']] = new $entityClass($entity);
        }

        return $return;
    }

    /**
     * Get path categories entity.
     *
     * @param   int $id
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getPath($id = 1)
    {
        $return      = [];
        $table       = $this->getTable();
        $entityClass = $table->getEntity();
        $categories  = (array) $table->getPath($id);

        foreach ($categories as $category) {
            $return[$category->id] = new $entityClass((array) $category);
        }

        return $return;
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  JDatabaseQuery
     *
     * @throws  \RuntimeException|\Exception
     *
     * @since   2.0
     */
    protected function getListQuery()
    {
        $db           = $this->_db;
        $query        = $db->getQuery(true);
        $fullOrdering = $this->getState('list.fullordering', 'a.lft ASC');

        list($order, $direction) = explode(' ', $fullOrdering);

        $query
            ->select('*')
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'))
            ->order($db->quoteName($order) . ' ' . $direction);

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.title') . ' LIKE ' . $search);
        }

        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where($db->quoteName('a.published') . ' = ' . (int) $published);
        } elseif ($published === '') {
            $defaultStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_UNPUBLISHED];
            $query->where($db->quoteName('a.published') . ' IN (' . implode(', ', $defaultStatuses) . ')');
        }

        return $query;
    }

    /**
     * Build group tree.
     *
     * @param   array $groups
     * @param   int $parentId
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function buildTree(array $productFolders = [], $parentId = 1)
    {
        $tree = [];
        if (!count($productFolders)) {
            $productFolders = $this->getFolders();
        }

        /** @var ProductFolder $productFolder */
        foreach ($productFolders as $productFolder) {
            if ($productFolder->parent_id === $parentId) {
                $children = $this->buildTree($productFolders, $productFolder->id);
                if ($children) {
                    $productFolder->set('children', $children);
                }
                $tree[$productFolder->id] = $productFolder;
            }
        }

        return $tree;
    }
}
