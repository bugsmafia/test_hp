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

namespace HYPERPC\Money;

use JBZoo\SimpleTypes\Formatter as JBFormatter;

/**
 * Class Formatter
 *
 * @package HYPERPC\Money
 *
 * @since 2.0
 */
class Formatter extends JBFormatter
{

    /**
     * Html output.
     *
     * @param array $current
     * @param array $orig
     * @param array $params
     * @return string
     *
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since 2.0
     */
    public function html($current, $orig, $params)
    {
        $data  = $this->_format($current['value'], $current['rule']);
        $rData = $this->get($current['rule']);

        if ($this->_type === 'money') {
            $result = str_replace(
                ['%v', '%s'],
                [
                    '<span class="simpleType-value" content="' . $current['value'] . '">' .
                        $data['value'] .
                    '</span>',
                    '<span content="' . $rData['iso_code']. '" class="simpleType-symbol">' .
                        $rData['symbol'] .
                    '</span>'
                ],
                $data['template']
            );

            return '<span ' . $this->htmlAttributes([
                    'class'                      => [
                        'simpleType',
                        'simpleType-block',
                        'simpleType-' . $this->_type
                    ],
                    'data-simpleType-id'         => $params['id'],
                    'data-simpleType-value'      => $current['value'],
                    'data-simpleType-rule'       => $current['rule'],
                    'data-simpleType-orig-value' => $orig['value'],
                    'data-simpleType-orig-rule'  => $orig['rule'],
                ]
            ) . '>' . $result . '</span>';
        }

        $result = str_replace(
            ['%v', '%s'],
            [
                '<span class="simpleType-value">' . $data['value'] . '</span>',
                '<span class="simpleType-symbol">' . $rData['symbol'] . '</span>'
            ],
            $data['template']
        );

        return '<span ' . $this->htmlAttributes([
                'class'                      => [
                    'simpleType',
                    'simpleType-block',
                    'simpleType-' . $this->_type
                ],
                'data-simpleType-id'         => $params['id'],
                'data-simpleType-value'      => $current['value'],
                'data-simpleType-rule'       => $current['rule'],
                'data-simpleType-orig-value' => $orig['value'],
                'data-simpleType-orig-rule'  => $orig['rule'],
            ]
        ) . '>' . $result . '</span>';
    }
}
