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
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Toolbar\Toolbar;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewStatuses
 *
 * @property    array       $items
 * @property    Form        $filterForm
 * @property    Pagination  $pagination
 *
 * @since       2.0
 */
class HyperPcViewStatuses extends ViewLegacy
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
        $this->_addToolbar();
        ToolbarHelper::preferences(HP_OPTION);

        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');

        $this->hyper['helper']['crm'];

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
        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_WORKERS_TITLE'), 'users');
        ToolbarHelper::addNew('status.add');
        ToolbarHelper::publish('statuses.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('statuses.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'statuses.delete', 'JACTION_DELETE');

        $statusUrl = $this->hyper['helper']['route']->url([
            'option' => 'com_hyperpc',
            'view'   => 'status',
        ]);

        Toolbar::getInstance('toolbar')->appendButton(
            'HyperLink',
            'link',
            'COM_HYPERPC_STATUS_VIEW_TITLE',
            $this->hyper['helper']['route']->getSiteSefUrl($statusUrl)
        );
    }
}
