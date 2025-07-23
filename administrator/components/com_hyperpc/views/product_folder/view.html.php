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
use Joomla\CMS\Toolbar\Toolbar;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use Joomla\CMS\Uri\Uri;

/**
 * Class HyperPcViewProduct_Folder
 *
 * @property    Form            $form
 * @property    ProductFolder   $item
 *
 * @since       2.0
 */
class HyperPcViewProduct_Folder extends ViewLegacy
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
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_ERROR_GROUP_NOT_FOUND'), 'error');
            $this->hyper['cms']->redirect(
                $this->hyper['route']->build(['view' => 'product_folders'])
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
        ToolbarHelper::apply('product_folder.apply');
        ToolbarHelper::save('product_folder.save');

        $url = $this->hyper['route']->buildSite($this->item->getViewUrl());
        if (strripos($url, '/component/hyperpc')) {
            $path = trim(str_replace('/administrator', '', $this->item->getViewUrl()), '/');
            $url = Uri::root() . $path;
        }

        if ($this->item->id !== 0) {
            Toolbar::getInstance('toolbar')->appendButton(
                'HyperLink',
                'eye',
                'COM_HYPERPC_GO_TO_SITE',
                $url
            );
        }

        ToolbarHelper::cancel('product_folder.cancel');

        ToolbarHelper::title(Text::sprintf('COM_HYPERPC_VIEW_CATEGORIES_EDIT_TITLE', $this->item->title), 'folder');

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
                if (task == "product_folder.cancel" || document.formvalidator.isValid(document.getElementById("item-form"))) {
                    Joomla.submitform(task, document.getElementById("item-form"));
                }
            };
        ');

        parent::_loadAssets();
    }
}
