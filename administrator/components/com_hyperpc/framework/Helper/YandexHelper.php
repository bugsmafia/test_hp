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

use JBZoo\Utils\Filter;

/**
 * Class YandexHelper
 *
 * @package     HYPERPC\Helper
 *
 * @since version
 */
class YandexHelper extends AppHelper
{

    const TYPE_PART         = 'part';
    const TYPE_PART_OPTION  = 'option';
    const TYPE_PRODUCT      = 'product';

    const YA_CATEGORY_PART_VAL    = 0;
    const YA_CATEGORY_PRODUCT_CAL = 1000;

    /**
     * Get category id.
     *
     * @param   int     $value
     * @param   string  $type
     *
     * @return  float|int
     *
     * @since   2.0
     */
    public function getCategoryId($value, $type = self::TYPE_PART)
    {
        $value = Filter::int($value);
        if ($type === self::TYPE_PART) {
            return $value + self::YA_CATEGORY_PART_VAL;
        }

        return $value + self::YA_CATEGORY_PRODUCT_CAL;
    }
}
