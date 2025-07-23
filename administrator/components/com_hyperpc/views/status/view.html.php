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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Status;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewStatus
 *
 * @property    Form    $form
 * @property    Status  $item
 *
 * @since       2.0
 */
class HyperPcViewStatus extends ViewLegacy
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
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->_addToolbar();

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
        ToolbarHelper::apply($this->_name      . '.apply');
        ToolbarHelper::save($this->_name       . '.save');
        ToolbarHelper::save2new($this->_name   . '.save2new');
        ToolbarHelper::save2copy($this->_name  . '.save2copy');
        ToolbarHelper::cancel('status.cancel');

        if ($this->item->id !== 0) {
            ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_STATUS_EDIT_TITLE', $this->item->name), 'lamp');
        } else {
            ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_STATUS_ADD_NEW_TITLE'), 'lamp');
        }

        $this->hyper['input']->set('hidemainmenu', true);
    }
}
