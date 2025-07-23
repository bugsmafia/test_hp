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

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewWorkers
 *
 * @property    array       $items
 * @property    Pagination  $pagination
 *
 * @since       2.0
 */
class HyperPcViewWorkers extends ViewLegacy
{

    /**
     * Display view action.
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
        $userIsSuperAdmin = $this->getCurrentUser()->authorise('core.admin');
        if (!$userIsSuperAdmin) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $this->_addToolbar();
        ToolbarHelper::preferences(HP_OPTION);

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        parent::display($tpl);
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
        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_WORKERS_TITLE'), 'users');
        ToolbarHelper::addNew('worker.add');
        ToolbarHelper::publish('workers.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('workers.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'workers.delete', 'JACTION_DELETE');
    }
}
