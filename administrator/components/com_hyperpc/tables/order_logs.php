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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Version;
use Joomla\CMS\Factory;
use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\OrderLog;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class HyperPcTableOrder_Logs
 *
 * @property    string  $id
 * @property    string  $order_id
 * @property    string  $type
 * @property    string  $content
 * @property    string  $created_time
 *
 * @since       2.0
 */
class HyperPcTableOrder_Logs extends Table
{

    /**
     * HyperPcTableOrder_Logs constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        $params = ComponentHelper::getParams(HP_OPTION);

        if (Filter::bool($params->get('use_common_db'))) {
            $version = new Version();

            if ($version->getShortVersion() > 4) {
                $config = Factory::getApplication()->getConfig();
                $commonDbClass = new \Joomla\Database\DatabaseFactory;
            } else {
                $config = Factory::getConfig();
                $commonDbClass = new \JDatabaseFactory;
            }

            $options = [
                'port'      => HP_DB_PORT,
                'database'  => HP_DB_COMMON,
                'host'      => $config->get('host'),
                'user'      => $config->get('user'),
                'driver'    => $config->get('dbtype'),
                'password'  => $config->get('password'),
                'prefix'    => $config->get('dbprefix')
            ];

            $commonDb = $commonDbClass->getDriver('mysqli', $options);
            $commonDb->connect();
            if ($commonDb->connected()) {
                $db = $commonDb;
            } else {
                trigger_error(
                    Text::sprintf(
                        'JLIB_DATABASE_ERROR_CONNECT_DATABASE',
                        HP_DB_COMMON . ' ' . HP_TABLE_ORDER_LOGS
                    ),
                    E_USER_NOTICE
                );
            }
        }

        parent::__construct(HP_TABLE_ORDER_LOGS, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Find all order logs.
     *
     * @param   int $orderId
     *
     * @return  array
     *
     * @since   2.0
     */
    public function findLogs($orderId)
    {
        $logs    = [];
        $orderId = Filter::int($orderId);
        $query   = $this->_db->getQuery(true);

        $query
            ->select(['a.*'])
            ->from($this->_db->qn($this->getTableName(), 'a'))
            ->where([
                $this->_db->qn('a.order_id') . ' = ' . $this->_db->q($orderId)
            ]);

        $items = $this->_db->setQuery($query)->loadAssocList('id');

        $class = OrderLog::class;
        $list  = [];
        foreach ($items as $id => $item) {
            $list[$id] = new $class($item);
        }

        if (count($list) === 0) {
            return $logs;
        }

        /** @var OrderLog $log */
        foreach ($list as $log) {
            if (!isset($logs[$log->type][$log->id])) {
                $logs[$log->type][$log->id] = $log;
            }
        }

        return $logs;
    }

    /**
     * Get count of logs.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCount($conditions = [])
    {
        $query = $this->_db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($this->_db->qn($this->getTableName(), 'a'));

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return $this->_db->setQuery($query)->loadResult();
    }

    /**
     * Get count of logs.
     *
     * @param   Date $date
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function deleteByDate(Date $date)
    {
        $query = $this->_db->getQuery(true);

        $query
            ->delete($this->_db->qn($this->_tbl))
            ->where($this->_db->qn('created_time') . ' <= ' . $this->_db->q($date->toSql()));

        return $this->_db->setQuery($query)->execute();
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
        $this->setEntity('OrderLog');
    }
}
