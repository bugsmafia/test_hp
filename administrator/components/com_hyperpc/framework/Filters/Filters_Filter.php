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

namespace HYPERPC\Filters;

abstract class Filter
{
    /**
     * Get list of filtered entity keys.
     * 
     * @return  string[]
     */
    abstract public function getItems(): array;

    /**
     * Get filter state for frontend visualization.
     *
     * @todo change return type
     */
    abstract public function getState(): array;

    /**
     * There are any filters.
     */
    abstract public function hasFilters(): bool;

    /**
     * There are any items.
     */
    abstract public function hasItems(): bool;
}
