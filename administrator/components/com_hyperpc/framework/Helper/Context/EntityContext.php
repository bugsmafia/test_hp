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

namespace HYPERPC\Helper\Context;

use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use JBZoo\Data\Data;
use Cake\Utility\Inflector;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Helper\AppHelper;
use HYPERPC\Helper\RenderHelper;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseDriver;
use HYPERPC\Joomla\Model\Entity\Entity;
use Joomla\CMS\Table\Table as JoomlaTable;

/**
 * Class EntityContext
 *
 * @package HYPERPC\Helper\Context
 *
 * @since   2.0
 */
class EntityContext extends AppHelper
{

    /**
     * Current SQL table.
     *
     * @var     Table
     *
     * @since   2.0
     */
    protected $_table;

    /**
     * Hold data base object.
     *
     * @return  DatabaseDriver  The internal database driver object.
     *
     * @since   2.0
     */
    protected $_db;

    /**
     * Hold data from findListData method.
     *
     * @var     array
     *
     * @since   2.0
     *
     * @deprecated  Use new find method
     */
    protected static $_findListData = [];

    /**
     * Hold data from getAllData method.
     *
     * @var     array
     *
     * @since   2.0
     *
     * @deprecated  Use new find method
     */
    protected static $_getAllData = [];

    /**
     * Hold data from getByData method.
     *
     * @var     array
     *
     * @since   2.0
     *
     * @deprecated  Use new find method
     */
    protected static $_getByData = [];

    /**
     * Hold data from getByIds method.
     *
     * @var     array
     *
     * @since   2.0
     *
     * @deprecated  Use new find method
     */
    protected static $_getByIds = [];

    /**
     * Hold data from getByPrice method.
     *
     * @var     array
     *
     * @since   2.0
     *
     * @deprecated  Use new find method
     */
    protected static $_getByPrice = [];

    /**
     * Hold result data from $this->findAll().
     *
     * @var     array
     *
     * @since   1.0
     */
    protected static $_findAllData = [];

    /**
     * Hold result data from $this->findBy().
     *
     * @var     array
     *
     * @since   1.0
     */
    protected static $_findByData = [];

    /**
     * Handles of dynamic finders.
     *
     * @param   string $method      Name of the method to be invoked.
     * @param   array  $args        List of arguments passed to the function.
     *
     * @return  mixed
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __call($method, $args)
    {
        if (preg_match('/^find(?:\w+)?By/', $method) > 0) {
            return $this->_dynamicFinder($method, $args);
        }

        throw new \Exception("Unknown method {$method}");
    }

    /**
     * Count record by conditions.
     *
     * @param   array $conditions
     *
     * @return  int
     *
     * @since   2.0
     */
    public function count(array $conditions = [])
    {
        $db = $this->_table->getDbo();

        $query = $db
            ->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->_getFromQuery());

        $this->_setConditions($query, $conditions);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Find all record.
     *
     * @param   array $options
     *
     * @return  array|mixed
     *
     * @throws  \Exception
     *
     * @since   1.0
     */
    public function findAll(array $options = [])
    {
        $options = new Data(array_replace([
            'conditions' => [],
            'key'        => 'id',
            'select'     => ['a.*'],
            'order'      => 'a.id ASC',
            'offset'     => 0,
            'limit'      => 0
        ], $options));

        $options->set('table', $this->_table->getTableName());

        $hash = md5($options->write());

        if (!array_key_exists($hash, self::$_findAllData)) {
            $db = $this->_table->getDbo();

            $query = $db
                ->getQuery(true)
                ->select($options->get('select'))
                ->from($this->_getFromQuery())
                ->order($options->get('order'));

            $this->_setConditions($query, $options->get('conditions'));
            $_list = (array) $db
                ->setQuery($query, $options->get('offset'), $options->get('limit'))
                ->loadAssocList($options->get('key'));

            $class = $this->_getTableEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_findAllData[$hash] = $list;
        }

        return self::$_findAllData[$hash];
    }

    /**
     * Delete record from database.
     *
     * @param   array  $options
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function delete(array $options = []): bool
    {
        $options = new Data(array_replace([
            'conditions' => []
        ], $options));

        $db     = $this->_table->getDbo();
        $table  = $this->_table->getTableName();
        $query  = $db->getQuery(true)->delete($db->quoteName($table));

        if ($options->get('id')) {
            $query->where($db->qn('id') . ' = '. $db->q($options->get('id')));
        } else {
            $this->_setConditions($query, $options->get('conditions'));
        }

        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Find by entity object column key value.
     *
     * @param   string  $key          Key of table column.
     * @param   mixed   $value        Value of table column.
     * @param   array   $options
     *
     * @return  array|mixed
     *
     * @throws  \Exception
     *
     * @since   1.0
     */
    public function findBy($key, $value, array $options = [])
    {
        $class = $this->_getTableEntity();

        $options = array_replace([
            'offset'     => 0,
            'limit'      => 0,
            'conditions' => [],
            'select'     => ['a.*']
        ], $options);

        if (is_callable($options['conditions'])) {
            $options['conditions'] = (array) call_user_func($options['conditions'], $this->hyper['db']);
        }

        $options = new Data($options);

        $options
            ->set('key', $key)
            ->set('value', $value)
            ->set('table', $this->_table->getTableName());

        $hash = md5($options->write());

        if (empty($value)) {
            return $this->hyper['helper']['object']->create([], $class);
        }

        $db = $this->_table->getDbo();
        $query = $db
            ->getQuery(true)
            ->select($options->get('select'))
            ->from($this->_getFromQuery());

        if (is_array($value)) {
            $query->whereIn($db->qn('a.' . $key), $value, ParameterType::STRING);
        } else {
            $query->where($db->qn('a.' . $key) . ' = ' . $db->q($value));
        }

        if ($options->get('order')) {
            $query->order($options->get('order'));
        }

        $this->_setConditions($query, $options->get('conditions'));

        $list  = [];
        if ($options->get('new') === true) {
            if (is_array($value)) {
                $_list = $db
                    ->setQuery($query, $options->get('offset'), $options->get('limit'))
                    ->loadAssocList($options->get('key'));

                foreach ($_list as $id => $item) {
                    $list[$id] = new $class($item);
                }

                return $list;
            }

            $item = $db->setQuery($query)->loadAssoc();
            $item = new $class(is_array($item) ? $item : []);

            return $item;
        }

        if (!array_key_exists($hash, self::$_findByData)) {
            if (is_array($value)) {
                $_list = $db->setQuery($query)->loadAssocList($options->get('key'));

                foreach ($_list as $id => $item) {
                    $list[$id] = new $class($item);
                }

                self::$_findByData[$hash] = $list;
            } else {
                $item  = $db->setQuery($query)->loadAssoc();

                self::$_findByData[$hash] = new $class(is_array($item) ? $item : []);
            }
        }

        return self::$_findByData[$hash];
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();
        $this->_db = $this->_table->getDbo();
    }

    /**
     * Find list records.
     *
     * @param       array   $select
     * @param       array   $conditions
     * @param       string  $order
     * @param       string  $key Array key from entity
     * @param       bool    $cache
     *
     * @return      null|array
     *
     * @throws      \RuntimeException
     *
     * @deprecated  Use $this->findAll()
     *
     * @since       2.0
     */
    public function findList(array $select = ['a.*'], array $conditions = [], $order = 'a.id ASC', $key = null, $cache = true)
    {
        $data = new Data([
            'key'        => $key,
            'order'      => $order,
            'select'     => $select,
            'conditions' => $conditions
        ]);

        $data->set('table', $this->_table->getTableName());

        $hash = md5($data->write());

        if (!array_key_exists($hash, self::$_findListData)) {
            $db = $this->_table->getDbo();

            $query = $db
                ->getQuery(true)
                ->select($select)
                ->from($this->_getFromQuery())
                ->order($order);

            $query = $this->_setConditions($query, $conditions);

            $_list = $db->setQuery($query)->loadAssocList($key);

            $class = $this->_getTableEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_findListData[$hash] = (array) $list;
        }

        return self::$_findListData[$hash];
    }

    /**
     * Get all records.
     *
     * @param       array       $select
     * @param       string      $order
     * @param       array       $conditions
     * @param       null|string $key
     * @param       bool        $cache
     *
     * @return      array
     *
     * @throws      \RuntimeException
     *
     * @deprecated  Use $this->findAll()
     *
     * @since       2.0
     */
    public function getAll(array $select = ['a.*'], $order = 'a.id ASC', array $conditions = [], $key = null, $cache = true)
    {
        $data = new Data([
            'key'        => $key,
            'order'      => $order,
            'select'     => $select,
            'conditions' => $conditions
        ]);

        $data->set('table', $this->_table->getTableName());

        $hash = md5($data->write());
        if (!array_key_exists($hash, self::$_getAllData)) {
            $db = $this->_table->getDbo();

            $query = $db
                ->getQuery(true)
                ->select($select)
                ->from($this->_getFromQuery())
                ->order($order);

            $query = $this->_setConditions($query, $conditions);

            $_list = $db->setQuery($query)->loadAssocList($key);

            $class = $this->_getTableEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_getAllData[$hash] = $list;
        }

        return self::$_getAllData[$hash];
    }

    /**
     * Get record by key.
     *
     * @param       string|array    $value
     * @param       string          $key
     * @param       array           $select
     * @param       array           $conditions
     * @param       bool            $cache
     *
     * @return      Entity
     *
     * @throws      \RuntimeException
     *
     * @deprecated  Use $this->findBy()
     *
     * @since       2.0
     */
    public function getBy($key, $value, array $select = ['a.*'], array $conditions = [], $cache = true)
    {
        $data = new Data([
            'key'        => $key,
            'value'      => $value,
            'select'     => $select,
            'conditions' => $conditions
        ]);

        $data->set('table', $this->_table->getTableName());

        $hash = md5($data->write());

        if (!array_key_exists($hash, self::$_getByData)) {
            $db = $this->_table->getDbo();
            $query = $db
                ->getQuery(true)
                ->select($select)
                ->from($this->_getFromQuery());

            if (is_array($value)) {
                $query->where($db->qn('a.' . $key) . ' IN(' . implode(', ', $value) . ')');
            } else {
                $query->where($db->qn('a.' . $key) . ' = ' . $db->quote($value));
            }

            $entity = $this->_getTableEntity();
            $query  = $this->_setConditions($query, $conditions);
            $item   = $db->setQuery($query)->loadAssoc();

            self::$_getByData[$hash] = new $entity($item);
        }

        return self::$_getByData[$hash];
    }

    /**
     * Get record by id.
     *
     * @param       string  $id
     * @param       array   $select
     * @param       array   $conditions
     * @param       bool    $cache
     *
     * @return      Entity
     *
     * @throws      \RuntimeException
     *
     * @deprecated  Use $this->findById()   Magic method.
     *
     * @since       2.0
     */
    public function getById($id, array $select = ['a.*'], array $conditions = [], $cache = false)
    {
        return $this->getBy(HP_TABLE_PRIMARY_KEY, $id, $select, $conditions, $cache);
    }

    /**
     * Get records by ids.
     *
     * @param       array|string    $id
     * @param       array           $select
     * @param       string          $order
     * @param       null|string     $key
     * @param       array           $conditions
     * @param       bool            $cache
     * @return      array
     *
     * @throws      \RuntimeException
     *
     * @deprecated  Use $this->findById([$id])   Magic method.
     *
     * @since       2.0
     */
    public function getByIds($id, array $select = ['a.*'], $order = 'a.id ASC', $key = null, array $conditions = [], $cache = false)
    {
        $ids = (array) $id;
        if (!count($ids)) {
            return [];
        }

        $data = new Data([
            'ids'        => $ids,
            'key'        => $key,
            'order'      => $order,
            'select'     => $select,
            'conditions' => $conditions
        ]);

        $data->set('table', $this->_table->getTableName());

        $hash = md5($data->write());

        if (!array_key_exists($hash, self::$_getByIds)) {
            $db = $this->_table->getDbo();
            $query = $db
                ->getQuery(true)
                ->select($select)
                ->from($this->_getFromQuery())
                ->order($order)
                ->where($db->qn('a.id') . ' IN (' . implode(', ', $ids) . ')');

            $query = $this->_setConditions($query, $conditions);

            $_list = $db->setQuery($query)->loadAssocList($key);

            $class = $this->_getTableEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_getByIds[$hash] = $list;
        }

        return self::$_getByIds[$hash];
    }

    /**
     * Get records by price range.
     *
     * @param       array           $price
     * @param       array           $select
     * @param       string          $order
     * @param       null|string     $key
     * @param       array           $conditions
     * @param       bool            $cache
     * @return      array
     *
     * @throws      \RuntimeException
     *
     * @deprecated  Use $this->findById([$id])   Magic method.
     *
     * @since       2.0
     */
    public function getByPrice(array $price, array $select = ['a.*', 'c.id as category_id'], $order = 'a.price ASC', $key = null, array $conditions = [], $cache = false)
    {
        $data = new Data([
            'price'      => $price,
            'key'        => $key,
            'order'      => $order,
            'select'     => $select,
            'conditions' => $conditions
        ]);

        $data->set('table', $this->_table->getTableName());

        $hash = md5($data->write());

        if (!array_key_exists($hash, self::$_getByIds)) {
            $db = $this->_table->getDbo();
            $query = $db
                ->getQuery(true)
                ->select($select)
                ->from($this->_getFromQuery())
                ->join('right', $db->qn('#__hp_categories', 'c') . ' ON a.category_id = c.id')
                ->order($order);

            if (count($price) == 2) {
                $query->where($db->qn('a.price') . ' >= ' . $db->q(intval($price[0])));
                $query->where($db->qn('a.price') . ' <= ' . $db->q(intval($price[1])));
            } else {
                $query->where($db->qn('a.price') . ' < ' . $db->q(intval($price[0])));
            }
            $query->where($db->qn('a.published') . ' = ' . HP_STATUS_PUBLISHED);

            $query = $this->_setConditions($query, $conditions);

            $_list = $db->setQuery($query)->loadAssocList($key);

            $class = $this->_getTableEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_getByPrice[$hash] = $list;
        }

        return self::$_getByPrice[$hash];
    }

    /**
     * Get table name.
     *
     * @return  JoomlaTable
     *
     * @since   2.0
     */
    public function getTable(): JoomlaTable
    {
        return $this->_table;
    }

    /**
     * Get DBO object.
     *
     * @return  DatabaseDriver
     *
     * @since   2.0
     */
    public function getDbo(): DatabaseDriver
    {
        return $this->_db;
    }

    /**
     * Translate object id to alias.
     *
     * @param   int     $id         The object id.
     * @param   array   $select     Select fields.
     *
     * @return  Entity
     *
     * @since   2.0
     *
     * @deprecated  not used
     */
    public function translateIDToAlias($id, array $select = ['a.alias'])
    {
        $id = (int) $id;
        return $this->getById($id, $select);
    }

    /**
     * Translate object alias to id.
     *
     * @param   string $alias   The object alias.
     * @param   string $key     The object select name.
     *
     * @return  Entity
     *
     * @since   2.0
     *
     * @deprecated  not used
     */
    public function translateAliasToId($alias, $key = 'alias')
    {
        return $this->getBy($key, $alias, ['a.id']);
    }

    /**
     * Render entity partial.
     *
     * @param   string  $name
     * @param   array   $args
     * @param   bool    $cached
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function partial($name, array $args = [], $cached = false)
    {
        $group = str_replace(HP_TABLE_CLASS_PREFIX, '', get_class($this->_table));
        $group = Str::low(Inflector::singularize($group));

        return $this->hyper['helper']['render']->render(FS::clean($group . '/' . $name), $args, RenderHelper::DEFAULT_GROUP, $cached);
    }

    /**
     * Setup SQL table name.
     *
     * @param   JoomlaTable $table
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setTable(JoomlaTable $table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * Provides the dynamic findBy and findByAll methods.
     *
     * @param   string  $method       The method name that was fired.
     * @param   array   $args         List of arguments passed to the function.
     *
     * @return  mixed
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _dynamicFinder($method, $args)
    {
        $_method = Inflector::underscore($method);
        preg_match('/^find_by_([\w]+)/', $_method, $matches);
        if (empty($matches)) {
            $findType = 'all';
        } else {
            $findType = Inflector::variable($matches[1]);
        }

        $callableMethod = str_replace(Inflector::camelize($findType), '', $method);
        if (method_exists($this, $callableMethod)) {
            array_unshift($args, Inflector::underscore($findType));
            return call_user_func_array([$this, $callableMethod], $args);
        }

        throw new \Exception("Unknown method {$method}");
    }

    /**
     * Setup query conditions.
     *
     * @param   DatabaseQuery   $query
     * @param   array           $conditions
     *
     * @return  DatabaseQuery
     *
     * @since   2.0
     */
    protected function _setConditions(DatabaseQuery $query, array $conditions = [])
    {
        if (count($conditions)) {
            foreach ($conditions as $condition) {
                $query->where($condition);
            }
        }

        return $query;
    }

    /**
     * Get table entity.
     *
     * @return  Entity
     *
     * @since   2.0
     */
    protected function _getTableEntity()
    {
        return $this->_table->getEntity();
    }

    /**
     * Get query for from condition
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getFromQuery()
    {
        return $this->getDbo()->qn($this->getTable()->getTableName(), 'a');
    }
}
