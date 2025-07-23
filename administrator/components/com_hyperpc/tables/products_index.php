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

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTableProducts_Index
 *
 * @property    string  $id
 * @property    string  $product_id
 * @property    string  $price_a
 * @prorerty    string  $price_b
 * @propery     string  $in_stock
 *
 * @since       2.0
 */
class HyperPcTableProducts_Index extends Table
{

    /**
     * HyperPcTableProducts_Index constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PRODUCTS_INDEX, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Delete all data by product id.
     *
     * @param   int         $id
     * @param   int|null    $stockId
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function deleteByProductId($id, $stockId = null)
    {
        $query = $this->_db
            ->getQuery(true)
            ->delete($this->_tbl);

        $conditions = [
            'product_id = ' . $this->_db->quote($id)
        ];

        if ($stockId !== null) {
            $conditions[] = 'in_stock = ' . $this->_db->quote($stockId);
        } else {
            $conditions[] = 'in_stock IS NULL';
        }

        $query->where($conditions);

        $this->_db->setQuery($query);
        return $this->_db->execute();
    }

    /**
     * Write data.
     *
     * @param   array   $rowDataList
     *
     * @return  void
     *
     * @since   2.0
     */
    public function write(array $rowDataList)
    {
        foreach ($rowDataList as $data) {
            if (is_array($data)) {
                $data = new JSON($data);
            }

            $values = [];
            $keys   = [];

            foreach ($data->getArrayCopy() as $key => $value) {
                $values[] = $this->_db->q($value);
                $keys[]   = $this->_db->qn($key);
            }

            $query = $this->_db
                ->getQuery(true)
                ->insert($this->_tbl)
                ->columns(
                    implode(', ', $keys)
                )
                ->values(
                    implode(', ', $values)
                );

            $this->_db->setQuery($query);
            $this->_db->execute();
        }
    }
}
