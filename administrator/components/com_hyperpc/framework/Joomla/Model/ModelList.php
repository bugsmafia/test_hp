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
 */

namespace HYPERPC\Joomla\Model;

use HYPERPC\App;
use Joomla\CMS\Table\Table;
use Cake\Utility\Inflector;
use HYPERPC\Helper\AppHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class ModelList
 *
 * @package HYPERPC\Joomla\Model
 *
 * @since   2.0
 */
class ModelList extends ListModel
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     *
     * @deprecated  Use only $this->>hyper
     */
    public $app;

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Hold helper object.
     *
     * @var     AppHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * ModelAdmin constructor.
     *
     * @param   array $config
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->app = $this->hyper = App::getInstance();
        $this->setDbo($this->getTable()->getDbo());
        $this->initialize($config);
    }

    /**
     * Returns a Model object, always creating it.
     *
     * @param   string  $type       The model type to instantiate.
     * @param   string  $prefix     Prefix for the model class name. Optional.
     * @param   array   $config     Configuration array for model. Optional.
     *
     * @return  BaseDatabaseModel|boolean A \JModelLegacy instance or false on failure
     *
     * @since   2.0
     */
    public static function getInstance($type, $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getInstance($type, $prefix, $config);
    }

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  \JDatabaseQuery
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getListQuery()
    {
        $db    = $this->hyper['db'];
        $query = $db->getQuery(true);

        $query
            ->select(['a.*'])
            ->from($db->quoteName($this->getTable()->getTableName(), 'a'));

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.name') . ' LIKE ' . $search);
        }

        $query->order($this->getState('list.order', 'a.id ASC'));

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string $ordering
     * @param   string $direction
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.list.order', 'list_order');
        $this->setState('list.order', $access);

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * @param   string $id
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getStoreId($id = '')
    {
        //  Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('list.order');

        return parent::getStoreId($id);
    }

    /**
     * Setup model helper.
     *
     * @param   AppHelper $helper
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setHelper(AppHelper $helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Get model helper.
     *
     * @return  AppHelper|null
     *
     * @since   2.0
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  Table|\JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = '', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        if (empty($type)) {
            $type = Inflector::camelize(Inflector::pluralize($this->getName()));
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @param   string  $query          The query.
     * @param   int     $limitstart     Offset.
     * @param   int     $limit          The number of records.
     *
     * @return  mixed|array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $table = $this->getTable();
        $table->getDbo()->setQuery($query, $limitstart, $limit);

        if (!method_exists($table, 'getEntity')) {
            throw new \Exception('Method ' . get_class($table) . '::getEntity() not found');
        }

        $class = $this->getTable()->getEntity();
        if ($class !== 'stdClass') {
            $return = [];
            $items  = $table->getDbo()->loadAssocList('id', $class);
            foreach ((array) $items as $id => $item) {
                $return[$id] = new $class((array) $item);
            }

            return $return;
        }

        return $table->getDbo()->loadObjectList('id');
    }
}
