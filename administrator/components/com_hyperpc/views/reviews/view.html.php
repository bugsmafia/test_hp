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

use Joomla\CMS\Form\Form;
use Cake\Utility\Inflector;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewReviews
 *
 * @property    array       $items
 * @property    Pagination  $pagination
 * @property    Form        $filterForm
 *
 * @since       2.0
 */
class HyperPcViewReviews extends ViewLegacy
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
        $this->hyper['helper']['assets']
            ->jqueryRaty()
            ->addScript('
                 $(".jsRating").raty({
                    starType    : "i",
                    readOnly    : true
                });
            ');

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
        ToolbarHelper::title($this->getViewTitle(), 'list');
        ToolbarHelper::addNew(Inflector::singularize($this->getName()) . '.add');
        ToolbarHelper::publish($this->getName() . '.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish($this->getName() . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', $this->getName() . '.delete', 'JACTION_DELETE');
    }
}
