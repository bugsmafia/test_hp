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

namespace HYPERPC\Render;

use JBZoo\Utils\Str;
use Cake\Utility\Hash;

/**
 * Class Order
 *
 * @package     HYPERPC\Render
 *
 * @since       2.0
 */
class Order extends Render
{

    /**
     * Hold entity object.
     *
     * @var \HYPERPC\Joomla\Model\Entity\Order
     *
     * @since 2.0
     */
    protected $_entity;

    /**
     * Render status history.
     *
     * @param   string $tpl
     * @param   array $data
     * @return  string
     *
     * @since   2.0
     */
    public function statusHistory($tpl = 'status_history', array $data = [])
    {
        if ($this->hyper['cms']->isClient('administrator')) {
            $tpl = 'administrator/' . $tpl;
        }

        $data = Hash::merge(['order' => $this->_entity], $data);

        return $this->renderLayout(Str::low($tpl), $data);
    }

    /**
     * Render credit status history.
     *
     * @param   string $tpl
     * @param   array $data
     * @return  string
     *
     * @since   2.0
     */
    public function creditStatusHistory($tpl = 'credit_status_history', array $data = [])
    {
        if ($this->hyper['cms']->isClient('administrator')) {
            $tpl = 'administrator/' . $tpl;
        }

        $data = Hash::merge(['order' => $this->_entity], $data);

        return $this->renderLayout(Str::low($tpl), $data);
    }
}
