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

namespace HYPERPC\ORM\Entity;

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use Joomla\CMS\Table\Table;

/**
 * Trait EntityTrait
 *
 * @since 2.0
 */
trait EntityTrait
{

    /**
     * Map of properties in this entity that can be safely assigned, each
     * property name points to a boolean indicating its status. An empty array
     * means no properties are accessible.
     *
     * The special property '\*' can also be mapped, meaning that any other property
     * not defined in the map will take its value. For example, `'\*' => true`
     * means that any property not defined in the map will be accessible by default.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_accessible = ['*' => true];

    /**
     * Holds a cached list of getters/setters per class.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_accessors = [];

    /**
     * List of property names that should **not** be included in JSON or Array
     * representations of this Entity.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_hidden = [];

    /**
     * Holds all properties that have been changed and their original values for this entity.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_original = [];

    /**
     * Holds all properties and their values for this entity.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_properties = [];

    /**
     * List of computed or virtual fields that **should** be included in JSON or array
     * representations of this Entity. If a field is present in both _hidden and _virtual
     * the field will **not** be in the array/json versions of the entity.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_virtual = [];

    /**
     * The table type to instantiate.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_tableType;

    /**
     * Table class prefix.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_tablePrefix = HP_TABLE_CLASS_PREFIX;

    /**
     * Hold table object.
     *
     * @var     Table
     *
     * @since   2.0
     */
    protected $_table;

    /**
     * Custom field types.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldTypes = [
        'money'
    ];

    /**
     * Field list of json type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldJsonType = [
        'params'
    ];

    /**
     * Field list of boolean type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldBooleanType = [
        'published'
    ];

    /**
     * Returns an array that can be used to describe the internal state of this object.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function __debugInfo()
    {
        return $this->_properties + [
                '[virtual]'     => $this->_virtual,
                '[original]'    => $this->_original,
                '[accessible]'  => $this->_accessible
            ];
    }

    /**
     * Magic getter to access properties that have been set in this entity.
     *
     * @param   string $property    Name of the property to access.
     * @return  mixed
     *
     * @since   2.0
     */
    public function &__get($property)
    {
        return $this->get($property);
    }

    /**
     * Magic setter to add or edit a property in this entity
     *
     * @param   string $property The name of the property to set
     * @return  bool
     *
     * @since   2.0
     */
    public function __isset($property)
    {
        return $this->has($property);
    }

    /**
     * Magic setter to add or edit a property in this entity.
     *
     * @param   string $property    The name of the property to set.
     * @param   mixed $value        The value to set to the property.
     * @return  void
     *
     * @since   2.0
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * Removes a property from this entity.
     *
     * @param   string $property The property to unset.
     * @return  void
     *
     * @since   2.0
     */
    public function __unset($property)
    {
        $this->unsetProperty($property);
    }

    /**
     * Returns an array with the requested properties
     * stored in this entity, indexed by property name.
     *
     * @param   array $properties list of properties to be returned.
     * @return  array
     *
     * @since   2.0
     */
    public function extract(array $properties)
    {
        $result = [];
        foreach ($properties as $property) {
            $result[$property] = $this->get($property);
        }

        return $result;
    }

    /**
     * Returns an array with the requested original properties
     * stored in this entity, indexed by property name.
     *
     * Properties that are unchanged from their original value will be included in the
     * return of this method.
     *
     * @param   array $properties List of properties to be returned.
     * @return  array
     *
     * @since   2.0
     */
    public function extractOriginal(array $properties)
    {
        $result = [];
        foreach ($properties as $property) {
            $result[$property] = $this->getOriginal($property);
        }

        return $result;
    }

    /**
     * Returns an array with only the original properties
     * stored in this entity, indexed by property name.
     *
     * This method will only return properties that have been modified since
     * the entity was built. Unchanged properties will be omitted.
     *
     * @param   array $properties List of properties to be returned.
     * @return  array
     *
     * @since   2.0
     */
    public function extractOriginalChanged(array $properties)
    {
        $result = [];
        foreach ($properties as $property) {
            $original = $this->getOriginal($property);
            if ($original !== $this->get($property)) {
                $result[$property] = $original;
            }
        }

        return $result;
    }

    /**
     * Returns the value of a property by name.
     *
     * @param   string $property            The name of the property to retrieve.
     * @return  mixed
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function &get($property)
    {
        if (!strlen((string) $property)) {
            throw new \InvalidArgumentException('Cannot get an empty property');
        }

        $value  = null;
        $method = static::_accessor($property, 'get');

        if (isset($this->_properties[$property])) {
            $value =& $this->_properties[$property];
        }

        if ($method) {
            $result = $this->{$method}($value);
            return $result;
        }

        return $value;
    }

    /**
     * Gets the hidden properties.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getHidden()
    {
        return $this->_hidden;
    }

    /**
     * Returns the value of an original property by name.
     *
     * @param   string $property the name of the property for which original value is retrieved.
     * @return  mixed
     *
     * @throws  \InvalidArgumentException if an empty property name is passed.
     *
     * @since   2.0
     */
    public function getOriginal($property)
    {
        if (!strlen((string) $property)) {
            throw new \InvalidArgumentException('Cannot get an empty property');
        }

        if (array_key_exists($property, $this->_original)) {
            return $this->_original[$property];
        }

        return $this->get($property);
    }

    /**
     * Gets all original values of the entity.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getOriginalValues()
    {
        $originals    = $this->_original;
        $originalKeys = array_keys($originals);

        foreach ($this->_properties as $key => $value) {
            if (!in_array($key, $originalKeys)) {
                $originals[$key] = $value;
            }
        }

        return $originals;
    }

    /**
     * Gets the virtual properties on this entity.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getVirtual()
    {
        return $this->_virtual;
    }

    /**
     * Returns whether this entity contains a property named $property that contains a non-null value.
     *
     * @param   string|array $property The property or properties to check.
     * @return  bool
     *
     * @since   2.0
     */
    public function has($property)
    {
        foreach ((array)$property as $prop) {
            if ($this->get($prop) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a property is accessible.
     *
     * @param   string $property Property name to check
     * @return  bool
     *
     * @since   2.0
     */
    public function isAccessible($property)
    {
        $value = isset($this->_accessible[$property]) ?
            $this->_accessible[$property] :
            null;

        return ($value === null && !empty($this->_accessible['*'])) || $value;
    }

    /**
     * Returns the properties that will be serialized as JSON.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function jsonSerialize(): array
    {
        return $this->extract($this->visibleProperties());
    }

    /**
     * Implements isset($entity);
     *
     * @param   mixed $offset The offset to check.
     * @return  bool Success
     *
     * @since   2.0
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Implements $entity[$offset];
     *
     * @param   mixed $offset The offset to get.
     * @return  mixed
     *
     * @since   2.0
     */
    public function &offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Implements $entity[$offset] = $value;
     *
     * @param   mixed $offset   The offset to set.
     * @param   mixed $value    The value to set.
     * @return  void
     *
     * @since   2.0
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Implements unset($result[$offset]);
     *
     * @param   mixed $offset The offset to remove.
     * @return  void
     *
     * @since   2.0
     */
    public function offsetUnset($offset): void
    {
        $this->unsetProperty($offset);
    }

    /**
     * Sets a single property inside this entity.
     *
     * @param   string|array|int $property  The name of property to set or a list of properties with their
     *                                      respective values.
     * @param   mixed $value                The value to set to the property or an array if the first argument is also
     *                                      an array, in which case will be treated as $options.
     * @param   array $options
     * @return  $this
     *
     * @since   2.0
     */
    public function set($property, $value = null, array $options = [])
    {
        $schemaFields = [];
        $table        = $this->getTable();

        if ($table) {
            $schemaFields = (array) $table->getFields();
        }

        if (is_string($property) && $property !== '') {
            $guard    = false;
            $property = [$property => $value];
        } else {
            $guard = true;
        }

        if (!is_array($property)) {
            throw new \InvalidArgumentException('Cannot set an empty property');
        }

        $options += ['guard' => $guard];
        foreach ($property as $p => $value) {
            if ($options['guard'] === true && !$this->isAccessible($p)) {
                continue;
            }

            if (array_key_exists($p, $schemaFields)) {
                $field = new JSON($schemaFields[$p]);

                list ($type) = explode('(', $field->get('Type'));

                if (array_key_exists($p, $this->_fieldTypes)) {
                    $type = $this->_fieldTypes[$p];
                }

                if (in_array($p, $this->_fieldJsonType)) {
                    $type = 'json';
                }

                if (in_array($p, $this->_fieldBooleanType)) {
                    $type = 'boolean';
                }

                if ($field->get('Field') === 'price') {
                    $type = 'money';
                }

                if ($type === 'int unsigned') {
                    $type = 'int';
                }

                $typeClass = '\\HYPERPC\\ORM\\Database\\Type\\' . Inflector::camelize($type) . 'Type';
                if (class_exists($typeClass) && !($value === null && $field->get('Null', false, 'bool'))) {
                    $value = (new $typeClass())->toPHP($value);
                }
            }

            if (
                !array_key_exists($p, $this->_original) &&
                array_key_exists($p, $this->_properties) &&
                $this->_properties[$p] !== $value
            ) {
                $this->_original[$p] = $this->_properties[$p];
            }

            $setter = static::_accessor($p, 'set');
            if ($setter) {
                $value = $this->{$setter}($value);
            }

            $this->_properties[$p] = $value;
        }

        return $this;
    }

    /**
     * Stores whether or not a property value can be changed or set in this entity.
     * The special property `*` can also be marked as accessible or protected, meaning
     * that any other property specified before will take its value. For example
     * `$entity->setAccess('*', true)` means that any property not specified already
     * will be accessible by default.
     *
     * @param   string|array $property single or list of properties to change its accessibility.
     * @param   bool $set true marks the property as accessible, false will  mark it as protected.
     * @return  $this
     *
     * @since   2.0
     */
    public function setAccess($property, $set)
    {
        if ($property === '*') {
            $this->_accessible = array_map(function ($p) use ($set) {
                return (bool) $set;
            }, $this->_accessible);
            $this->_accessible['*'] = (bool) $set;

            return $this;
        }

        foreach ((array) $property as $prop) {
            $this->_accessible[$prop] = (bool) $set;
        }

        return $this;
    }

    /**
     * Sets hidden properties.
     *
     * @param   array $properties An array of properties to hide from array exports.
     * @param   bool $merge Merge the new properties with the existing. By default false.
     * @return  $this
     *
     * @since   2.0
     */
    public function setHidden(array $properties, $merge = false)
    {
        if ($merge === false) {
            $this->_hidden = $properties;
            return $this;
        }

        $properties    = array_merge($this->_hidden, $properties);
        $this->_hidden = array_unique($properties);

        return $this;
    }

    /**
     * Sets the virtual properties on this entity.
     *
     * @param   array $properties An array of properties to treat as virtual.
     * @param   bool $merge Merge the new properties with the existing. By default false.
     * @return  $this
     *
     * @since   2.0
     */
    public function setVirtual(array $properties, $merge = false)
    {
        if ($merge === false) {
            $this->_virtual = $properties;
            return $this;
        }

        $properties     = array_merge($this->_virtual, $properties);
        $this->_virtual = array_unique($properties);

        return $this;
    }

    /**
     * Returns an array with all the properties that have been set to this entity.
     * This method will recursively transform entities assigned to properties into arrays as well.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->visibleProperties() as $property) {
            $value = $this->get($property);
            if (is_array($value)) {
                $result[$property] = [];
                foreach ($value as $k => $entity) {
                    if ($entity instanceof EntityInterface) {
                        $result[$property][$k] = $entity->toArray();
                    } else {
                        $result[$property][$k] = $entity;
                    }
                }
            } elseif ($value instanceof EntityInterface) {
                $result[$property] = $value->toArray();
            } else {
                $result[$property] = $value;
            }
        }

        return $result;
    }

    /**
     * Removes a property or list of properties from this entity.
     *
     * @param   string|array $property The property to unset.
     * @return  $this
     *
     * @since   2.0
     */
    public function unsetProperty($property)
    {
        $property = (array) $property;
        foreach ($property as $p) {
            unset($this->_properties[$p]);
        }

        return $this;
    }

    /**
     * Setup table the table type to instantiate.
     *
     * @param   string $type
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setTableType($type)
    {
        $this->_tableType = $type;
        return $this;
    }

    /**
     * Get table the table type to instantiate.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTableType()
    {
        return $this->_tableType;
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_setTable();
    }

    /**
     * Setup a prefix for the table class name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTablePrefix()
    {
        return $this->_tablePrefix;
    }

    /**
     * Setup table instance.
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setTable()
    {
        $this->_table = Table::getInstance($this->getTableType(), $this->getTablePrefix());
        return $this;
    }

    /**
     * Gt table instance.
     *
     * @return  Table
     *
     * @since   2.0
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Setup a prefix for the table class name.
     *
     * @param   $prefix
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function setTablePrefix($prefix = HP_TABLE_CLASS_PREFIX)
    {
        $this->_tablePrefix = $prefix;
        return $this;
    }

    /**
     * Fetch accessor method name.
     * Accessor methods (available or not) are cached in $_accessors.
     *
     * @param   string $property the field name to derive getter name from.
     * @param   string $type the accessor type ('get' or 'set').
     * @return  string method name or empty string (no method available).
     *
     * @since   2.0
     */
    protected static function _accessor($property, $type)
    {
        $class = static::class;

        if (isset(static::$_accessors[$class][$type][$property])) {
            return static::$_accessors[$class][$type][$property];
        }

        if (!empty(static::$_accessors[$class])) {
            return static::$_accessors[$class][$type][$property] = '';
        }

        if ($class === 'HYPERPC\ORM\Entity\Entity') {
            return '';
        }

        foreach (get_class_methods($class) as $method) {
            $prefix = substr($method, 1, 3);
            if ($method[0] !== '_' || ($prefix !== 'get' && $prefix !== 'set')) {
                continue;
            }

            $field      = lcfirst(substr($method, 4));
            $snakeField = Inflector::underscore($field);
            $titleField = ucfirst($field);

            static::$_accessors[$class][$prefix][$snakeField] = $method;
            static::$_accessors[$class][$prefix][$field] = $method;
            static::$_accessors[$class][$prefix][$titleField] = $method;
        }

        if (!isset(static::$_accessors[$class][$type][$property])) {
            static::$_accessors[$class][$type][$property] = '';
        }

        return static::$_accessors[$class][$type][$property];
    }

    /**
     * Get helper name from class name.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getHelperName()
    {
        $class   = (string) static::class;
        $details = explode('\\', $class);

        return Str::low(array_pop($details));
    }

    /**
     * Get the list of visible properties.
     *
     * The list of visible properties is all standard properties
     * plus virtual properties minus hidden properties.
     *
     * @return  array A list of properties that are 'visible' in all representations.
     *
     * @since   2.0
     */
    public function visibleProperties()
    {
        $properties = array_keys($this->_properties);
        $properties = array_merge($properties, $this->_virtual);

        return array_diff($properties, $this->_hidden);
    }
}
