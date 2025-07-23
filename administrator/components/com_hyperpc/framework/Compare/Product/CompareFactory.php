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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Compare\Product;

final class CompareFactory
{
    public static function createCompare($type = 'Product'): Compare
    {
        $className = 'HYPERPC\\Compare\\Product\\' . $type . 'Compare';

        if (!class_exists($className)) {
            throw new \Exception('Unknown compare type');
        }

        return new $className;
    }
}
