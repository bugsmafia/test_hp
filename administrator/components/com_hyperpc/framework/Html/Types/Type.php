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

namespace HYPERPC\Html\Types;

defined('_JEXEC') or die('Restricted access');

/**
 * Class Type
 *
 * @package HYPERPC\Html\Data\Types
 *
 * @since   2.0
 */
class Type
{

    /**
     * List name of available properties.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_properties = [];

    /**
     * Type constructor.
     *
     * @param   array  $values
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $key => $value) {
            if (in_array($key, $this->_properties)) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Set property.
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function set($key, $value)
    {
        $this->$key = $value;
        return $this;
    }
}
