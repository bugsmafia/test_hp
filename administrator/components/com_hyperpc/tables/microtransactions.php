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
use HYPERPC\Money\Type\Money;

/**
 * Class HyperPcTableMicrotransactions
 *
 * @property    int     $id
 * @property    string  $purchase_key
 * @property    Money   $total
 * @property    string  $description
 * @property    string  $player
 * @property    bool    $paid
 * @property    int     $module_id
 * @property    bool    $activated
 * @property    int     $created_user_id
 * @property    string  $created_time
 *
 * @since       2.0
 */
class HyperPcTableMicrotransactions extends Table
{

    /**
     * HyperPcTableMicrotransactions constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_MICROTRANSACTIONS, HP_TABLE_PRIMARY_KEY, $db);
    }
}
