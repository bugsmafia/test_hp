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

namespace HYPERPC\Joomla\Categories;

use HYPERPC\App;
use HyperPcViewProduct_Folder;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Categories\Categories as BaseCategories;

/**
 * @var HyperPcViewProduct_Folder $this
 */

/**
 * Class Categories
 *
 * @package     HYPERPC\Joomla\Categories
 *
 * @since       2.0
 */
class Categories extends BaseCategories
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Name of included helper.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected static $_defaultHelperName = 'category';

    /**
     * Categories constructor.
     *
     * @param   array $options
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $options)
    {
        $options['extension'] = HP_OPTION;
        $options['helper']    = (array_key_exists('helper', $options)) ? $options['helper'] : self::$_defaultHelperName;

        $this->hyper = App::getInstance();

        parent::__construct($options);
    }

    /**
     * Returns a reference to a Categories object.
     *
     * @param   string  $extension  Name of the categories extension.
     * @param   array   $options    An array of options.
     *
     * @return  BaseCategories|boolean  Categories object on success, boolean false if an object does not exist.
     *
     * @since   2.0
     */
    public static function getInstance($extension, $options = [])
    {
        $hash = md5(strtolower($extension) . serialize($options));

        if (isset(self::$instances[$hash])) {
            return self::$instances[$hash];
        }

        $options['helper'] = (array_key_exists('helper', $options)) ? $options['helper'] : self::$_defaultHelperName;

        $parts     = explode('.', $extension);
        $component = 'com_' . strtolower($parts[0]);
        $section   = count($parts) > 1 ? $parts[1] : '';
        $className = ucfirst(substr($component, 4)) . ucfirst($section) . ucfirst($options['helper']);

        if (!class_exists($className)) {
            $path = JPATH_SITE . '/components/' . $component . '/helpers/' . $options['helper'] . '.php';
            \JLoader::register($className, $path);

            if (!class_exists($className)) {
                return false;
            }
        }

        self::$instances[$hash] = new $className($options);

        return self::$instances[$hash];
    }

    /**
     * Load method.
     *
     * @param   int $id Id of category to load.
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _load($id)
    {
        /** @var \JDatabaseDriver */
        $db = $this->_getDbo();

        /** Record that has this $id has been checked. */
        $this->_checkedCategories[$id] = true;

        $query = $db
            ->getQuery(true)
            ->select(['c.*']);

        $caseWhen = ' CASE WHEN ';
        $caseWhen .= $query->charLength('c.alias', '!=', '0');
        $caseWhen .= ' THEN ';

        $c_id = $query->castAsChar('c.id');
        $caseWhen .= $query->concatenate([$c_id, 'c.alias'], ':');
        $caseWhen .= ' ELSE ';
        $caseWhen .= $c_id . ' END as slug';

        if ($this->_options['published'] == 1) {
            $query->where('c.published = 1');
        }

        $query->order('c.lft');

        /** Note: s for selected id. */
        if ($id != 'root') {

            /** Get the selected category. */
            $query
                ->from($db->quoteName($this->_table, 's'))
                ->where('s.id = ' . (int) $id);

            $query->innerJoin(
                $db->quoteName($this->_table, 'c')
                . ' ON (s.lft <= c.lft AND c.lft < s.rgt)'
                . ' OR (c.lft < s.lft AND s.rgt < c.rgt)'
            );
        } else {
            $query->from($db->quoteName($this->_table, 'c'));
        }

        /** Note: i for item. */
        if ($this->_options['countItems'] == 1) {
            $subQuery = $db->getQuery(true)
                ->select('COUNT(i.' . $db->quoteName($this->_key) . ')')
                ->from($db->quoteName($this->_table, 'i'))
                ->where('i.' . $db->quoteName($this->_field) . ' = c.id');

            if ($this->_options['published'] == 1) {
                $subQuery->where('i.' . $this->_statefield . ' = 1');
            }

            $query->select('(' . $subQuery . ') AS numitems');
        }

        /** Get the results. */
        $db->setQuery($query);
        $results = $db->loadObjectList('id');
        $childrenLoaded = false;

        if (count($results)) {
            /** Foreach categories. */
            foreach ($results as $result) {

                /** Deal with root category. */
                if ($result->id == 1) {
                    $result->id = 'root';
                }

                /** Deal with parent_id. */
                if ($result->parent_id == 1) {
                    $result->parent_id = 'root';
                }

                /** Create the node */
                if (!isset($this->_nodes[$result->id])) {

                    /** Create the CategoryNode and add to _nodes. */
                    $this->_nodes[$result->id] = new CategoryNode($result, $this);

                    /**
                     * If this is not root and if the current node's parent
                     * is in the list or the current node parent is 0.
                     */
                    if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 1)) {
                        // Compute relationship between node and its parent - set the parent in the _nodes field
                        $this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
                    }

                    /**
                     * If the node's parent id is not in the _nodes list and the node is not root
                     * (doesn't have parent_id == 0), then remove the node from the list.
                     */
                    if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0)) {
                        unset($this->_nodes[$result->id]);
                        continue;
                    }

                    if ($result->id == $id || $childrenLoaded) {
                        $this->_nodes[$result->id]->setAllLoaded();
                        $childrenLoaded = true;
                    }

                } elseif ($result->id == $id || $childrenLoaded) {

                    /** Create the CategoryNode. */
                    $this->_nodes[$result->id] = new CategoryNode($result, $this);

                    if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id)) {
                        /** Compute relationship between node and its parent. */
                        $this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
                    }

                    /**
                     * If the node's parent id is not in the _nodes list and the node is not root
                     * (doesn't have parent_id == 0), then remove the node from the list.
                     */
                    if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0)) {
                        unset($this->_nodes[$result->id]);
                        continue;
                    }

                    if ($result->id == $id || $childrenLoaded) {
                        $this->_nodes[$result->id]->setAllLoaded();
                        $childrenLoaded = true;
                    }

                }
            }
        } else {
            $this->_nodes[$id] = null;
        }
    }

    /**
     * Get current DBO.
     *
     * @return  \JDatabaseDriverMysqli|mixed
     *
     * @since   2.0
     */
    protected function _getDbo()
    {
        return $this->hyper['db'];
    }
}
