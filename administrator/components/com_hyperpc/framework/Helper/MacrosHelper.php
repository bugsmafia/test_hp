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

namespace HYPERPC\Helper;

use Cake\Utility\Hash;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * Class MacrosHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MacrosHelper extends AppHelper
{

    /**
     * Hold data.
     *
     * @var array
     *
     * @since   2.0
     */
    protected $_data = [];

    /**
     * Get data value by key.
     *
     * @param   null $key
     * @param   null $default
     *
     * @return  array|null
     *
     * @since   2.0
     */
    public function get($key = null, $default = null)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }

        return $default;
    }

    /**
     * Add new value in list.
     *
     * @param   string|int $key
     * @param   string|int $val
     *
     * @return  $this
     *
     * @since   2.0
     */
    public function set($key, $val)
    {
        $this->_data = Hash::merge([$key => $val], $this->_data);
        return $this;
    }

    /**
     * Setup data.
     *
     * @param   Entity|array $data
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function setData($data = [])
    {
        if ($data instanceof Entity) {
            $data = $data->getArray();
        }

        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * Get replacement text.
     *
     * @param   string $text
     *
     * @return  string mixed
     *
     * @since   2.0
     */
    public function text($text)
    {
        foreach ($this->_data as $macros => $value) {
            $macros = '{' . $macros . '}';

            if (!is_object($value) && !is_array($value)) {
                $text = preg_replace('#' . addcslashes($macros, '[]()') . '#ius', (string) $value, $text);
            }
        }

        return $text;
    }
}
