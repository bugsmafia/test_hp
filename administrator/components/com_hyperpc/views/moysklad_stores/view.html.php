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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

/**
 * Class HyperPcViewMoysklad_Stores
 *
 * @property    array       $items
 * @property    CMSObject   $state
 * @property    array       $paths
 * @property    array       $folders
 * @property    int         $folderId
 * @property    array       $ordering
 * @property    Form        $filterForm
 * @property    Pagination  $pagination
 * @property    array       $sidebarItems
 *
 * @since       2.0
 */
class HyperPcViewMoysklad_Stores extends ViewLegacy
{

    /**
     * View display action.
     *
     * @param   null|string $tpl
     * @return  mixed
     *
     * @throws  Exception
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $userIsSuperAdmin = $this->getCurrentUser()->authorise('core.admin');
        if (!$userIsSuperAdmin) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        HTMLHelper::_('bootstrap.tooltip');

        $this->_addToolbar();

        $this->items         = $this->get('Items');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->pagination    = $this->get('Pagination');
        $this->activeFilters = $this->get('ActiveFilters');

        foreach ($this->items as $item) {
            $this->ordering[$item->parent_id][] = $item->id;
        }

        return parent::display($tpl);
    }

    /**
     * Add toolbar for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_MOYSKLAD_STORES_TITLE'), 'puzzle');

        ToolbarHelper::publish('moysklad_stores.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('moysklad_stores.unpublish', 'JTOOLBAR_UNPUBLISH', true);

        ToolbarHelper::preferences(HP_OPTION);
    }
}
