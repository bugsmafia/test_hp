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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\Joomla\Model\ModelAdmin;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcModelOrder_log
 *
 * @since   2.0
 */
class HyperPcModelOrder_Log extends ModelAdmin
{

    /**
     * Get table object.
     *
     * @param   string  $type
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  \JTable
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTable($type = 'Order_Logs', $prefix = HP_TABLE_CLASS_PREFIX, $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }
}
