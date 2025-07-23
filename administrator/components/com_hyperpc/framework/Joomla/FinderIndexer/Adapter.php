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

namespace HYPERPC\Joomla\FinderIndexer;

use HYPERPC\App;
use JBZoo\Utils\Str;
use Joomla\CMS\Table\Table;
use Cake\Utility\Inflector;
use Joomla\Database\QueryInterface;
use HYPERPC\Joomla\Model\Entity\Entity;
use Joomla\CMS\Component\ComponentHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Adapter as FinderIndexerAdapter;

/**
 * Adapter class
 *
 * @package HYPERPC\Joomla\FinderIndexer
 *
 * @since   2.0
 */
abstract class Adapter extends FinderIndexerAdapter
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
     * Hold removed part entity.
     *
     * @var     Entity
     *
     * @since   2.0
     */
    protected $_removed_part;

    /**
     * Name of indexer node.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name = 'part';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $state_field = 'published';

    /**
     * The title filed.
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $title_field = 'name';

    /**
     * Context name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_contextName;

    /**
     * Method to instantiate the indexer adapter.
     *
     * @param   object  $subject  The object to observe.
     * @param   array   $config   An array that holds the plugin configuration.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(&$subject, $config)
    {
        $this->extension = HP_OPTION;
        $this->hyper     = App::getInstance();

        $this->_setTable();

        $this->layout       = Str::low($this->_name);
        $this->_contextName = Str::low($this->_name);
        $this->type_title   = Inflector::camelize($this->_name);
        $this->context      = Inflector::camelize(Inflector::pluralize($this->_name));

        parent::__construct($subject, $config);
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  boolean  True on success.
     *
     * @since   2.0
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete($context, $table)
    {
        if (method_exists($table, 'toEntity')) {
            $this->_removed_part = $table->toEntity();
        }

        if ($context === HP_OPTION . '.' . $this->_contextName) {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return true;
        }

        //  Remove item from the index.
        return $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for a category that has been saved.
     * It also makes adjustments if the access level of the category has changed.
     *
     * @param   string $context The context of the category passed to the plugin.
     * @param   Table|\HyperPcTableParts $row A JTable object.
     * @param   boolean $isNew True if the category has just been created.
     *
     * @return  boolean True on success.
     *
     * @throws  \Exception on database error.
     *
     * @since   2.0
     */
    public function onFinderAfterSave($context, $row, $isNew)
    {
        $notebookGroups = (array) $this->hyper['params']->get('notebook_groups', []);

        //  We only want to handle categories here.
        if ($context === HP_OPTION . '.' . $this->_contextName && !in_array($row->group_id, $notebookGroups)) {
            //  Reindex the category item.
            $this->reindex($row->id);
        }

        return true;
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the category passed to the plugin.
     * @param   Table    $row      A JTable object.
     * @param   boolean  $isNew    True if the category is just about to be created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.0
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        return true;
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the category passed to the plugin.
     * @param   array    $pks      An array of primary key ids of the category that has changed state.
     * @param   integer  $value    The value of the state that the category has been changed to.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function onFinderChangeState($context, $pks, $value)
    {
        //  We only want to handle categories here.
        if ($context === HP_OPTION . '.' . $this->_contextName) {
            /**
             * The category published state is tied to the parent category
             * published state so we need to look up all published states
             * before we change anything.
             */
            foreach ($pks as $pk) {
                $query = clone $this->getStateQuery();
                $query->where('a.id = ' . (int) $pk);

                $this->db->setQuery($query);
                $item = $this->db->loadObject();

                //  Translate the state.
                $state = $item->state;
                $temp  = $this->translateState($value, $state);

                //  Update the item.
                $this->change($pk, 'state', $temp);

                //  Reindex the item.
                $this->reindex($pk);
            }
        }

        //  Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Get part link remove administrator prefix.
     *
     * @param   string $url
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getFrontendEntityUrl($url)
    {
        return str_replace('/administrator/', '', $url);
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A JDatabaseQuery object or null.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.0
     */
    protected function getListQuery($query = null)
    {
        $db = $this->db;
        //  Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->getQuery(true)->select(['a.*']);

        //  Handle the alias CASE WHEN portion of the query.
        $caseWhenItemAlias = ' CASE WHEN ';
        $caseWhenItemAlias .= $query->charLength('a.alias', '!=', '0');
        $caseWhenItemAlias .= ' THEN ';

        $aId = $query->castAs('CHAR', 'a.id');
        $caseWhenItemAlias .= $query->concatenate([$aId, 'a.alias'], ':');
        $caseWhenItemAlias .= ' ELSE ';
        $caseWhenItemAlias .= $aId . ' END as slug';

        $query
            ->select($caseWhenItemAlias)
            ->from($db->quoteName($this->table, 'a'))
            ->where($db->quoteName('a.id') . ' >= 1');

        return $query;
    }

    /**
     * Method to get a SQL query to load the published and access states for
     * an article and category.
     *
     * @return  \JDatabaseQuery  A database object.
     *
     * @since   2.0
     */
    protected function getStateQuery()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id')
            ->select('a.' . $this->state_field . ' AS state')
            ->from($this->db->quoteName($this->table, 'a'));

        return $query;
    }

    /**
     * Method to index an item. The item must be a FinderIndexerResult object.
     *
     * @param   Result $item    The item to index as a FinderIndexerResult object.
     * @param   string $format  The item format.  Not used.
     *
     * @return  void
     *
     * @throws  \Exception on database error.
     *
     * @since   2.0
     */
    protected function index(Result $item, $format = 'html')
    {
        //  Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        if ($this->hyper['helper']->loaded($this->_contextName)) {
            /** @var Entity $entity */
            $entity = $this->hyper['helper'][$this->_contextName]->getById($item->getElement('id'));
            if ($entity->get('id')) {
                $item->setLanguage();

                //  Handle the link to the metadata.
                $item->addInstruction(Indexer::META_CONTEXT, 'link');
                $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
                $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
                $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
                $item->addInstruction(Indexer::META_CONTEXT, 'author');

                //  Build the necessary route and path information.
                $item->url   = $this->_getFrontendEntityUrl($entity->getViewUrl());
                $item->route = $item->url;
                //$item->path  = Helper::getContentPath($item->route);

                if ($entity instanceof Position) {
                    switch ($entity->type_id) {
                        case 1:
                            $this->type_title = 'Service';
                            break;
                        case 2:
                            $this->type_title = 'Part';
                            break;
                        case 3:
                            $this->type_title = 'Product';
                            break;
                    }

                    $this->type_id = $item->type_id = $this->getTypeId();
                    // Add the content type if it doesn't exist and is set.
                    if (empty($item->type_id) && !empty($this->type_title)) {
                        $item->type_id = Helper::addContentType($this->type_title, $item->mime);
                    }
                }

                //  Get the menu title if it exists.
                $item->title = $entity->get($this->title_field);

                //  Trigger the onContentPrepare event.
                $item->summary = $this->_getEntitySummary($entity);

                //  Translate the state. Categories should only be published if the parent category is published.
                $item->state  = $this->translateState($entity->get($this->state_field));
                $item->access = 1;

                //  Add the type taxonomy data.
                $item->addTaxonomy('Type', $this->type_title);

                //  Add the language taxonomy data.
                $item->addTaxonomy('Language', $item->language);

                //  Get content extras.
                Helper::getContentExtras($item);

                //  Index the item.
                $this->indexer->index($item);
            }
        } else {
            throw new \Exception('Not find ' . $this->_contextName . ' helper', 500);
        }
    }

    /**
     * Method to remove an item from the index.
     *
     * @param   string  $id  The ID of the item to remove.
     * @param   bool    $removeTaxonomies Remove empty taxonomies
     *
     * @return  boolean  True on success.
     *
     * @throws  \Exception on database error.
     *
     * @since   2.5
     */
    protected function remove($id, $removeTaxonomies = true)
    {
        if ($this->hyper['helper']->loaded($this->_contextName)) {
            /** @var Entity $entity */
            $entity = $this->hyper['helper'][$this->_contextName]->findById($id);
            if (!$entity->get('id')) {
                $entity = $this->_removed_part;
            }

            if ($entity->get('id')) {
                //  Get the link ids for the content items.
                $query = $this->db
                    ->getQuery(true)
                    ->select($this->db->quoteName('link_id'))
                    ->from($this->db->quoteName('#__finder_links'))
                    ->where($this->db->quoteName('url') . ' = ' . $this->db->quote($this->_getFrontendEntityUrl($entity->getViewUrl())));

                $this->db->setQuery($query);
                $items = $this->db->loadColumn();

                //  Check the items.
                if (empty($items)) {
                    return true;
                }

                //  Remove the items.
                foreach ($items as $item) {
                    $this->indexer->remove($item);
                }

                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.0
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Setup adapter table by name.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _setTable()
    {
        if ($this->_name === 'product') {
            $this->table = HP_TABLE_PRODUCTS;
        } elseif ($this->_name === 'position') {
            $this->table = HP_TABLE_POSITIONS;
        } elseif ($this->_name === 'productfolder') {
            $this->table = HP_TABLE_PRODUCT_FOLDERS;
        } else {
            throw new \Exception('Not find table', 500);
        }
    }
}
