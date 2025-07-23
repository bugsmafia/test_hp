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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use Joomla\CMS\Access\Exception\NotAllowed;

/**
 * Class HyperPcViewOrder
 *
 * @property    Form    $form
 * @property    Order   $order
 *
 * @since       2.0
 */
class HyperPcViewOrder extends ViewLegacy
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

        $this->form  = $this->get('Form');
        $this->order = $this->get('Item');

        if (!$this->order->id) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ORDER_NOT_FOUND'), 'error');
            $this->hyper['cms']->redirect($this->hyper['route']->build(['view' => 'orders']));
        }

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
        if ($this->hyper->isDevUser()) {
            ToolbarHelper::apply('order.apply');
        }

        if ($this->order->id) {
            ToolbarHelper::link($this->hyper['helper']['route']->url([
                'view' => 'order_log',
                'id'   => $this->order->id
            ]), Text::_('COM_HYPERPC_ORDER_LOG_VIEW_TITLE'), 'notification-2');
        }

        ToolbarHelper::cancel('order.cancel');
        ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_ORDER_EDIT_TITLE', $this->order->getName()), 'cart');

        if (empty($this->order->getUuid())) {
            ToolbarHelper::custom('order.send_to_moysklad', 'arrow-right', '', Text::_('COM_HYPERPC_SEND_TO_MOYSKLAD_ORDER'), false);
        }

        $this->hyper['input']->set('hidemainmenu', true);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        HTMLHelper::_('behavior.keepalive');
        HTMLHelper::_('bootstrap.tooltip');

        parent::_loadAssets();

        $this->hyper['helper']['assets']->widget('.hp-wrapper-form', 'HyperPC.AdminOrder');
    }
}
