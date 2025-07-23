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

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Game;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Class HyperPcViewGame
 *
 * @property    Form    $form
 * @property    Game    $item
 *
 * @since       2.0
 */
class HyperPcViewGame extends ViewLegacy
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
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->_addToolbar();

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
        ToolbarHelper::apply('game.apply');
        ToolbarHelper::save('game.save');
        ToolbarHelper::save2new('game.save2new');
        ToolbarHelper::save2copy('game.save2copy');
        ToolbarHelper::cancel('game.cancel');

        if ($this->item->id > 0) {
            ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_GAME_EDIT_TITLE', $this->item->name), 'puzzle');
        } else {
            ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_GAME_ADD_NEW_TITLE'), 'puzzle');
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
        HTMLHelper::_('behavior.formvalidator');
        HTMLHelper::_('behavior.keepalive');

        $this->hyper['helper']['assets']->addScript('
            Joomla.submitbutton = function(task) {
                if (task == "game.cancel" || document.formvalidator.isValid(document.getElementById("item-form"))) {
                    Joomla.submitform(task, document.getElementById("item-form"));
                }
            };
        ');

        parent::_loadAssets();
    }
}
