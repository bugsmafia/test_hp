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

use JBZoo\Data\JSON;
use JBZoo\Data\Data;
use Joomla\CMS\Version;
use Joomla\CMS\Factory;
use JBZoo\Utils\Filter;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Class HyperPcTableOrders
 *
 * @property    string $id
 * @property    string $cid
 * @property    string $total
 * @property    string $parts
 * @property    string $products
 * @property    string $positions
 * @property    string $context
 * @property    string $form
 * @property    string $delivery_type
 * @property    string $payment_type
 * @property    string $status
 * @property    string $status_history
 * @property    string $elements
 * @property    string $promo_code
 * @property    string $created_time
 * @property    string $created_user_id
 * @property    string $params
 * @property    string $modified_time
 * @property    string $modified_user_id
 * @property    string $worker_id
 *
 * @since       2.0
 */
class HyperPcTableOrders extends Table
{

    /**
     * HyperPcTableOrders constructor.
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
                        HP_DB_COMMON . ' ' . HP_TABLE_ORDERS
                    ),
                    E_USER_NOTICE
                );
            }
        }

        parent::__construct(HP_TABLE_ORDERS, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Overloaded bind function.
     *
     * @param   array|object    $array
     * @param   string          $ignore
     *
     * @return  bool
     *
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        //  TODO for next dev, setup data from $bindData
        $bindData = new JSON($array);

        if (array_key_exists('elements', $array)) {
            $array['elements'] = (new JSON($array['elements']))->write();
        }

        if ($this->hyper['cms']->isClient('site')) {
            if (!$bindData->get('context')) {
                $array['context'] = $this->hyper->getContext();
                $bindData->set('context', $this->hyper->getContext());
            }

            $fields         = new Data($this->hyper['params']->get('cart'));
            $elementKeyType = (Filter::int($array['form']) === HP_ORDER_FORM) ? 'individual' : 'order';
            $elementNames   = array_keys((array) $fields->get($elementKeyType));

            //  Remove empty cart element fields.
            if (count($elementNames)) {
                foreach ($elementNames as $elementName) {
                    if (array_key_exists($elementName, $array['elements'])) {
                        unset($array['elements'][$elementName]);
                    }
                }
            }

            if (array_key_exists('positions', $array)) {
                $array['positions'] = (new JSON($array['positions']))->write();
            } else {
                $array['positions'] = '{}';
            }
        }

        if (array_key_exists('parts', $array)) {
            $parts = new JSON($array['parts']);
            foreach ($parts as $id => $data) {
                $data = new JSON($data);
                $parts[$id] = $data;
            }

            $array['parts'] = (new JSON($parts))->write();
        } else {
            $array['parts'] = '{}';
        }

        if (array_key_exists('products', $array)) {
            $savedProducts = [];
            $products = new JSON($array['products']);

            foreach ($products as $qid => $data) {
                $data = new JSON($data);

                if (!empty($data->get('id'))) {
                    $data->set('id', $data->get('id'));
                }

                if (!empty($data->get('saved_configuration', null))) {
                    $data->set('saved_configuration', $data->get('saved_configuration', null));
                }

                $savedProducts[$qid] = $data;
            }

            $array['products'] = (new JSON($savedProducts))->write();
        } else {
            $array['products'] = '{}';
        }

        if (array_key_exists('status_history', $array)) {
            $array['status_history'] = (new JSON($array['status_history']))->write();
        }

        return parent::bind($array, $ignore);
    }
}
