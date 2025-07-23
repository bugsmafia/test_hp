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
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class HyperPcViewMoysklad_Variant
 *
 * @property    Form            $form
 * @property    MoyskladVariant $item
 *
 * @since       2.0
 */
class HyperPcViewMoysklad_Variant extends ViewLegacy
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
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        if (empty($this->item->id)) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_NO_MATCHING_PRODUCTS_RESULTS'), 'error');
            $this->hyper['cms']->redirect(
                $this->hyper['route']->build([
                    'id'                => $this->hyper['input']->get('part_id', 0),
                    'view'              => 'moysklad_part',
                    'product_folder_id' => $this->hyper['input']->get('product_folder_id', 1)
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
        ToolbarHelper::apply('moysklad_variant.apply');
        ToolbarHelper::save('moysklad_variant.save');
        ToolbarHelper::cancel('moysklad_variant.cancel');

        ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_OPTIONS_EDIT_TITLE', $this->item->name, $this->item->sale_price->text()), 'puzzle');

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
                if (task == "moysklad_variant.cancel" || document.formvalidator.isValid(document.getElementById("item-form"))) {
                    Joomla.submitform(task, document.getElementById("item-form"));
                }
            };
        ');

        parent::_loadAssets();
    }
}
