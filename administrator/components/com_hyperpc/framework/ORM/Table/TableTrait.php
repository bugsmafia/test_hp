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
use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use HYPERPC\ORM\Marshaller;
use Cake\Utility\Inflector;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * Trait TableTrait
 *
 * @property    App $hyper
 *
 * @package     HYPERPC\ORM\Table
 *
 * @since       2.0
 */
trait TableTrait
{

    /**
     * Database port.
     *
     * @var     int
     *
     * @since   2.0
     */
    protected $_dbPost = 3306;

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $array   An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     * @return  bool
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        if (array_key_exists('params', $array)) {
            $params = new JSON($array['params']);
            $array['params'] = $params->write();
        } else {
            $array['params'] = '{}';
        }

        if (array_key_exists('metadata', $array)) {
            $metadata = new JSON($array['metadata']);
            $array['metadata'] = $metadata->write();
        } else {
            $array['metadata'] = '{}';
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Get entity class name.
     *
     * @return  Entity
     *
     * @since   2.0
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Get the object used to marshal/convert array data into objects.
     *
     * @return  Marshaller
     *
     * @since   2.0
     */
    public function getMarshaller()
    {
        static $_instance;

        if (!$_instance) {
            $_instance = new Marshaller($this);
        }

        return $_instance;
    }

    /**
     * Setup entity class.
     *
     * @param   string|null $entityName
     * @return  $this
     *
     * @since   2.0
     */
    public function setEntity($entityName = null)
    {
        if (!$entityName) {
            $entityName = Inflector::singularize(str_replace(HP_TABLE_CLASS_PREFIX, '', get_class($this)));
            $entityName = str_replace('_', '', $entityName);
        }
        $this->_entity = $this->hyper['helper']['object']->getEntityClass($entityName, 'JObject');
        return $this;
    }

    /**
     * Method to set created and modified time to the database table.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setDates()
    {
        $date = Factory::getDate();

        if (property_exists($this, 'modified_time')) {
            $this->modified_time = $date->toSql();
        }

        if (property_exists($this, 'created_time')) {
            if ((!$this->_autoincrement || !$this->id) && empty($this->created_time)) {
                $this->created_time = $date->toSql();
            }
        }
    }

    /**
     * Method to set created and modified user id to the database table.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setUser()
    {
        $user = Factory::getUser();
        $userId = $user->get('id');

        if (property_exists($this, 'modified_user_id') && $this->id) {
            $this->modified_user_id = $userId;
        }

        if (property_exists($this, 'created_user_id') && empty($this->created_user_id)) {
            $this->created_user_id = $userId;
        }
    }
}
