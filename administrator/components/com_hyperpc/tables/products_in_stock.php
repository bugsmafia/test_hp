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

use HYPERPC\ORM\Table\Table;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTableProducts_In_Stock
 *
 * @property    string $id
 * @property    string $product_id
 * @property    string $price
 * @property    string $configuration
 * @property    string $params
 *
 * @since       2.0
 */
class HyperPcTableProducts_In_Stock extends Table
{

    /**
     * HyperPcTableProducts_In_Stock constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PRODUCTS_IN_STOCK, HP_TABLE_PRIMARY_KEY, $db);
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
        $this->setEntity('ProductInStock');
    }
}
