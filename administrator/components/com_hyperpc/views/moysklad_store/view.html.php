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
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Exception\NotAllowed;
use HYPERPC\Joomla\Model\Entity\MoyskladStore;

/**
 * Class HyperPcViewMoysklad_Store
 *
 * @property    Form            $form
 * @property    MoyskladStore   $item
 *
 * @since       2.0
 */
class HyperPcViewMoysklad_Store extends ViewLegacy
{

    /**
     * View display action.
     *
     * @param   null|string $tpl
     *
     * @return  mixed
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

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        if (empty($this->item->id)) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_NO_MATCHING_PRODUCTS_RESULTS'), 'error');
            $this->hyper['cms']->redirect(
                $this->hyper['route']->build([
                    'view' => 'moysklad_stores'
                ])
            );
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
        ToolbarHelper::apply('moysklad_store.apply');
        ToolbarHelper::save('moysklad_store.save');
        ToolbarHelper::cancel('moysklad_store.cancel');

        ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_STORE_EDIT_TITLE', $this->item->name), 'puzzle');

        $this->hyper['input']->set('hidemainmenu', true);
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
        HTMLHelper::_('behavior.formvalidator');
        HTMLHelper::_('behavior.keepalive');

        $this->hyper['helper']['assets']->addScript('
             Joomla.submitbutton = function(task) {
                if (task == "moysklad_part.cancel" || document.formvalidator.isValid(document.getElementById("item-form"))) {
                    Joomla.submitform(task, document.getElementById("item-form"));
                }
            };
        ');

        parent::_loadAssets();
    }
}
