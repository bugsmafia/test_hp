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
use JBZoo\Utils\Filter;
use Joomla\CMS\Version;
use Joomla\CMS\Date\Date;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class HyperPcTableForm_Counter
 *
 * @property    string  $context
 * @property    string  $count
 * @property    string  $created_time
 * @property    string  $id
 * @property    string  $token
 * @property    string  $updated_time
 * @property    string  $value
 */
class HyperPcTableForm_Counter extends Table
{
    /**
     * Hold high active flag.
     *
     * @var     bool
     */
    protected $_isHighActive = false;

    /**
     * Default max average send per second.
     *
     * @var     float
     */
    protected $_maxAverageSendPerSecond = 0.03;

    /**
     * Wait time action.
     *
     * @var     int
     */
    protected $_waitTime = 60;

    /**
     * HyperPcTableForm_Counter constructor.
     *
     * @param   JDatabaseDriver  $db
     *
     * @throws  \Exception
     * @throws  \RuntimeException
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
                        HP_DB_COMMON . ' ' . HP_TABLE_FORM_COUNTER
                    ),
                    E_USER_NOTICE
                );
            }
        }

        parent::__construct(HP_TABLE_FORM_COUNTER, HP_TABLE_PRIMARY_KEY, $db);

        $_maxAverageSendPerSecond = Filter::float($this->hyper['params']->get(
            'max_average_send_per_second',
            $this->_maxAverageSendPerSecond
        ));

        if ($_maxAverageSendPerSecond > 0) {
            $this->_maxAverageSendPerSecond = $_maxAverageSendPerSecond;
        }

        $this->_waitTime = $this->hyper['helper']['auth']->getWaitTime();
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $array   An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool
     *
     * @throws  \InvalidArgumentException
     */
    public function bind($array, $ignore = '')
    {
        $data = new JSON($array);

        if ($data->get('count') === null) {
            $data->set('count', 1);
        }

        $array = $data->getArrayCopy();

        return parent::bind($array, $ignore);
    }

    /**
     * Check request value
     *
     * @param   string $value
     * @param   string $context
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     */
    public function checkRequest(string $value, string $context): bool
    {
        if (empty(trim($value)) || empty(trim($context))) {
            throw new \InvalidArgumentException('Invalid argument in ' . __METHOD__, 500);
        }

        if ($context === HP_OPTION . '.auth_mobile') {
            $value = $this->hyper['helper']['string']->clearMobilePhone($value);
        }

        $nowTime = new Date();

        $waitTime = $this->getWaitTime();
        $timeFrom = Date::getInstance()->sub(\DateInterval::createFromDateString($waitTime . ' sec'));

        // Check by value
        $valueEntity = $this->_findEntity($value, $context, $timeFrom);
        if (!$valueEntity->get('id')) {
            $this->save([
                'value' => $value,
                'context' => $context
            ]);
        } else {
            $lastUpdate = new Date($valueEntity->updated_time);
            $waitTime = $lastUpdate->add(\DateInterval::createFromDateString($waitTime . ' sec'));

            if ($waitTime->getTimestamp() > $nowTime->getTimestamp()) {
                $waitSeconds = $waitTime->getTimestamp() - $nowTime->getTimestamp();

                $this->setError(Text::plural('COM_HYPERPC_ERROR_AUTH_TIME_OUT', $waitSeconds));
                return false;
            }
        }

        // Check by ip
        $userIp = IpHelper::getIp();
        $ipContext = $context . '_ip';
        $ipEntity = $this->_findEntity($userIp, $ipContext, $timeFrom);
        if (!$ipEntity->get('id')) {
            $this->reset();
            $this->id = null;
            $this->save([
                'value' => $userIp,
                'context' => $ipContext
            ]);
        } else {
            $createdTime = new Date($ipEntity->created_time);
            $updateTime = new Date($ipEntity->updated_time);

            $watchSeconds = ($updateTime->getTimestamp() - $createdTime->getTimestamp());
            if ($watchSeconds === 0) {
                $watchSeconds = 1;
            }

            $avgPerSecond = $ipEntity->count / $watchSeconds;
            if ($avgPerSecond > $this->_maxAverageSendPerSecond) {
                if ($ipEntity->count >= 4 && $ipEntity->count <= 6) { // Set ban notice.
                    $this->setError(Text::_('COM_HYPERPC_ERROR_WARNING_IP_LIMIT_BANNED'));
                } elseif ($ipEntity->count > 6) { // Ban ip
                    Table::getInstance('Banned_Ids')->save(['ip' => $userIp]);
                    $this->setError(Text::_('COM_HYPERPC_ERROR_IP_LIMIT_BANNED'));
                    return false;
                }
            }

            $ipEntity->count++;
            $this->save($ipEntity->getProperties());
        }

        if ($valueEntity->get('id')) {
            $valueEntity->count++;
            $this->save($valueEntity->getProperties());
        }

        return true;
    }

    /**
     * Check high active.
     *
     * @param   int    $checkTime seconds count to checking
     * @param   array  $context
     *
     * @return  void
     */
    public function checkHighActive($checkTime = 600, $context = [])
    {
        $checkTime = (int) $checkTime;
        if ($checkTime === 0) {
            return;
        }

        $nowTime  = new Date();
        $timeFrom = Date::getInstance()->sub(\DateInterval::createFromDateString($checkTime . ' sec'));

        if (count($context) === 0) {
            $context = [
                HP_OPTION . '.auth_email',
                HP_OPTION . '.auth_mobile',
            ];
        }

        $contextValue = [];
        foreach ($context as $item) {
            $contextValue[] = $this->_db->q($item);
        }

        $countList = $this->hyper['helper']['formCounter']->findAll([
            'select' => [
                'a.id',
                'a.count'
            ],
            'conditions' => [
                $this->_db->qn('a.updated_time') .
                'BETWEEN ' .
                $this->_db->q($timeFrom->toSql()) .
                ' AND ' .
                $this->_db->q($nowTime->toSql()),
                $this->_db->qn('a.context') . ' IN (' . implode(', ', $contextValue) . ')',
            ]
        ]);

        $totalCount = 0;
        if (count($countList) > 0) {
            foreach ($countList as $item) {
                $item = new JSON($item);
                $totalCount += $item->get('count', 1, 'int');
            }
        }

        if ($totalCount > 0) {
            $result = $totalCount / $checkTime;
            $maxInTotalPerSecond = 0.03; // About 18 sends in 10 minutes
            if ($result > $maxInTotalPerSecond) {
                $this->setIsHighActive(true);
            }
        }
    }

    /**
     * Get wait time.
     *
     * @return  int
     */
    public function getWaitTime()
    {
        return $this->_waitTime;
    }

    /**
     * Get is high active flag.
     *
     * @return  bool
     */
    public function isHighActive()
    {
        return $this->_isHighActive;
    }

    /**
     * Setup is high active flag.
     *
     * @param   bool    $flag
     *
     * @return  $this
     */
    public function setIsHighActive($flag)
    {
        $this->_isHighActive = Filter::bool($flag);
        return $this;
    }

    /**
     * Set wait time.
     *
     * @param   string  $time
     *
     * @return  $this
     */
    public function setWaitTime($time)
    {
        $this->_waitTime = Filter::int($time);
        return $this;
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   bool $updateNulls
     *
     * @return  bool
     */
    public function store($updateNulls = false)
    {
        $date = Factory::getDate();

        if (!$this->id) {
            if (!(int) $this->created_time) {
                $this->created_time = $date->toSql();
            }
        }

        $this->updated_time = $date->toSql();

        return parent::store($updateNulls);
    }

    /**
     * Find entity
     *
     * @param   string $value
     * @param   string $context
     * @param   Date   $timeFrom
     *
     * @return  CMSObject
     *
     * @throws  \Exception
     */
    protected function _findEntity(string $value, string $context, Date $timeFrom): CMSObject
    {
        $nowTime = new Date();

        $entity = $this->hyper['helper']['formCounter']->findByValue($value, [
            'conditions' => [
                $this->_db->qn('a.updated_time') .
                'BETWEEN ' .
                $this->_db->q($timeFrom->toSql()) .
                ' AND ' .
                $this->_db->q($nowTime->toSql()),
                $this->_db->qn('a.context') . ' = ' . $this->_db->q($context),
            ],
            'order' => $this->_db->qn('a.updated_time') . ' DESC'
        ]);

        return $entity;
    }
}
