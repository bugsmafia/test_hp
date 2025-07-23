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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\Entity;
use Joomla\CMS\Table\Nested as BaseNested;
use Joomla\CMS\Application\ApplicationHelper;

/**
 * Class TableNested
 *
 * @package     HYPERPC\Joomla\Table
 *
 * @property    string $alias
 * @property    string $created_time
 * @property    string $created_user_id
 * @property    string $description
 * @property    string $id
 * @property    string $meta_desc
 * @property    string $meta_keys
 * @property    string $meta_title
 * @property    string $modified_time
 * @property    string $modified_user_id
 * @property    string $params
 * @property    string $parent_id
 * @property    string $published
 * @property    string $title
 *
 * @since       2.0
 */
class Nested extends BaseNested
{

    use TableTrait;

    /**
     * Hold HYPERPC application object.
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
     * Table name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name;

    /**
     * TableNested constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct($db)
    {
        $this->hyper = App::getInstance();
        $this->initialize();

        parent::__construct($this->_tbl, $this->_tbl_keys, $db);
    }

    /**
     * Add the root node to an empty table.
     *
     * @return  bool|mixed
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function addRoot()
    {
        $db = $this->hyper['db'];

        $query = $db->getQuery(true)
            ->insert($this->_tbl)
            ->set('parent_id = 0')
            ->set('lft = 0')
            ->set('published = 1')
            ->set('rgt = 1')
            ->set('level = 0')
            ->set('title = ' . $db->quote('root'))
            ->set('alias = ' . $db->quote('root'))
            ->set('path = ' . $db->quote(''));
        $db->setQuery($query);

        if (!$db->execute()) {
            return false;
        }

        return $db->insertid();
    }

    /**
     * Override check function.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function check()
    {
        if (trim($this->title) === '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));
            return false;
        }

        $this->alias = trim($this->alias);
        if ($this->alias === '') {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return true;
    }

    /**
     * Static method to get an instance of a Table class if it can be found in the table include paths.
     *
     * To add include paths for searching for Table classes see Table::addIncludePath().
     *
     * @param   string $type The type (name) of the Table class to get an instance of.
     * @param   string $prefix An optional prefix for the table class name.
     * @param   array $config An optional array of configuration values for the Table object.
     * @return  Table|boolean A Table object if found or boolean false on failure.
     *
     * @since   2.0
     */
    public static function getInstance($type, $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getInstance($type, $prefix, $config);
    }

    /**
     * Get table name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Initialize hook table method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setName()->setEntity();
    }

    /**
     * Sett table name.
     *
     * @param   string|null $name
     * @return  $this
     *
     * @since   2.0
     */
    public function setName($name = null)
    {
        if (!$name) {
            $name = str_replace('_', '', str_replace(HP_TABLE_CLASS_PREFIX, '', get_class($this)));
        }

        $this->_name = $name;
        return $this;
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   bool $updateNulls
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function store($updateNulls = false)
    {
        $this->_setDates();
        $this->_setUser();

        //  Verify that the alias is unique.
        /** @var BaseNested $table */
        $table = Table::getInstance($this->_name, HP_TABLE_CLASS_PREFIX, ['dbo' => $this->getDbo()]);
        if ($table->load(['alias' => $this->alias, 'parent_id' => (int) $this->parent_id]) &&
            ($table->id != $this->id || $this->id == 0)
        ) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
    }
}
