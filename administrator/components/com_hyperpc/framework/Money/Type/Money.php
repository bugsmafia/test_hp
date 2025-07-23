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

namespace HYPERPC\Money\Type;

use HYPERPC\Money\Formatter;
use JBZoo\SimpleTypes\Parser;
use JBZoo\SimpleTypes\Exception;
use JBZoo\SimpleTypes\Config\Config;
use JBZoo\SimpleTypes\Type\Money as JBMoney;

/**
 * Class Money
 *
 * @package HYPERPC\Money\Type
 *
 * @since 2.0
 */
class Money extends JBMoney
{

    /**
     * Money constructor.
     *
     * @param null $value
     * @param Config|null $config
     *
     * @throws Exception
     *
     * @since 2.0
     */
    public function __construct($value = null, Config $config = null)
    {
        $this->_type = strtolower(str_replace(__NAMESPACE__ . '\\', '', get_class($this)));

        //  Get custom or global config.
        $config = $this->_getConfig($config);

        //  Debug flag (for logging).
        $this->_isDebug = (bool)$config->isDebug;

        //  Set default rule.
        $this->_default = trim(strtolower($config->default));
        !$this->_default && $this->error('Default rule cannot be empty!');

        //  Create formatter helper.
        $this->_formatter = new Formatter($config->getRules(), $config->defaultParams, $this->_type);

        //  Check that default rule.
        $rules = $this->_formatter->getList(true);
        if (!array_key_exists($this->_default, $rules)) {
            throw new Exception($this->_type . ': Default rule not found!');
        }

        //  Create parser helper.
        $this->_parser = new Parser($this->_default, $rules);

        //  Parse data.
        list($this->_value, $this->_rule) = $this->_parser->parse($value);

        //  Count unique id.
        self::$_counter++;
        $this->_uniqueId = self::$_counter;

        //  Success log.
        $this->log('Id=' . $this->_uniqueId . ' has just created; dump="' . $this->dump(false) . '"');
    }

    /**
     * Custom convert.
     *
     * @param   string $rule
     * @param   bool   $addToLog
     * @return  float
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _customConvert($rule, $addToLog = false)
    {
        if ($rule === 'percent') {
            return $this->_value;
        }

        return parent::_customConvert($rule, $addToLog);
    }
}
