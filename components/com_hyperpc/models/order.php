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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;
use Joomla\CMS\Form\Form;
use Joomla\Event\Event;
use Joomla\CMS\Factory;
use HYPERPC\Money\Type\Money;
use Joomla\Registry\Registry;
use HYPERPC\Helper\CartHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\ModelForm;
use HYPERPC\Joomla\Model\Entity\Order;

/**
 * Class HyperPcModelOrder
 *
 * @since   2.0
 */
class HyperPcModelOrder extends ModelForm
{

    /**
     * Order item list.
     *
     * @var     Data
     *
     * @since   2.0
     */
    public $items;

    /**
     * Order items total.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $itemsTotal;

    /**
     * Name of table.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $name = 'orders';

    /**
     * The event to trigger after saving the data.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_eventAfterSave = 'onSiteAfterSaveOrder';

    /**
     * The event to trigger before saving the data.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_eventBeforeSave = 'onSiteBeforeSaveOrder';

    /**
     * Hold order object when controller save action processed.
     *
     * @var     null|Order
     *
     * @since   2.0
     */
    protected static $_order;

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name                The table name. Optional.
     * @param   string  $prefix              The class prefix. Optional.
     * @param   array   $options             Configuration array for model. Optional.
     *
     * @return  \HyperPcTableOrders|JTable  A \JTable object
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($name = '', $prefix = HP_TABLE_CLASS_PREFIX, $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Setup order object to hold when save action in controller.
     *
     * @param   Order $order
     *
     * @return  void
     *
     * @since   2.0
     */
    public static function setHoldOrder(Order $order)
    {
        if (self::$_order === null) {
            self::$_order = $order;
        }
    }

    /**
     * Get hold order object.
     *
     * @return  Order|null
     *
     * @since   2.0
     */
    public static function getHoldOrder()
    {
        return self::$_order;
    }

    /**
     * Getting the form from the model.
     *
     * @param   array   $data
     * @param   bool    $loadData
     *
     * @return  bool|\Joomla\CMS\Form\Form
     *
     * @since   2.0
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(HP_OPTION . '.order', 'order', [
            'control'   => 'jform',
            'load_data' => $loadData
        ]);

        if ($form === null) {
            return false;
        }

        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];
        if (!$cartHelper->showCaptcha()) {
            $form->removeField('captcha');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  Order
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function loadFormData()
    {
        /** @var Order $item */
        $item      = $this->getItem();
        $item->cid = $this->hyper['helper']['google']->getCID();

        if ($item->id !== 0) {
            $item->id = (int) $item->id;
        }

        if ($this->hyper['input']->get('view') === 'credit' && !$item->id) {
            $item->set('form', HP_ORDER_FORM_CREDIT);
        }

        /** @var Money $total */
        $total = $this->itemsTotal;
        $item->total = $total->val();

        return $item->getArray();
    }

    /**
     * Method to get a single record.
     *
     * @param   null|int $pk
     *
     * @return  bool|object|Order
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        /** @var HyperPcTableOrders $table */
        $table = $this->getTable();

        if ($pk > 0) {
            //  Attempt to load the row.
            $return = $table->load($pk);
            //  Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());
                return false;
            }
        }

        //  Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);
        $entity = $table->getEntity();
        if ($entity === 'JObject') {
            $item = ArrayHelper::toObject($properties, 'JObject');

            if (property_exists($item, 'params')) {
                $registry = new Registry($item->params);
                $item->params = $registry->toArray();
            }

            if (property_exists($item, 'metadata')) {
                $registry = new Registry($item->metadata);
                $item->metadata = $registry->toArray();
            }

            return $item;
        }

        return new $entity($properties);
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
        $dispatcher = Factory::getApplication()->getDispatcher();
        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;

        $isNew  = true;
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        //  Include the plugins for the save events.
        PluginHelper::importPlugin('content');

        //  Allow an exception to be thrown.
        try {
            //  Load the row if saving an existing record.
            if ($pk > 0) {
                $table->load($pk);
                $isNew = false;
            }

            if (array_key_exists('elements', $data)) {
                foreach ($data['elements'] as $elementKey => $elementData) { // sanitize user input
                    foreach ($elementData as $valueKey => $value) {
                        if (is_string($value)) {
                            $data['elements'][$elementKey][$valueKey] = trim(strip_tags($value));
                        }
                    }
                }

                $elements = new Registry($data['elements']);

                if ($elements->get('email.value') === 'save@hyperpc.ru') {
                    $data['elements']['phone']['value'] = '+7 (123) 000-00-00';
                    $data['elements']['username']['value'] = 'Save';
                }

                if (empty($data['delivery_type'])) {
                    $data['delivery_type'] = $elements->get('delivery_method.value');
                }

                if (empty($data['payment_type'])) {
                    $data['payment_type'] = $elements->get('payments.value');
                }
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
            $event = new Joomla\Event\Event($this->_eventBeforeSave, [$context, $table, $isNew, $data]);
            $result = $dispatcher->dispatch($this->_eventBeforeSave, $event);
            if ($result->isStopped()) {
                $this->setError($table->getError());
                return false;
            }

            //  Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            //  Clean the cache.
            $this->cleanCache();

            //  Trigger the after save event.
            $event = new Joomla\Event\Event($this->_eventAfterSave, [$context, $table, $isNew, $data]);
            $result = $dispatcher->dispatch($this->_eventAfterSave, $event);
            if ($result->isStopped()) {
                $errors = (array) $this->hyper['cms']->getMessageQueue();
                foreach ($errors as $error) {
                    $error = new Registry($error);
                    $this->setError($error->get('message'), $error->get('type', 'error'));
                }

                return false;
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        if (isset($table->$key)) {
            $this->setState($this->getName() . '.id', $table->$key);
        }

        $this->setState($this->getName() . '.new', $isNew);

        return true;
    }
}
