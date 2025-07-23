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
use Joomla\CMS\Object\CMSObject;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Class HyperPcViewGames
 *
 * @property    array       $items
 * @property    CMSObject   $state
 * @property    Form        $filterForm
 * @property    Pagination  $pagination
 *
 * @since       2.0
 */
class HyperPcViewGames extends ViewLegacy
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
        $this->_addToolbar();
        ToolbarHelper::preferences(HP_OPTION);

        $this->items      = $this->get('Items');
        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');

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
        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_GAMES_TITLE'), 'puzzle');
        ToolbarHelper::addNew('game.add');
        ToolbarHelper::publish('games.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('games.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'games.delete', 'JACTION_DELETE');
    }
}
