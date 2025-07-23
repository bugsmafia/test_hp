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

use HYPERPC\Joomla\Controller\ControllerAdmin;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcControllerOrder_Logs
 *
 * @since   2.0
 */
class HyperPcControllerOrder_Logs extends ControllerAdmin
{

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  bool|HyperPcModelPromo_Code
     *
     * @since   2.0
     */
    public function getModel($name = 'Order_Log', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
