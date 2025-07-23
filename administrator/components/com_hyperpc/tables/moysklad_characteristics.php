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

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;

/**
 * Class HyperPcTableMoysklad_Characteristics
 *
 * @property    string $uuid
 * @property    string $name
 * @property    JSON $params
 */
class HyperPcTableMoysklad_Characteristics extends Table
{
    /**
     * HyperPcTableMoysklad_Characteristics constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_MOYSKLAD_CHARACTERISTICS, 'uuid', $db);

        $this->_autoincrement = false;
    }
}
