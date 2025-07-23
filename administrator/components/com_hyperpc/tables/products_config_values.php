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

use HYPERPC\ORM\Table\Table;
use Joomla\Database\ParameterType;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTableProducts_Config_Values
 *
 * @since       2.0
 */
class HyperPcTableProducts_Config_Values extends Table
{

    /**
     * HyperPcTableProducts_Config_Values constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PRODUCTS_CONFIG_VALUES, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Write data.
     *
     * @param   array $data array of associative arrays
     */
    public function write(array $data)
    {
        $firstElement = current($data);

        $query = $this->_db
            ->getQuery(true)
            ->insert($this->_tbl)
            ->columns(\array_map(
                fn($column) => $this->_db->quoteName($column),
                \array_keys($firstElement)
            ))
            ->values(
                \array_map(
                    fn($row) => implode(
                        ', ',
                        \array_map(
                            fn($value) => $this->_db->quote($value),
                            $row
                        )
                    ),
                    $data
                )
            );

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch (\RuntimeException $e) {
            // log it
        }
    }

    /**
     * Delete all product data by product id.
     *
     * @param   int $productId
     */
    public function deleteAllProductData(int $productId)
    {
        $query = $this->_db
            ->getQuery(true)
            ->delete($this->_tbl)
            ->where(
                $this->_db->quoteName('product_id') . ' = :id'
            )
            ->bind(':id', $productId, ParameterType::INTEGER);

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch (\RuntimeException $e) {
            // log it
        }
    }

    /**
     * Delete product data by product id and stock id.
     * If stock id is omitted, only rows with stock_id IS NULL will be affected.
     *
     * @param   int $productId
     */
    public function deleteProductData(int $productId, int $stockId = null)
    {
        $query = $this->_db
            ->getQuery(true)
            ->delete($this->_tbl)
            ->where(
                $this->_db->quoteName('product_id') . ' = :id'
            )
            ->bind(':id', $productId, ParameterType::INTEGER);

        if (!empty($stockId)) {
            $query->where(
                $this->_db->quoteName('stock_id') . ' = :stockId'
            )
            ->bind(':stockId', $stockId, ParameterType::INTEGER);
        } else {
            $query->where(
                $this->_db->quoteName('stock_id') . ' IS NULL'
            );
        }

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch (\RuntimeException $e) {
            // log it
        }
    }

    /**
     * Deletes rows related to outdated stock products.
     *
     * @param array $actualStocks array of actual stock ids
     */
    public function clearOutdatedStocks(array $actualStocks)
    {
        $query = $this->_db
            ->getQuery(true)
            ->delete($this->_tbl)
            ->where($this->_db->quoteName('stock_id') . ' IS NOT NULL')
            ->whereNotIn($this->_db->quoteName('stock_id'), $actualStocks);

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        } catch (\RuntimeException $e) {
            // log it
        }
    }
}
