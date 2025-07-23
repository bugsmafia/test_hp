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

use ArrayAccess;
use JsonSerializable;

/**
 * Interface EntityInterface
 *
 * @since   2.0
 */
interface EntityInterface extends ArrayAccess, JsonSerializable
{

    /**
     * Returns the value of a property by name.
     *
     * @param   string $property the name of the property to retrieve.
     * @return  mixed
     *
     * @since   2.0
     */
    public function &get($property);

    /**
     * Returns whether this entity contains a property named $property
     * regardless of if it is empty.
     *
     * @param   string|array $property The property to check.
     * @return  bool
     *
     * @since   2.0
     */
    public function has($property);

    /**
     * Sets one or multiple properties to the specified value.
     *
     * @param   string|array $property the name of property to set or a list of properties with their respective values.
     * @param   mixed $value The value to set to the property.
     *
     * @return  \HYPERPC\ORM\Entity\EntityInterface
     *
     * @since   2.0
     */
    public function set($property, $value = null);

    /**
     * Sets hidden properties.
     *
     * @param   array $properties An array of properties to hide from array exports.
     * @param   bool $merge Merge the new properties with the existing. By default false.
     * @return  $this
     *
     * @since   2.0
     */
    public function setHidden(array $properties, $merge = false);

    /**
     * Returns an array with all the visible properties set in this entity.
     *
     * *Note* hidden properties are not visible, and will not be output by toArray().
     *
     * @return  array
     *
     * @since   2.0
     */
    public function toArray();

    /**
     * Removes a property or list of properties from this entity.
     *
     * @param   string|array $property The property to unset.
     * @return  \HYPERPC\ORM\Entity\EntityInterface
     *
     * @since   2.0
     */
    public function unsetProperty($property);

    /**
     * Get the list of visible properties.
     *
     * @return  array A list of properties that are 'visible' in all representations.
     *
     * @since   2.0
     */
    public function visibleProperties();
}
