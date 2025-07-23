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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Url;
use HYPERPC\Filters\Filter;
use Joomla\CMS\Language\Text;
use HYPERPC\Filters\FilterFactory;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcViewProduct_Folder
 *
 * @property    array               $params
 * @property    array               $options
 * @property    array               $filters
 * @property    bool                $showFps
 * @property    int                 $filterType
 * @property    bool                $hasArchive
 * @property    array               $resultFilters
 * @property    ProductFolder       $productFolder
 * @property    array               $productFolders
 *
 * @since       2.0
 */
class HyperPcViewProduct_Folder extends ViewLegacy
{
    /**
     * Check is archive.
     */
    public bool $isArchive = false;

    /**
     * Should folder positions be shown
     */
    public bool $showPositions = true;

    /**
     * Should subgroups be shown
     */
    public bool $showSubGroups = true;

    public array $products = [];
    public array $parts = [];
    public array $services = [];

    public Filter $partsFilter;

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
        $db = $this->hyper['db'];
        $folderId = $this->hyper['input']->getInt('id');
        $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];

        /** @var ProductFolder */
        $this->productFolder = $this->hyper['helper']['productFolder']->findById($folderId, [
            'conditions' => [$db->quoteName('a.published') . ' IN (' . implode(',', $publishStatuses) . ')']
        ]);

        if (!$this->productFolder->id) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_CATEGORY'), 404);
        }

        $app  = $this->hyper['app'];
        $menu = $app->getMenu()->getActive();
        if (!is_object($menu)) {
            $this->hyper['doc']->setMetaData('robots', 'noindex, nofollow');
        }

        $this->hyper['doc']->addHeadLink(Url::pathToUrl($this->productFolder->getViewUrl()), 'canonical', 'rel');

        $this->showSubGroups = $this->productFolder->getParams()->get('show_sub_categories', true, 'bool');

        $this->showPositions = $this->productFolder->getParams()->get('show_elements', true, 'bool');

        if ($this->showPositions) {
            // Products
            $this->products = $this->productFolder->getProducts([], 'a.list_price ASC');
            if (count($this->products)) {
                $this->showFps = $this->hyper['helper']['fps']->showFps($this->productFolder->id);
                $this->productFolders = $this->hyper['helper']['productFolder']->findAll([
                    'conditions' => [$db->quoteName('a.published') . ' IN (' . implode(',', $publishStatuses) . ')']
                ]);
                $this->options = $this->hyper['helper']['moyskladVariant']->getVariants();
            }

            // Parts
            $this->partsFilter = FilterFactory::createFilter('productFolderParts');
            if ($this->partsFilter->hasFilters()) {
                $this->_loadPartsFilterAssets();
            }

            $hasParts = $this->partsFilter->hasItems();
            if ($hasParts) {
                $this->parts = $this->hyper['helper']['moyskladPart']->getByItemKeys($this->partsFilter->getItems());
            }

            // Services
            $this->services = $this->productFolder->getServices([], 'a.list_price ASC');

            if (count($this->products) || count($this->parts) || count($this->services)) {
                $this->hyper['helper']['google']
                    ->setDataLayerViewProductList($this->parts) /** @todo use all goods */
                    ->setJsViewItems($this->parts, false, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_CATEGORY_PAGE'), 'category_page')
                    ->setDataLayerProductClickFunc()
                    ->setDataLayerAddToCart();

                $xmlCategoryId = $this->hyper['helper']['yandex']->getCategoryId((int) $this->productFolder->id);
                $this->hyper['doc']->addScriptDeclaration('window.hpXmlCategoryId = ' . $xmlCategoryId . ';');
            }
        }

        $this->hyper['helper']['meta']->setup($this->productFolder);
        $this->hyper['helper']['opengraph']
            ->setImage($this->productFolder->params->get('image', '', 'hpimagepath'));

        parent::display($tpl);
    }

    /**
     * Check has group archive items.
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function _hasGroupArchive()
    {
        return (bool) $this->productFolder->hasArchiveParts();
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadPartsFilterAssets()
    {
        $cols = $this->hyper['params']->get('parts_cols', HP_DEFAULT_ROW_COLS);

        $this->hyper['helper']['assets']
            ->js('js:widget/site/group-filter-ajax.js')
            ->widget('.jsGroupFilter', 'HyperPC.GroupFilterAjax', [
                'gridClassWFilters'   => $this->hyper['helper']['uikit']->getResponsiveClassByCols($cols - 1),
                'gridClassDefault'    => $this->hyper['helper']['uikit']->getResponsiveClassByCols($cols),
                'clearAllFiltersText' => Text::_('COM_HYPERPC_CLEAR_ALL_FILTERS'),
                'context'             => 'product_folders'
            ]);

        if (!$this->hyper['detect']->isMobile()) {
            $this->hyper['wa']->useScript('jquery-sticky-sidebar');
        }
    }
}
