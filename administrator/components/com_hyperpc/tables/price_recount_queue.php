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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\ORM\Table\Table;

/**
 * Class HyperPcTablePrice_Recount_Queue
 *
 * @property    int $id
 * @property    int $part_id
 * @property    int $option_id
 *
 * @since       2.0
 */
class HyperPcTablePrice_Recount_Queue extends Table
{

    /**
     * HyperPcTablePrice_Recount_Queue constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PRICE_RECOUNT_QUEUE, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Initialize table.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setEntity('PriceRecountQueueItem');
    }

    /**
     * Clear all table data
     *
     * @return  void
     *
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function clear()
    {
        $this->_db->truncateTable($this->getTableName());
    }

    /**
     * Get product ids for recount price
     *
     * @return  int[]
     *
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function getProductIdsForRecount()
    {
        $db = $this->_db;

        $query = $db
            ->getQuery(true)
            ->select(['DISTINCT b.product_id'])
            ->from($db->qn($this->getTableName(), 'a'))
            ->join(
                'LEFT',
                $db->qn(HP_TABLE_PRODUCTS_CONFIG_VALUES, 'b') .
                ' ON (' . $db->qn('a.part_id') . ' = ' . $db->qn('b.part_id') .
                ' AND ' . $db->qn('a.option_id') . ' = ' . $db->qn('b.option_id') . ')'
            )
            ->where([
                $db->qn('b.context') . ' = ' . $db->q(HP_OPTION . '.position'),
                $db->qn('b.stock_id') . ' IS NULL'
            ]);

        $result = $db->setQuery($query)->loadRowList(0);

        return array_keys($result);
    }
}
