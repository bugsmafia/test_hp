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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Class HyperPcViewProduct_Folder
 *
 * @property    array $items
 * @property    array $ordering
 * @property    CMSObject $state
 * @property    Form $filterForm
 * @property    Pagination $pagination
 *
 * @since       2.0
 */
class HyperPcViewProduct_Folders extends ViewLegacy
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var   \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * All transition, which can be executed of one if the items
     *
     * @var  array
     */
    protected $transitions = [];

    /**
     * Is this view an Empty State
     *
     * @var   boolean
     * @since 4.0.0
     */
    private $isEmptyState = false;

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
        HTMLHelper::_('bootstrap.tooltip');

        $this->items   = $this->get('Items');
        $this->state   = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->pagination = $this->get('Pagination');

        foreach ($this->items as $item) {
            $this->ordering[$item->parent_id][] = $item->id;
        }

        $this->_addToolbar();

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
        ToolbarHelper::title(
            Text::_('COM_HYPERPC_VIEW_MOYSKLAD_TITLE') . ' ' . Text::_('JCATEGORIES'),
            'hyperpc'
        );
        ToolbarHelper::link($this->hyper['helper']['route']->url([
            'option'  => 'com_hyperpc',
            'view'    => 'positions',
        ]), 'COM_HYPERPC_PRODUCTS_AND_SERVICES', 'list');

        ToolbarHelper::preferences(HP_OPTION);

        if ($this->state->get('filter.published') == HP_STATUS_TRASHED) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'product_folders.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
    }

    /**
     * Load assets for display action.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();
    }
}
