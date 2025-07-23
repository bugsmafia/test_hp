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

namespace HYPERPC\ORM\Database\Type;

use JBZoo\Utils\Filter;

/**
 * Class BooleanType
 *
 * @package     HYPERPC\ORM\Database\Type
 *
 * @since       2.0
 */
class BooleanType implements TypeInterface
{

    /**
     * Convert string data into the database format.
     *
     * @param   $value
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function toDatabase($value)
    {
    }

    /**
     * Convert string values to PHP strings.
     *
     * @param   $value
     *
     * @return  int
     *
     * @since   2.0
     */
    public function toPHP($value)
    {
        return Filter::bool($value);
    }
}
