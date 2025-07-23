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

namespace HYPERPC\ORM\Table;

use HYPERPC\App;
use Joomla\CMS\Filesystem\Path;
use HYPERPC\Joomla\Model\Entity\Entity;
use Joomla\CMS\Table\Table as BaseTable;
use HYPERPC\ORM\Entity\Entity as ORMEntity;

/**
 * Class Table
 *
 * @package     HYPERPC\Joomla\Table
 *
 * @since       2.0
 */
class Table extends BaseTable
{

    use TableTrait;

    /**
     * Hold HYPERPC Application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Entity object name
     *
     * @var     Entity
     *
     * @since   2.0
     */
    protected $_entity;

    /**
     * Table constructor.
     *
     * @param   string $table
     * @param   mixed $key
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct($table, $key, \JDatabaseDriver $db)
    {
        $this->hyper = App::getInstance();

        parent::__construct($table, $key, $db);
        $this->initialize();
    }

    /**
     * Static method to get an instance of a Table class if it can be found in the table include paths.
     *
     * To add include paths for searching for Table classes see Table::addIncludePath().
     *
     * @param   string  $type    The type (name) of the Table class to get an instance of.
     * @param   string  $prefix  An optional prefix for the table class name.
     * @param   array   $config  An optional array of configuration values for the Table object.
     *
     * @return  Table|boolean   A Table object if found or boolean false on failure.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function getInstance($type, $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        //  Sanitize and prepare the table class name.
        $type       = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
        $tableClass = $prefix . ucfirst($type);

        //  Only try to load the class if it doesn't already exist.
        if (!class_exists($tableClass)) {
            //  Search for the class file in the JTable include paths.
            jimport('joomla.filesystem.path');

            $paths = self::addIncludePath();
            $pathIndex = 0;

            while (!class_exists($tableClass) && $pathIndex < count($paths)) {
                if ($tryThis = Path::find($paths[$pathIndex++], strtolower($type) . '.php')) {
                    //  Import the class file.
                    /** @noinspection PhpIncludeInspection */
                    include_once $tryThis;
                }
            }

            if (!class_exists($tableClass)) {
                /*
                * If unable to find the class file in the Table include paths. Return false.
                * The warning JLIB_DATABASE_ERROR_NOT_SUPPORTED_FILE_NOT_FOUND has been removed in 3.6.3.
                * In 4.0 an Exception (type to be determined) will be thrown.
                * For more info see https://github.com/joomla/joomla-cms/issues/11570
                */

                return false;
            }
        }

        $app = App::getInstance();

        //  If a database object was passed in the configuration array use it, otherwise get the global one from \JFactory.
        $db = isset($config['dbo']) ? $config['dbo'] : $app['db'];

        //  Instantiate a new table class and return it.
        return new $tableClass($db);
    }

    /**
     * Initialize table.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setEntity();
    }

    /**
     * Method to provide a shortcut to binding, checking and storing a Table instance to the database table.
     *
     * The method will check a row in once the data has been stored and if an ordering filter is present will attempt to reorder
     * the table rows based on the filter.  The ordering filter is an instance property name.  The rows that will be reordered
     * are those whose value matches the Table instance for the property specified.
     *
     * @param   array|object $src
     * @param   string $orderingFilter
     * @param   string $ignore
     *
     * @return  bool
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function save($src, $orderingFilter = '', $ignore = '')
    {
        if ($src instanceof Entity) {
            $src = $src->getArray();
        } elseif ($src instanceof ORMEntity) {
            $src = $src->toArray();
        }

        return parent::save($src, $orderingFilter, $ignore);
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   bool $updateNulls
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function store($updateNulls = false)
    {
        $this->_setDates();
        $this->_setUser();

        return parent::store($updateNulls);
    }

    /**
     * Table properties extract to entity class.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function toEntity()
    {
        $entityClass = $this->getEntity();
        $properties  = get_object_vars($this);

        return new $entityClass($properties);
    }
}
