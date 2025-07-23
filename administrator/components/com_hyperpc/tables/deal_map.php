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
use HYPERPC\ORM\Entity\DealMapItem;

/**
 * Class HyperPcTableDeal_Map
 *
 * @since   2.0
 */
class HyperPcTableDeal_Map extends Table
{
    /**
     * HyperPcTableDeal_Map constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_DEAL_MAP, HP_TABLE_PRIMARY_KEY, $db);
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
        $this->setEntity('DealMapItem');
    }

    /**
     * Method to provide a shortcut to binding, checking and storing a Table instance to the database table.
     *
     * The method will check a row in once the data has been stored and if an ordering filter is present will attempt to reorder
     * the table rows based on the filter.  The ordering filter is an instance property name.  The rows that will be reordered
     * are those whose value matches the Table instance for the property specified.
     *
     * @param   array|object $src
     * @param   string $orderingFilter
     * @param   string $ignore
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function save($src, $orderingFilter = '', $ignore = '')
    {
        if (is_numeric($src->moysklad_order_uuid)) {
            $this->hyper->log(
                new \JBZoo\Data\JSON(debug_backtrace()),
                null,
                'deal_map/' . date('Y/m/d') . '/error_log.php'
            );
        }

        if ($src instanceof DealMapItem) {
            $src = $src->toArray();
        }

        // Attempt to bind the source to the instance.
        if (!$this->bind($src, $ignore)) {
            return false;
        }

        // Run any sanity checks on the instance and verify that it is ready for storage.
        if (!$this->check()) {
            return false;
        }

        // Attempt to store the properties to the database table.
        try {
            $this->store(true);
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return false;
        }

        // Attempt to check the row in, just in case it was checked out.
        if (!$this->checkin()) {
            return false;
        }

        // If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
        if ($orderingFilter) {
            $filterValue = $this->$orderingFilter;
            $this->reorder($orderingFilter ? $this->_db->quoteName($orderingFilter) . ' = ' . $this->_db->quote($filterValue) : '');
        }

        // Set the error to empty and return true.
        $this->setError('');

        return true;
    }
}
