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

use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcModelProduct_Folder
 *
 * @since   2.0
 */
class HyperPcModelProduct_Folder extends ModelAdmin
{

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->setHelper($this->hyper['helper']['productFolder']);

        $this->getState();
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
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Product_Folders', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool|ProductFolder
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var ProductFolder */
        $item = clone $this->getItem();

        if (property_exists($item, 'published')) {
            $item->set('published', (int) $item->published);
        }

        unset($item->app, $item->hyper);

        return $item;
    }

    /**
     * Build group tree.
     *
     * @param   array   $folders
     * @param   int     $parentId
     * @return  array
     *
     * @throws  RuntimeException|Exception
     *
     * @since   2.0
     */
    public function buildTree(array $folders = [], $parentId = 1)
    {
        $tree = [];
        /** @var ProductFolder $folder */
        foreach ($folders as $folder) {
            if ($folder->parent_id === $parentId) {
                $children = $this->buildTree($folders, $folder->id);
                if ($children) {
                    $folder->set('children', $children);
                }
                $tree[$folder->id] = $folder;
            }
        }

        return $tree;
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data
     * @return  bool
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function save($data)
    {
        $dispatcher = Factory::getApplication()->getDispatcher();

        /** @var HyperPcTableProduct_Folders */
        $table = $this->getTable();

        $pk      = (!empty($data['id'])) ? $data['id'] : 0;
        $isNew   = true;
        $context = $this->option . '.' . $this->name;

        /** @todo Add root category if not exist. */

        //  Include the plugins for the save events.
        PluginHelper::importPlugin($this->events_map['save']);

        // Load the row if saving an existing category.
        if ($pk > 0) {
            $table->load($pk);
        }

        if (!empty($table->id)) {
            $isNew = false;
        }

        //  Set the new parent id if parent id not matched OR while New/Save as Copy .
        if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
            $table->setLocation($data['parent_id'], 'last-child');
        }

        //  Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());
            return false;
        }

        //  Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }

        //  Trigger the before save event.
        $event  = new Event($this->event_before_save, [$context, &$table, $isNew, $data]);
        $result = $dispatcher->dispatch($this->event_before_save, $event);
        if (in_array(false, $result->getArgument('result'), true)) {
            $this->setError($table->getError());
            return false;
        }

        //  Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        //  Trigger the after save event.
        $event  = new Event($this->event_after_save, [$context, &$table, $isNew, $data]);
        $dispatcher->dispatch($this->event_after_save, $event);

        // Rebuild the path for the category.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());
            return false;
        }

        //  Rebuild the paths of the category's children.
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        //   Clear the cache.
        $this->cleanCache();

        return true;
    }
}
