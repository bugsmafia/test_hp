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
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class HyperPcModelMoysklad_Variant
 *
 * @since   2.0
 */
class HyperPcModelMoysklad_Variant extends ModelAdmin
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
        $this->setHelper($this->hyper['helper']['moyskladVariant']);

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
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Moysklad_Variants', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  bool|ProductFolder
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var MoyskladVariant */
        $item = clone $this->getItem();

        unset($item->app, $item->hyper);

        return $item;
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

        /** @var HyperPcTableMoysklad_Variants */
        $table = $this->getTable();

        $pk      = (!empty($data['id'])) ? $data['id'] : 0;
        $isNew   = true;
        $context = $this->option . '.' . $this->name;

        //  Include the plugins for the save events.
        PluginHelper::importPlugin($this->events_map['save']);

        // Load the row if saving an existing category.
        if ($pk > 0) {
            $table->load($pk);
        }

        if (!empty($table->id)) {
            $isNew = false;
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
        $event  = new Event($this->event_before_save, [$context, $table, $isNew, $data]);
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

        $this->setState($this->getName() . '.id', $table->id);
        $this->setState($this->getName() . '.new', $isNew);

        //   Clear the cache.
        $this->cleanCache();

        return true;
    }
}
