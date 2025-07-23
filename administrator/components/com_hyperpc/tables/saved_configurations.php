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
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class HyperPcTableSaved_Configurations
 *
 * @property    string $id
 * @property    string $product
 * @property    string $parts
 * @property    string $options
 * @property    string $created_time
 * @property    string $modified_time
 *
 * @since       2.0
 */
class HyperPcTableSaved_Configurations extends Table
{

    /**
     * HyperPcTableSaved_Configurations constructor.
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
                        HP_DB_COMMON . ' ' . HP_TABLE_SAVED_CONFIGURATIONS
                    ),
                    E_USER_NOTICE
                );
            }
        }

        parent::__construct(HP_TABLE_SAVED_CONFIGURATIONS, HP_TABLE_PRIMARY_KEY, $db);
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
        $this->setEntity('SaveConfiguration');
    }

    /**
     * Overloaded bind function.
     *
     * @param   array|object $array
     * @param   string $ignore
     * @return  bool
     *
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        foreach (['product', 'parts'] as $fieldName) {
            if (key_exists($fieldName, $array)) {
                $content = new JSON($array[$fieldName]);
                $array[$fieldName] = $content->write();
            } else {
                $array[$fieldName] = '{}';
            }
        }

        return parent::bind($array, $ignore);
    }
}
