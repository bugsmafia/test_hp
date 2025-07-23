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
use Joomla\Registry\Registry;
use HYPERPC\Helper\MoySkladHelper;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * Class HyperPcModelMoysklad_Product
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Product extends ModelAdmin
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
        $this->setHelper($this->hyper['helper']['moyskladProduct']);

        $this->getState();
    }

    /**
     * Method to get a single record.
     *
     * @param   null|int $pk
     *
     * @return  bool|object|Entity
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        /** @var \HyperPcTableMoysklad_Products */
        $productTable = $this->getTable();

        if ($pk > 0) {
            //  Attempt to load the row.
            $return = $productTable->load($pk);
            //  Check for a table object error.
            if ($return === false && $productTable->getError()) {
                $this->setError($productTable->getError());
                return false;
            }
        }

        /** @var \HyperPcTablePositions */
        $positionsTable = $this->getTable('Positions', HP_TABLE_CLASS_PREFIX, []);

        //  Convert to the JObject before adding other data.
        $properties = $productTable->getProperties(1);
        $entity = $productTable->getEntity();

        $positionsTable->load($pk);
        $positionProperties = $positionsTable->getProperties(1);

        $properties = array_merge($properties, $positionProperties);

        return new $entity($properties);
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
    public function getTable($type = 'Moysklad_Products', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool|MoyskladProduct
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var MoyskladProduct */
        $item = clone $this->getItem();

        unset($item->app, $item->hyper);

        return $item;
    }

    /**
     * Method to save the form data.
     *
     * @param   array $data  The form data.
     *
     * @return  boolean True on success, False on error.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function save($data)
    {
        $pk = (!empty($data['id'])) ? $data['id'] : 0;

        /** @var HyperPcTablePositions */
        $positionsTable = $this->getTable('Positions');

        //  Allow an exception to be thrown.
        try {
            //  Load the row if saving an existing record.
            if ($pk > 0) {
                $positionsTable->load($pk);
            }

            if (!$this->_saveDataToTable($positionsTable, $data)) {
                return false;
            }

            /** @var HyperPcTableMoysklad_Products */
            $productsTable = $this->getTable();

            $productsTable->load($positionsTable->id);

            if (empty($productsTable->id)) {
                $data['id'] = $positionsTable->id;
            }

            if (!$this->_saveDataToTable($productsTable, $data)) {
                return false;
            }

            $this->setState($this->getName() . '.id', $positionsTable->id);

            //  Clean the cache.
            $this->cleanCache();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Method to save data to the table.
     *
     * @param   Table $table
     * @param   array $data
     *
     * @return  boolean True on success, false on error.
     *
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    protected function _saveDataToTable(Table $table, array $data)
    {
        $dispatcher = Factory::getApplication()->getDispatcher();

        $context = $this->option . '.' . $this->name;
        $isNew = true;

        if (!empty($table->id)) {
            $isNew = false;
        }

        //  Include the plugins for the save events.
        PluginHelper::importPlugin($this->events_map['save']);

        /** @var MoyskladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];

        //  Bind the data.
        if (!$table->bind($data)) {
            $error = $table->getError();
            $this->setError($error);
            $moyskladHelper->log('Can\'t bind data to position: ' . $error);
            return false;
        }

        //  Check the data.
        if (!$table->check()) {
            $error = $table->getError();
            $this->setError($error);
            $moyskladHelper->log('Check position data failed: ' . $error);
            return false;
        }

        //  Trigger the before save event.
        $event  = new Event($this->event_before_save, [$context, $table, $isNew, $data]);
        $result = $dispatcher->dispatch($this->event_before_save, $event);
        if (in_array(false, $result->getArgument('result'), true)) {
            $this->setError($table->getError());
            return false;
        }

        //  Store the data.
        if (!$table->store()) {
            $error = $table->getError();
            $this->setError($error);
            $moyskladHelper->log('Can\'t store data to position: ' . $error);
            return false;
        }

        //  Trigger the after save event.
        $event  = new Event($this->event_after_save, [$context, $table, $isNew, $data]);
        $result = $dispatcher->dispatch($this->event_after_save, $event);
        if (in_array(false, $result->getArgument('result', []), true)) {
            $errors = (array) $this->hyper['cms']->getMessageQueue();
            foreach ($errors as $error) {
                $error = new Registry($error);
                $this->setError($error->get('message'), $error->get('type', 'error'));
            }

            return false;
        }

        $this->setState($this->getName() . '.new', $isNew);

        return true;
    }
}
