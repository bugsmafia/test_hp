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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\ORM\Table\Table;

/**
 * Class HyperPcTableMoysklad_Webhook
 *
 * @since   2.0
 */
class HyperPcTableMoysklad_Webhooks extends Table
{

    /**
     * HyperPcTableMoyskladWebhooks constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_MOYSKLAD_WEBHOOKS, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Delete record from table by uuid
     *
     * @param   $uuid
     *
     * @return  mixed
     *
     * @since 2.0
     */
    public function deleteByUuid($uuid)
    {
        $db = $this->hyper['db'];
        $query = $db->getQuery(true)
            ->delete($db->qn($this->_tbl))
            ->where($db->qn('uuid') . ' = ' . $db->q($uuid));

        return $db->setQuery($query)->execute();
    }
}
