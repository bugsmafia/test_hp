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

use HYPERPC\Data\JSON;
use Joomla\CMS\Version;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class HyperPcTableBanned_Ids
 *
 * @property    string  $banned_down
 * @property    string  $banned_up
 * @property    string  $id
 * @property    string  $ip
 *
 * @since       2.0
 */
class HyperPcTableBanned_Ids extends Table
{

    /**
     * HyperPcTableBanned_Ids constructor.
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

        if ((bool) $params->get('use_common_db')) {
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
                        HP_DB_COMMON . ' ' . HP_TABLE_BANNED_IDS
                    ),
                    E_USER_NOTICE
                );
            }
        }

        parent::__construct(HP_TABLE_BANNED_IDS, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Find ip in ban list.
     *
     * @param   null|string $ip
     * @param   array       $select
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function findByIp($ip = null, $select = ['a.id'])
    {
        if ($ip === null) {
            $ip = IpHelper::getIp();
        }

        $query = $this->_db->getQuery(true)
            ->select($select)
            ->from(
                $this->_db->qn($this->_tbl, 'a')
            )
            ->where([
                $this->_db->qn('a.ip') . ' = ' . $this->_db->q($ip)
            ]);

        return new JSON((array) $this->_db->setQuery($query)->loadObject());
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

        $this->banned_up = $date->toSql();

        if (!$this->banned_down) {
            $timeMark = 'now + ' . $this->hyper['params']->get('ban_time', 24, 'int') * (60 * 60) . ' sec';
            $banDate  = Factory::getDate($timeMark);

            $this->banned_down = $banDate->toSql();
        }

        $object = $this->findByIp($this->ip);
        if ($object->get('id')) {
            $this->id = $object->get('id');
        }

        return parent::store($updateNulls);
    }
}
