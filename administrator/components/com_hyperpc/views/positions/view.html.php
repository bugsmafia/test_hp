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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Layout\LayoutHelper;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Pagination\Pagination;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HyperPcModelProduct_Folders as ModelProductFolders;

/**
 * Class HyperPcViewPositions
 *
 * @property    Position[]      $items
 * @property    CMSObject       $state
 * @property    array           $paths
 * @property    ProductFolder[] $folders
 * @property    mixed           $sidebar
 * @property    int             $folderId
 * @property    array           $ordering
 * @property    Form            $filterForm
 * @property    Pagination      $pagination
 * @property    array           $sidebarItems
 *
 * @since       2.0
 */
class HyperPcViewPositions extends ViewLegacy
{

    /**
     * View display action.
     *
     * @param   null|string $tpl
     * @return  mixed
     *
     * @throws  Exception
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        HTMLHelper::_('bootstrap.tooltip');

        /** @var ModelProductFolders $productFolderModel */
        $productFolderModel = ModelList::getInstance('Product_folders');
        $this->folderId     = $this->hyper['input']->get('folder_id', 1, 'int');

        $this->items        = $this->get('Items');
        $this->state        = $this->get('State');
        $this->filterForm   = $this->get('FilterForm');
        $this->pagination   = $this->get('Pagination');
        $this->paths        = $productFolderModel->getPath($this->folderId);
        $this->folders      = $productFolderModel->getByParent($this->folderId);
        $this->sidebarItems = $productFolderModel->getFolders(false);

        foreach ($this->sidebarItems as $folder) {
            $this->ordering[$folder->parent_id][] = $folder->id;
        }

        $this->_categorySidebarNav();

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
        ToolbarHelper::title(
            Text::_('COM_HYPERPC_SIDEBAR_POSITIONS'),
            'hyperpc'
        );

        ToolbarHelper::link($this->hyper['helper']['route']->url([
            'view'  => 'product_folders'
        ]), 'JCATEGORIES', 'list');

        ToolbarHelper::divider();
        ToolbarHelper::link($this->hyper['helper']['route']->url([
            'option'  => 'com_fields',
            'view'    => 'fields',
            'context' => HP_OPTION . '.position',
        ]), 'JGLOBAL_FIELDS', 'list');

        ToolbarHelper::link($this->hyper['helper']['route']->url([
            'option'  => 'com_fields',
            'view'    => 'groups',
            'context' => HP_OPTION . '.position',
        ]), 'JGLOBAL_FIELD_GROUPS', 'grid');

        ToolbarHelper::preferences(HP_OPTION);

        if ($this->state->get('filter.published') == HP_STATUS_TRASHED)
        {
            ToolbarHelper::divider();
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'positions.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
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
        parent::_loadAssets();
        $this->hyper['helper']['assets']->widget('#content', 'HyperPC.AdminPositions');
    }

    /**
     * Tree part group navigation.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _categorySidebarNav()
    {
        $layout = $this->hyper['input']->get('layout');

        /** @var ProductFolder $folder */
        foreach ($this->sidebarItems as $i => $productFolder) {
            if ($productFolder->alias === 'root') {
                $productFolder->set('title', Text::_('COM_HYPERPC_ROOT_CATEGORY_TITLE'));
            }

            //  Get the parents of item for sorting.
            if ($productFolder->level > 1) {
                $_currentParentId = $productFolder->parent_id;
                $parentsStr = ' ' . $_currentParentId;
                for ($i2 = 1; $i2 < $productFolder->level; $i2++) {
                    foreach ($this->ordering as $k => $v) {
                        $v = implode('-', $v);
                        $v = '-' . $v . '-';
                        if (strpos($v, '-' . $_currentParentId . '-') !== false) {
                            $parentsStr .= ' ' . $k;
                            $_currentParentId = $k;
                            break;
                        }
                    }
                }
            }

            $queryLink = [
                'view'      => 'positions',
                'folder_id' => $productFolder->id
            ];

            if ($layout === 'modal') {
                $queryLink = array_merge($queryLink, [
                    'tmpl'   => 'component',
                    'layout' => 'modal'
                ]);
            }

            $folderLink = $this->hyper['helper']['route']->url($queryLink);
            $folder_id  = $this->hyper['input']->get('folder_id', 1, 'int');
            $isActive   = ($productFolder->id === $folder_id) ? true : false;
            $title      = LayoutHelper::render('joomla.html.hptreeprefix', ['level' => $productFolder->level]) . $productFolder->title;

            if ((int) $productFolder->published === HP_STATUS_TRASHED) {
                continue;
            }

            if ($productFolder->level >= 2) {
                if ($productFolder->parent_id !== $folder_id && $productFolder->level >= 4) {
                    continue;
                }
            }

            \JHtmlSidebar::addEntry(
                $title,
                $folderLink,
                $isActive
            );
        }

        $this->sidebar = JHtmlSidebar::render();
    }
}
