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

namespace HYPERPC\Helper\Traits;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;

/**
 * Trait PositionsFinder
 *
 * @package HYPERPC\Helper\Traits
 */
trait PartsFinder
{
    /**
     * Returns a query to retrieve all parts, divided by variants, from the database.
     *
     * The following table aliases are available in the query: positions, types, parts, options.
     * Additionally, you can sort the results by the following columns: list_price, in_stock.
     * 
     * @return  DatabaseQuery
     */
    protected function getAllPartsQuery(): DatabaseQuery
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Subquery to check stock for positions
        $positionBalanceExists = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName(HP_TABLE_MOYSKLAD_STORE_ITEMS, 'storeitems'))
            ->where($db->quoteName('storeitems.item_id') . ' = ' . $db->quoteName('positions.id'))
            ->where($db->quoteName('storeitems.balance') . ' > 0');

        // Subquery to check stock for options
        $optionBalanceExists = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName(HP_TABLE_MOYSKLAD_STORE_ITEMS, 'storeitems'))
            ->where($db->quoteName('storeitems.option_id') . ' = ' . $db->quoteName('options.id'))
            ->where($db->quoteName('storeitems.balance') . ' > 0');

        // Condition for positions (items)
        $positionCondition =
            '(' .
                $db->quoteName('positions.state') . ' = ' . HP_STATUS_PUBLISHED . ' OR ' .
                '(' .
                    $db->quoteName('positions.state') . ' = ' . HP_STATUS_ARCHIVED . ' AND EXISTS (' . $positionBalanceExists . ')' .
                ')' .
            ')';

        // Condition for options (variants)
        $optionCondition =
            '(' .
                $db->quoteName('options.id') . ' IS NULL OR ' .
                '(' .
                    $db->quoteName('options.state') . ' = ' . HP_STATUS_PUBLISHED . ' OR ' .
                    '(' .
                        $db->quoteName('options.state') . ' = ' . HP_STATUS_ARCHIVED . ' AND EXISTS (' . $optionBalanceExists . ')' .
                    ')' .
                ')' .
            ')';

        $priceSortField = 'COALESCE(' . $db->quoteName('options.list_price') . ', ' . $db->quoteName('positions.list_price') . ')';
        $inStockField =
            'CASE ' .
                // Check stock for a specific option if it exists
                'WHEN ' . $db->quoteName('options.id') . ' IS NOT NULL AND EXISTS (' . 
                    $db->getQuery(true)
                        ->select('1')
                        ->from($db->quoteName(HP_TABLE_MOYSKLAD_STORE_ITEMS, 'storeitems'))
                        ->where($db->quoteName('storeitems.option_id') . ' = ' . $db->quoteName('options.id'))
                        ->where($db->quoteName('storeitems.balance') . ' > 0') .
                    ') THEN 1 ' .
                // Check stock for the position if no options exist
                'WHEN ' . $db->quoteName('options.id') . ' IS NULL AND EXISTS (' . 
                    $db->getQuery(true)
                        ->select('1')
                        ->from($db->quoteName(HP_TABLE_MOYSKLAD_STORE_ITEMS, 'storeitems'))
                        ->where($db->quoteName('storeitems.item_id') . ' = ' . $db->quoteName('positions.id'))
                        ->where($db->quoteName('storeitems.balance') . ' > 0') .
                    ') THEN 1 ' .
                // If no stock is found, mark the item as out of stock
                'ELSE 0 ' .
            'END';

        // Main query
        $query = $db->getQuery(true);
        $query
            ->select([
                $db->quoteName('positions.id', 'id'),
                $db->quoteName('options.id', 'option_id'),
                "{$priceSortField} AS list_price",  // Virtual field for sorting by price
                "{$inStockField} AS in_stock"       // Virtual field for sorting by stock availability
            ])
            ->from($db->quoteName(HP_TABLE_POSITIONS, 'positions'))
            ->where($db->quoteName('types.alias') . ' = ' . $db->quote('part'))
            ->where($positionCondition)
            ->where($optionCondition)
            ->join('INNER', $db->quoteName(HP_TABLE_POSITION_TYPES, 'types'), $db->quoteName('types.id') . ' = ' . $db->quoteName('positions.type_id'))
            ->join('LEFT', $db->quoteName(HP_TABLE_MOYSKLAD_PARTS, 'parts'), $db->quoteName('parts.id') . ' = ' . $db->quoteName('positions.id'))
            ->join('LEFT', $db->quoteName(HP_TABLE_MOYSKLAD_VARIANTS, 'options'), $db->quoteName('options.part_id') . ' = ' . $db->quoteName('positions.id'));

        return $query;
    }
}
