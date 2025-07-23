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

use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTableUser_Codes
 *
 * @property    string $id
 * @property    string $code
 * @property    string $created_time
 * @property    string $update_time
 *
 * @since       2.0
 */
class HyperPcTableUser_Codes extends Table
{

    /**
     * HyperPcTable_User_Codes constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_USER_CODES, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   bool $updateNulls
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function store($updateNulls = false)
    {
        $date = Factory::getDate();

        if (!$this->id && !(int) $this->created_time) {
            $this->created_time = $date->toSql();
        }

        $this->update_time = $date->toSql();

        if (is_int($this->code)) {
            $this->code = base64_encode($this->code);
        }

        return parent::store($updateNulls);
    }
}
