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

namespace HYPERPC\Helper;

use HYPERPC\Joomla\Factory;
use HYPERPC\Money\Type\Money;
use JBZoo\SimpleTypes\Config\Config;
use HYPERPC\Money\Config\Money as HyperMoneyConfig;

/**
 * Class MoneyHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoneyHelper extends AppHelper
{
    /**
     * @var HyperMoneyConfig
     */
    public static $defaultConfig;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        if (!isset($defaultConfig)) {
            $config = new HyperMoneyConfig();
            Config::registerDefault('money', $config);

            $ruleData = $config->getRules()[$config->default];
            Factory::getDocument()->addScriptOptions('moneyConfig', array_merge($config->defaultParams, $ruleData));

            self::$defaultConfig = $config;
        }
    }

    /**
     * Get money type object.
     *
     * @param   null|string $value
     * @param   Config $config
     * @return  Money
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function get($value = null, Config $config = null)
    {
        return new Money($value, $config);
    }

    /**
     * Get currency iso code.
     *
     * @param   ?Money $price
     *
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getCurrencyIsoCode(?Money $price = null)
    {
        if ($price === null) {
            $price = $this->get(0);
        }

        return $price->getRuleData($price->getRule())['iso_code'];
    }

    /**
     * Get currency symbol.
     *
     * @param   Money $price
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getCurrencySymbol(Money $price)
    {
        return $price->getRuleData($price->getRule())['symbol'];
    }

    /**
     * Get VAT excluded
     *
     * @param Money|int $amount
     *
     * @return Money
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws \InvalidArgumentException
     *
     * @since 2.0
     */
    public function getVat($amount)
    {
        if (!($amount instanceof Money) && !is_int($amount)) {
            throw new \InvalidArgumentException('First parameter must be instance of HYPERPC\Money\Type\Money or int');
        }

        if (is_int($amount)) {
            $amount = $this->get($amount);
        }

        $vat = $this->hyper['params']->get('vat', 20, 'int');
        $vatExcluded = ($amount->val() / (100 + $vat)) * $vat;

        return $this->get($vatExcluded);
    }
}
