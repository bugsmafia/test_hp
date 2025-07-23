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
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\GoogleHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\View\Html\Data\Manager;
use HYPERPC\ORM\Filter\Manager as FilterManager;
use HYPERPC\Joomla\View\Html\Data\Product\Filter;

/**
 * Class HyperPcViewProducts_In_Stock
 *
 * @property    array   $groups
 * @property    array   $options
 * @property    array   $products
 * @property    Filter  $filterData
 *
 * @since       2.0
 */
class HyperPcViewProducts_In_Stock extends ViewLegacy
{
    /**
     * Show FPS.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $showFps = true;

    /**
     * Page description.
     *
     * @var     string
     *
     * @since   2.0
     */
    public string $description = '';

    /**
     * Display action.
     *
     * @param   null|string $tpl
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->filterData = $this->_getFilterData();

        $this->groups   = $this->_getGroups();
        $this->options  = $this->_getOptions();
        $this->products = $this->filterData->getViewItems();

        $activeMenuItem = $this->hyper['app']->getMenu()->getActive();

        if ($activeMenuItem) {
            $this->description = $activeMenuItem->getParams()->get('description');
        }

        if ($this->hyper['input']->get->count()) {
            $url = $this->hyper['route']->build([
                'view' => 'products_in_stock',
            ]);

            $this->hyper['doc']->addHeadLink(Url::pathToUrl($url), 'canonical', 'rel');
        }

        /** @var GoogleHelper */
        $googleHelper = $this->hyper['helper']['google'];
        $googleHelper
            ->setDataLayerViewProductList($this->products, Text::_('COM_HYPERPC_PRODUCTS_IN_STOCK_HEADER'), 'products_in_stock')
            ->setJsViewItems($this->products, false, Text::_('COM_HYPERPC_PRODUCTS_IN_STOCK_HEADER'), 'products_in_stock')
            ->setDataLayerProductClickFunc()
            ->setDataLayerAddToCart();

        parent::display();
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

        if (!count($this->products)) {
            $this->hyper['wa']->useScript('product.teaser');
        }
    }

    /**
     * Get options list
     *
     * @return  mixed
     *
     * @since   2.0
     */
    protected function _getOptions()
    {
        $optionHelper = $this->filterData->optionsHelper;

        return $optionHelper->getVariants();
    }

    /**
     * Get groups list
     *
     * @return  mixed
     *
     * @since   2.0
     */
    protected function _getGroups()
    {
        $groupHelper = $this->filterData->groupHelper;

        return $groupHelper->getList();
    }

    /**
     * Get filter data
     *
     * @return Filter|null
     *
     * @throws Exception
     * @since   2.0
     */
    protected function _getFilterData()
    {
        return Manager::getInstance()->get('Product.Filter', [
            'filter' => FilterManager::getInstance()->get('MoyskladProductInStock')
        ]);
    }
}
