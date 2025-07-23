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
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Class HyperPcViewCompatibility
 *
 * @property    string      $item
 * @property    Form        $form
 *
 * @since       2.0
 */
class HyperPcViewCompatibility extends ViewLegacy
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
     * @throws  Exception
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        HTMLHelper::_('behavior.keepalive');
        HTMLHelper::_('behavior.formvalidator');

        $this->hyper['helper']['assets']->addScript('
            Joomla.submitbutton = function(task) {
                if (task == "' . $this->getName() . '.cancel" || document.formvalidator.isValid(document.getElementById("item-form"))) {
                    Joomla.submitform(task, document.getElementById("item-form"));
                }
            };
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
        ToolbarHelper::title($this->getViewTitle());

        ToolbarHelper::apply($this->getName() . '.apply');
        ToolbarHelper::save($this->getName() . '.save');
        ToolbarHelper::save2new($this->getName() . '.save2new');
        ToolbarHelper::save2copy($this->getName() . '.save2copy');
        ToolbarHelper::cancel($this->getName() . '.cancel');

        $this->hyper['input']->set('hidemainmenu', true);
    }
}
