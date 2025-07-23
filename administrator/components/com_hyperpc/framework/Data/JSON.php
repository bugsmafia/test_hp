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

namespace HYPERPC\Data;

use JBZoo\Utils\Filter;
use Joomla\Filesystem\Path;
use JBZoo\Data\Data as AppJSON;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Class Data
 *
 * @package     HYPERPC\Data
 *
 * @since       2.0
 */
class JSON extends AppJSON
{

    /**
     * {@inheritdoc}
     *
     * @return  mixed|null
     */
    public function offsetGet($key)
    {
        if (!\property_exists($this, (string)$key)) {
            return null;
        }

        return parent::offsetGet($key);
    }

    /**
     * Get a value from the data given its key
     * @param string $key     The key used to fetch the data
     * @param mixed  $default The default value
     * @param mixed  $filter  Filter returned value
     * @return mixed
     */
    public function get(string $key, $default = null, $filter = null)
    {
        $result = $default;
        if ($this->has($key)) {
            $result = $this->offsetGet($key);
        }

        return self::filter($result, $filter);
    }

    /**
     * Utility Method to unserialize the given data
     *
     * @param string $string
     * @return mixed
     */
    protected function decode(string $string)
    {
        return \json_decode($string, true, 512, \JSON_BIGINT_AS_STRING);
    }

    /**
     * Does the real json encoding adding human readability. Supports automatic indenting with tabs
     *
     * @param mixed $data
     * @return string
     */
    protected function encode($data): string
    {
        return (string)\json_encode($data, \JSON_PRETTY_PRINT | \JSON_BIGINT_AS_STRING);
    }

    /**
     * Filter value before return.
     *
     * @param   mixed $value
     * @param   mixed $filter
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected static function filter($value, $filter)
    {
        if (null !== $filter) {
            if (preg_match('/^hp/', $filter)) {
                $filter = '_' . $filter;
                if (method_exists(self::class, $filter)) {
                    return self::{$filter}($value);
                }
            }

            $value = Filter::_($value, $filter);
        }

        return $value;
    }

    /**
     * Custom filter for setup array value int.
     *
     * @param   array $value
     *
     * @return  array
     *
     * @since   2.0
     */
    protected static function _hpArrayKeyInt($value)
    {
        $return = [];
        foreach ((array) $value as $key => $val) {
            $return[$key] = Filter::int($val);
        }

        return $return;
    }

    /**
     * Custom filter for image path.
     *
     * @param   string $value
     *
     * @return  string
     *
     * @since   2.0
     */
    protected static function _hpImagePath(string $value): string
    {
        $img = HTMLHelper::_('cleanImageURL', trim($value));

        return !empty($img->url) ? str_replace('\\', '/', Path::clean($img->url)) : '';
    }
}
