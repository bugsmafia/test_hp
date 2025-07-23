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
use Cake\Utility\Inflector;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Class HyperPcViewCompatibilities
 *
 * @property    array       $items
 * @property    CMSObject   $state
 * @property    Form        $filterForm
 * @property    Pagination  $pagination
 *
 * @since       2.0
 */
class HyperPcViewCompatibilities extends ViewLegacy
{

    /**
     * View display action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  Exception
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        HTMLHelper::_('bootstrap.tooltip');

        $this->_addToolbar();
        ToolbarHelper::preferences(HP_OPTION);

        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');

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
        ToolbarHelper::title($this->getViewTitle(), 'folder');
        ToolbarHelper::addNew(Inflector::singularize($this->getName()) . '.add');
        ToolbarHelper::publish($this->getName() . '.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish($this->getName(). '.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', $this->getName(). '.delete', 'JACTION_DELETE');
    }
}
