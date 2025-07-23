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

namespace HYPERPC\Joomla\Model\Entity;

use HYPERPC\App;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use HYPERPC\Render\Render;
use Cake\Utility\Inflector;
use Joomla\CMS\Object\CMSObject;
use JBZoo\SimpleTypes\Type\Money;

/**
 * Class Entity
 *
 * @package     HYPERPC\Joomla\Model
 * @property    JSON $metadata
 *
 * @since   2.0
 */
abstract class Entity extends CMSObject
{

    /**
     * Hold HYPERPC Application object.
     *
     * @var     App
     *
     * @since   2.0
     *
     * @deprecated  Use only $this->hyper
     */
    public $app;

    /**
     * Hold HYPERPC Application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Product render.
     *
     * @var     Render|null
     *
     * @since   2.0
     */
    protected $_render;

    /**
     * Entity constructor.
     *
     * @param   array $properties
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct($properties = null)
    {
        $this->app = $this->hyper = App::getInstance();
        $this->bindData($properties);
        $this->initialize();
    }

    /**
     * Bind entity data.
     *
     * @param   array $rowData
     * @return  void
     *
     * @since   2.0
     */
    public function bindData($rowData)
    {
        $rowData = (array) $rowData;
        if ($rowData) {
            foreach ($rowData as $propName => $propValue) {
                if ($propName !== 'app' && property_exists($this, $propName)) {
                    $this->_prepareData($propName, $propValue);
                }
            }
        }
    }

    /**
     * Get array properties.
     *
     * @param   bool $public
     * @return   mixed
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getArray($public = true)
    {
        $properties = get_object_vars($this);

        if ($public) {
            foreach ($properties as $key => $value) {
                if ('_' === substr($key, 0, 1)) {
                    unset($properties[$key]);
                }

                if (in_array($key, $this->_getFieldMoney()) && $value instanceof Money) {
                    $properties[$key] = $value->val();
                }

                if (in_array($key, $this->_getFieldJsonData()) && $value instanceof JSON) {
                    $properties[$key] = $value->getArrayCopy();
                }

                if (in_array($key, $this->_getFieldDate()) && $value instanceof Date) {
                    $properties[$key] = $value->toSql();
                }

                if (in_array($key, $this->_getFieldBoolean())) {
                    $properties[$key] = Filter::int($value);
                }

                if (in_array($key, ['helper'])) {
                    unset($properties[$key]);
                }
            }
        }

        if (array_key_exists('app', $properties)) {
            unset($properties['app']);
        }

        if (array_key_exists('hyper', $properties)) {
            unset($properties['hyper']);
        }

        return $properties;
    }

    /**
     * Get render object.
     *
     * @return  Render|null|\HYPERPC\Render\Order
     *
     * @since   2.0
     */
    public function getRender()
    {
        return $this->_render;
    }

    /**
     * Get site view category url.
     *
     * @return  string
     * @param   array $query
     *
     * @since   2.0
     */
    abstract public function getViewUrl(array $query = []);

    /**
     * Initialize entity.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setRender();
    }

    /**
     * Get product render.
     *
     * @return      Render
     *
     * @deprecated  Use the getRender() method. Only!
     *
     * @since       2.0
     */
    public function render()
    {
        return $this->_render;
    }

    /**
     * Setup entity renderer.
     *
     * @param   string $name
     * @param   null|Entity $entity     Set personal entity data for render.
     * @return  $this
     *
     * @since   2.0
     */
    public function setRender($name = null, $entity = null)
    {
        if (!$name) {
            $className = explode('\\', get_class($this));
            $name = Inflector::singularize(end($className));
        }

        if (!$this->_render) {
            $object = 'HYPERPC\\Render\\' . Inflector::camelize($name);
            if (class_exists($object)) {
                /** @var Render $render */
                $render = new $object();
                if ($entity === null) {
                    $entity = $this;
                }

                $render->setEntity($entity);
                $this->_render = $render;
            }
        }

        return $this;
    }

    /**
     * Fields of boolean data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldBoolean()
    {
        return ['published', 'to_1c'];
    }

    /**
     * Fields of datetime.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldDate()
    {
        return ['created_time', 'modified_time'];
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        return [
            'id', 'level', 'parent_id', 'modified_user_id', 'created_user_id',
            'lft', 'rgt', 'ordering', 'count', 'balance', 'part_id', 'can_by', 'quantity',
            'group_id', 'status', 'form', 'saved_configuration', 'quantity', 'category_id'
        ];
    }

    /**
     * Fields of float data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldFloat()
    {
        return [];
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return ['params', 'metadata', 'review'];
    }

    /**
     * Fields of money.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldMoney()
    {
        return ['price', 'price_average', 'total'];
    }

    /**
     * Fields of string.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldString()
    {
        return [];
    }

    /**
     * Prepare entity properties.
     *
     * @param   mixed $propName
     * @param   mixed $propValue
     * @return  void
     *
     * @since   2.0
     */
    protected function _prepareData($propName, $propValue)
    {
        /** Prepare JSON Data. */
        if (in_array($propName, $this->_getFieldJsonData())) {
            $propValue = new JSON($propValue);
        }

        /** Prepare integer data. */
        if (in_array($propName, $this->_getFieldInt())) {
            $propValue = Filter::int($propValue);
        }

        /** Prepare float data. */
        if (in_array($propName, $this->_getFieldFloat())) {
            $propValue = Filter::float($propValue);
        }

        /** Prepare date data. */
        if (in_array($propName, $this->_getFieldDate())) {
            $propValue = new Date($propValue);
        }

        /** Prepare boolean data. */
        if (in_array($propName, $this->_getFieldBoolean())) {
            $propValue = Filter::bool($propValue);
        }

        /** Prepare money data. */
        if (in_array($propName, $this->_getFieldMoney())) {
            $propValue = $this->hyper['helper']['money']->get($propValue);
        }

        /** Prepare string data. */
        if (in_array($propName, $this->_getFieldString())) {
            $propValue = (string) $propValue;
        }

        $this->set($propName, $propValue);
    }
}
