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

namespace HYPERPC\Html\Render;

/**
 * Class Render
 *
 * @package     HYPERPC\Html\Render
 *
 * @since       2.0
 */
class Render
{

    /**
     * Build attributes.
     *
     * @param $attrs
     * @return string
     */
    public function buildAttrs(array $attrs = array())
    {
        $result = ' ';
        foreach ($attrs as $key => $param) {
            $param = (array) $param;
            if ($key == 'data') {
                $result .= $this->_buildDataAttrs($param);
                continue;
            }

            $value = implode(' ', $param);
            $value = $this->_cleanValue($value);

            if ($key !== 'data' && $attr = $this->_buildAttr($key, $value)) {
                $result .= $attr;
            }
        }

        return trim($result);
    }

    /**
     * Build attribute.
     *
     * @param   string $key
     * @param   string $val
     *
     * @return  null|string
     *
     * @since   2.0
     */
    protected function _buildAttr($key, $val)
    {
        $return = null;
        if (!empty($val) || $val == '0' || $key == 'value') {
            if (strpos($val, '"') !== false) {
                $return = ' ' . $key . '=\'' . $val . '\'';
            } else {
                $return = ' ' . $key . '="' . $val . '"';
            }
        }

        return $return;
    }

    /**
     * Build html data attributes.
     *
     * @param array $param
     *
     * @return string
     *
     * @since   2.0
     */
    protected function _buildDataAttrs(array $param = [])
    {
        $return = '';
        foreach ($param as $data => $val) {
            $dKey = 'data-' . trim($data);

            if (is_object($val)) {
                $val = (array) $val;
            }

            if (is_array($val)) {
                $val = json_encode($val);
            }

            $return .= $this->_buildAttr($dKey, $val);
        }

        return $return;
    }

    /**
     * Clear attribute value
     *
     * @param $value
     * @param bool|false $trim
     * @return string
     */
    protected function _cleanValue($value, $trim = false)
    {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return ($trim) ? trim($value) : $value;
    }
}
