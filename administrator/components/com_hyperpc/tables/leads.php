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

use JBZoo\Data\JSON;
use Joomla\CMS\Factory;
use HYPERPC\ORM\Table\Table;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTableLeads
 *
 * @property    string $id
 * @property    string $modified
 * @property    string $created
 *
 * @since       2.0
 */
class HyperPcTableLeads extends Table
{

    /**
     * HyperPcTableLeads constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_LEADS, HP_TABLE_PRIMARY_KEY, $db);
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

        $this->modified = $date->toSql();

        if (!$this->id) {
            if (!(int) $this->created) {
                $this->created = $date->toSql();
            }
        }

        return parent::store($updateNulls);
    }


    /**
     * Overloaded bind function.
     *
     * @param   array|object    $array
     * @param   string          $ignore
     *
     * @return  bool
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        if (array_key_exists('history', $array)) {
            $review = new JSON($array['history']);
            $array['history'] = $review->write();
        } else {
            $array['history'] = '{}';
        }

        return parent::bind($array, $ignore);
    }
}
