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

namespace HYPERPC\Money\Config;

use HYPERPC\App;
use JBZoo\SimpleTypes\Formatter;
use JBZoo\SimpleTypes\Config\Money as MoneyConfig;

/**
 * Class Money
 *
 * @package HyperPC\Helper\Config
 *
 * @since 2.0
 */
class Money extends MoneyConfig
{

    /**
     * Set default.
     *
     * @since 2.0
     */
    public function __construct()
    {
        $app = App::getInstance();
        $this->default = $app['params']->get('default_currency', 'rub');
    }

    /**
     * List of rules.
     *
     * @return array
     *
     * @since 2.0
     */
    public function getRules()
    {
        $this->defaultParams['num_decimals']    = 0;
        $this->defaultParams['round_type']      = Formatter::ROUND_CLASSIC;
        $this->defaultParams['decimal_sep']     = '.';
        $this->defaultParams['thousands_sep']   = ' ';
        $this->defaultParams['format_positive'] = '%v %s';
        $this->defaultParams['format_negative'] = '-%v %s';

        return [
            'eur' => [
                'symbol'   => '€',
                'iso_code' => 'EUR',
                'rate'     => 1
            ],

            'usd' => [
                'symbol'          => '$',
                'iso_code'        => 'USD',
                'format_positive' => '%s%v',
                'format_negative' => '-%s%v',
                'rate'            => 0.94
            ],

            'rub' => [
                'symbol'      => '₽',
                'iso_code'    => 'RUB',
                'decimal_sep' => ',',
                'rate'        => 0.01
            ],

            'uah' => [
                'symbol'      => 'грн.',
                'iso_code'    => 'UAH',
                'decimal_sep' => ',',
                'rate'        => 0.04
            ],

            'byn' => [
                'symbol'      => 'Br',
                'iso_code'    => 'BYN',
                'decimal_sep' => ',',
                'rate'        => 0.29
            ],

            'aed' => [
                'symbol'          => 'AED',
                'iso_code'        => 'AED',
                'thousands_sep'   => ',',
                'format_positive' => '%s %v',
                'format_negative' => '- %s %v',
                'rate'            => 0.26
            ],

            'kzt' => [
                'symbol'      => '₸',
                'iso_code'    => 'KZT',
                'decimal_sep' => ',',
                'rate'        => 0.0021
            ],

            '%' => [
                'symbol'          => '%',
                'iso_code'        => '%',
                'format_positive' => '%v%s',
                'format_negative' => '-%v%s'
            ],

            'percent' => [
                'symbol'          => '%',
                'iso_code'        => '%',
                'format_positive' => '%v%s',
                'format_negative' => '-%v%s'
            ]

        ];
    }
}
