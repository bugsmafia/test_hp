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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\Joomla\Model\Entity\Interfaces;

/**
 * Interface CMSObject
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface CMSObject
{
    /**
     * Sets a default value if not already assigned
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function def($property, $default = null);

    /**
     * Returns a property of the object or the default value if the property is not set.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed    The value of the property.
     *
     * @since   2.0
     */
    public function get($property, $default = null);

    /**
     * Returns an associative array of object properties.
     *
     * @param   boolean  $public  If true, returns only the public properties.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProperties($public = true);

    /**
     * Get the most recent error message.
     *
     * @param   integer  $i         Option error index.
     * @param   boolean  $toString  Indicates if JError objects should return their error message.
     *
     * @return  string   Error message
     *
     * @since   2.0
     */
    public function getError($i = null, $toString = true);

    /**
     * Return all errors, if any.
     *
     * @return  array  Array of error messages or JErrors.
     *
     * @since   2.0
     */
    public function getErrors();

    /**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $value     The value of the property to set.
     *
     * @return  mixed  Previous value of the property.
     *
     * @since   2.0
     */
    public function set($property, $value = null);

    /**
     * Set the object properties based on a named array/hash.
     *
     * @param   mixed  $properties  Either an associative array or another object.
     *
     * @return  boolean
     *
     * @since   2.0
     */
    public function setProperties($properties);

    /**
     * Add an error message.
     *
     * @param   string  $error  Error message.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function setError($error);
}
