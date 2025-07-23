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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

namespace HYPERPC\Data;

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * Trait ShortCode
 *
 * @package HYPERPC\Traits
 *
 * @since   2.0
 */
class ShortCode extends Data
{

    const ATTR_KEY = 'attrs';

    /**
     * Hold default data.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_default = [];

    /**
     * ShortCode constructor.
     *
     * @param   array  $data
     *
     * @since   2.0
     */
    public function __construct($data = [])
    {
        $parseData = null;
        if (array_key_exists(self::ATTR_KEY, $data)) {
            $parseData = $data[self::ATTR_KEY];
            unset($data[self::ATTR_KEY]);
        }

        $this->_default = (array) $data;

        parent::__construct($parseData);
    }

    /**
     * Utility Method to unserialize the given data.
     *
     * @param   string  $string
     *
     * @return  array|mixed
     *
     * @since   1.0
     */
    protected function decode($string)
    {
        $config = new JSON();
        $string = str_replace('"', '', $string);
        $attrs  = array_diff(explode(';', $string), array(''));

        foreach ($attrs as $attr) {
            list ($cKey, $cVal) = explode('=', $attr, 2);

            if (empty($cKey)) {
                continue;
            }

            $cKey = Inflector::variable(trim($cKey));
            $config->set($cKey, explode(',', $cVal));
        }

        return Hash::merge($this->_default, $config->getArrayCopy());
    }
}
