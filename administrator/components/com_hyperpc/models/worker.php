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
 * Class HyperPcModelWorker
 *
 * @since   2.0
 */
class HyperPcModelWorker extends ModelAdmin
{

    /**
     * Get global fields for form render.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGlobalFields()
    {
        return ['published', 'last_form_turn', 'last_order_turn'];
    }
}
