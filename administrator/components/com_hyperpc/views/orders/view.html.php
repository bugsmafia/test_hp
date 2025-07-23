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
 * @author      Roman Evsyukov
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use HYPERPC\Joomla\Model\Entity\Order;
use Joomla\CMS\Access\Exception\NotAllowed;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewOrders
 *
 * @property    Order[]     $orders
 * @property    Form        $filterForm
 * @property    Pagination  $pagination
 *
 * @since       2.0
 */
class HyperPcViewOrders extends ViewLegacy
{
    /**
     * Active filter wrapper.
     *
     * @var bool
     *
     * @since       2.0
     */
    public $activeFilters = true;

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $canDo = ContentHelper::getActions(HP_OPTION);
        if (!$canDo->get('core.orders')) {
            throw new NotAllowed(Text::_('COM_HYPERPC_ACCESS_NO_RULES_ORDERS'), 403);
        }

        $this->_addToolbar();

        $this->orders     = $this->get('Items');
        $this->filterForm = $this->get('FilterForm');
        $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();
    }

    /**
     * Add toolbar for display action.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_ORDER_TITLE'), 'cart');

        ToolbarHelper::custom('orders.recount', 'refresh', '', Text::_('COM_HYPERPC_RECOUNT_ORDER'));
        ToolbarHelper::custom('orders.send_to_amo', 'arrow-right', '', Text::_('COM_HYPERPC_SEND_TO_AMO_ORDER'));
        ToolbarHelper::custom('orders.update_from_amo', 'arrow-left', '', Text::_('COM_HYPERPC_UPDATE_FROM_AMO_ORDER'));

        ToolbarHelper::preferences(HP_OPTION);
    }
}
