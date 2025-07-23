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
use HYPERPC\ORM\Entity\MoyskladStoreItem;

/**
 * Class HyperPcTableMoysklad_Store_Items
 *
 * @since   2.0
 */
class HyperPcTableMoysklad_Store_Items extends Table
{
    /**
    * HyperPcTableMoysklad_Store_Items constructor.
    *
    * @param   \JDatabaseDriver $db
    *
    * @throws  \Exception
    *
    * @since   2.0
    */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_MOYSKLAD_STORE_ITEMS, HP_TABLE_PRIMARY_KEY, $db);
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
     * Write data.
     *
     * @param   MoyskladStoreItem[] $items
     *
     * @return  void
     *
     * @throws  RuntimeException
     *
     * @since   2.0
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $values = [];
            $keys   = [];

            foreach ($item->toArray() as $key => $value) {
                $values[] = $this->_db->quote($value);
                $keys[]   = $this->_db->quoteName($key);
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
