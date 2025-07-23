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

use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;
use HYPERPC\Joomla\Model\Entity\PromoCode;
use Joomla\CMS\Access\Exception\NotAllowed;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewPromo_Code
 *
 * @property    Form $form
 * @property    PromoCode $item
 *
 * @since       2.0
 */
class HyperPcViewPromo_Code extends ViewLegacy
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
        $userIsSuperAdmin = $this->getCurrentUser()->authorise('core.admin');
        if (!$userIsSuperAdmin) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

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
        HTMLHelper::_('bootstrap.tooltip');
        $this->hyper['helper']['assets']
            ->js('js:widget/fields/promo-codes.js')
            ->widget('#adminForm', 'HyperPC.FieldPromoCodes');
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
        ToolbarHelper::cancel($this->_name     . '.cancel');

        if ($this->item->id !== 0) {
            ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_PROMO_CODE_EDIT_TITLE', $this->item->code), 'basket');
        } else {
            ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_PROMO_CODE_ADD_NEW_TITLE'), 'basket');
        }

        $this->hyper['input']->set('hidemainmenu', true);
    }
}
