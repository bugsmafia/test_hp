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

use HYPERPC\Joomla\Controller\ControllerAdmin;

/**
 * Class HyperPcControllerMoysklad_Stores
 *
 * @since 2.0
 */
class HyperPcControllerMoysklad_Stores extends ControllerAdmin
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_MOYSKLAD_STORES';

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  bool|JModelLegacy
     *
     * @since   2.0
     */
    public function getModel($name = 'Moysklad_store', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
