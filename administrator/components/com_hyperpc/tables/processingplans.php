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
 * Class HyperPcTableProcessingplans
 *
 * @since   2.0
 */
class HyperPcTableProcessingplans extends Table
{

    /**
     * HyperPcTableProcessingplans constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PROCESSINGPLANS, HP_TABLE_PRIMARY_KEY, $db);

        $this->_autoincrement = false;
    }

    /**
     * Overloaded bind function.
     *
     * @param   array|object  $array
     * @param   string        $ignore
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        if (array_key_exists('parts', $array)) {
            $array['parts'] = (new JSON($array['parts']))->write();
        }

        return parent::bind($array, $ignore);
    }
}
