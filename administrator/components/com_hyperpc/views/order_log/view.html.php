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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\OrderLog;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use Joomla\CMS\Access\Exception\NotAllowed;

/**
 * Class HyperPcViewOrder_Log
 *
 * @property    Order           $order
 * @property    OrderLog[][]    $logs
 *
 * @since       2.0
 */
class HyperPcViewOrder_Log extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $canDo = ContentHelper::getActions(HP_OPTION);
        if (!$canDo->get('core.orders')) {
            throw new NotAllowed(Text::_('COM_HYPERPC_ACCESS_NO_RULES_ORDERS'), 403);
        }

        $this->order = $this->hyper['helper']['order']->findById($this->hyper['input']->get('id'));

        if (!$this->order->id) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'error');
            $this->hyper['cms']->redirect($this->hyper['route']->build(['view' => 'orders']));
        }

        $this->logs = Table::getInstance('Order_Logs')->findLogs($this->order->id);

        $this->_addToolbar();

        parent::display($tpl);
    }

    /**
     * Add toolbar for display action.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _addToolbar()
    {
        ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_ORDER_LOG_TITLE', $this->order->id), 'notification-2');

        ToolbarHelper::link($this->hyper['route']->build([
            'layout' => 'edit',
            'view'   => 'order',
            'id'     => $this->order->id
        ]), 'JTOOLBAR_CANCEL');

        $this->hyper['input']->set('hidemainmenu', true);
    }
}
