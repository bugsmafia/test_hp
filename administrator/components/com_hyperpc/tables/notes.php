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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\ORM\Table\Table;

/**
 * Class HyperPcTableNotes
 *
 * @property    int $id
 * @property    string $item_id
 * @property    string $context
 * @property    string $note
 * @property    string $created_time
 * @property    int $created_user_id
 * @property    string $modified_time
 * @property    int $modified_user_id
 *
 * @since       2.0
 */
class HyperPcTableNotes extends Table
{

    /**
     * HyperPcTableNotes constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_NOTES, HP_TABLE_PRIMARY_KEY, $db);
    }
}
