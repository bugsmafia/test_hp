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

namespace HYPERPC\Joomla\View\Html\Data\Product;

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use HYPERPC\ORM\Entity\Store;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use JBZoo\Utils\Filter as UFilter;
use HYPERPC\ORM\Filter\AbstractFilter;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Html\Types\Filter\Element;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Helper\MoyskladFilterHelper;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\ORM\Filter\Manager as FilterManager;
use HYPERPC\Joomla\View\Html\Data\FilterHtmlData;
use HYPERPC\ORM\Filter\MoyskladProductInStockFilter;

/**
 * Class Filter
 *
 * @property-read   AbstractFilter          $filter
 * @property-read   null|int                $groupId
 * @property-read   MoyskladFilterHelper    $helper
 * @property-read   ProductFolderHelper     $groupHelper
 * @property-read   MoyskladVariantHelper   $optionsHelper
 *
 * @package         HYPERPC\Joomla\View\Html\Data\Product
 *
 * @since           2.0
 */
class Filter extends FilterHtmlData
{

    const CATEGORY_FIELD_NAME = 'category';
    const PRICE_FIELD_NAME    = 'price-range';
    const STORE_FIELD_NAME    = 'store';

    /**
     * Get category list html.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCategoryListHtml()
    {
        $html = [];
        if ($this->helper->enableCategoryFilter()) {
            $defaultFilter    = $this->getDefaultFilterState();
            $categories       = $this->filter->getCategoryList();
            $fieldOptionCount = new JSON($this->filter->getFieldOptionsCount());

            if (count($categories)) {
                /** @var ProductFolder $category */
                foreach ($categories as $category) {
                    $defaultCount = $defaultFilter->findPropertyCount('tProd.category_id', $category->id);
                    if ((bool) $defaultCount === false) {
                        continue;
                    }

                    $optionChecked = false;
                    $queryOptions  = $this->getInput('category');
                    $defaultCount  = $fieldOptionCount->find('category.' . $category->id . '.count');

                    if (is_array($queryOptions) && count($queryOptions) > 0) {
                        foreach ($queryOptions as $queryValue) {
                            if ((int) $queryValue === $category->id) {
                                $optionChecked = $element['hasActive'] = true;
                            }
                        }
                    }

                    $html[] = $this->helper->renderField(
                        'checkbox',
                        $category->id,
                        $defaultCount,
                        $category->title,
                        ['isChecked' => $optionChecked]
                    );
                }
            }
        }

        return $html;
    }

    /**
     * Get default filter state object.
     *
     * @return  AbstractFilter|null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getDefaultFilterState()
    {
        static $instance;
        if ($instance === null) {
            $filter = $this->filter->context === 'com_hyperpc.position' ? 'MoyskladProductInStock' : 'ProductInStock';

            $instance = FilterManager::getInstance()->get($filter);
            $instance->setFilterData([]);
        }

        return $instance;
    }

    /**
     * Get render elements.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getRenderElements()
    {
        if (!$this->helper->hasFilterRender()) {
            return [];
        }

        $this->filter->setFieldOptionsCount();

        $allowedValues    = $this->getAllowedFilterFields();
        $fieldOptionCount = new JSON($this->filter->getFieldOptionsCount());
        $defaultFilter    = $this->getDefaultFilterState();

        $elements = [];

        $this
            ->_addStoreFilterElement($elements)
            ->_addPriceRangeFilterElement($elements)
            ->_addCategoryFilterElement($elements);

        /** @var Field $field */
        foreach ($allowedValues as $field) {
            $name  = $field->get('name');
            $title = $field->get('title');

            $options   = (array) $field->fieldparams->get('options');
            $hasFilter = $this->filter->hasFilter($name);

            if (!array_key_exists($name, $elements)) {
                $element = [
                    'name'       => $name,
                    'title'      => $title,
                    'type'       => 'checkbox',
                    'hasActive'  => false,
                    'hasFilters' => $hasFilter
                ];

                if (count($options) > 0) {
                    $optionsHtml = [];
                    foreach ($options as $option) {
                        $option = new JSON($option);
                        $defaultCount = $defaultFilter->findPropertyCount($name, $option->get('value'));

                        //  Please update filter index notice.
                        if ($defaultCount instanceof \Exception) {
                            if ($this->hyper->isDevUser() || $this->hyper->isLocalDomain()) {
                                $optionsHtml[] = implode(PHP_EOL, [
                                    '<div class="uk-alert uk-alert-warning">',
                                        Text::_('COM_HYPERPC_ERROR_FILTER_INDEX_PLEASE_UPDATE_DATA_INDEX'),
                                    '</div>'
                                ]);
                            }

                            break;
                        }

                        if ((bool) $defaultCount === false) {
                            continue;
                        }

                        if ($option->get('value') === 'none') {
                            $option->get('name', Text::_('COM_HYPERPC_NOT_DEFINED'));
                        }

                        $actualCountValue = $fieldOptionCount->find(implode('.', [
                            $field->get('name'),
                            $option->get('value'),
                            'count'
                        ]));

                        $optionChecked = false;
                        $queryOptions  = $this->getInput($field->get('name'));
                        if (is_array($queryOptions) && count($queryOptions) > 0) {
                            foreach ($queryOptions as $queryValue) {
                                if ($queryValue === $option->get('value')) {
                                    $optionChecked = $element['hasActive'] = true;
                                }
                            }
                        }

                        $optionsHtml[] = $this->helper->renderField(
                            'checkbox',
                            $option->get('value'),
                            $actualCountValue,
                            $option->get('name'),
                            ['isChecked' => $optionChecked]
                        );
                    }

                    $element['html'] = $optionsHtml;
                }

                $elements[$name] = new Element($element);
            }
        }

        return $elements;
    }

    /**
     * Render stores form element.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getStoreListHtml()
    {
        $html = [];

        if ($this->filter instanceof MoyskladProductInStockFilter) {
            $defaultFilter    = $this->getDefaultFilterState();
            $storeHelper      = $this->filter->getStoreHelper();
            $filterHelper     = $this->filter->getFilterHelper();

            $stores           = $storeHelper->findAll();
            // $selectData       = [HtmlHelper::_('select.option', null, Text::_('COM_HYPERPC_ALL_STORES'))];
            $fieldOptionCount = new JSON($this->filter->getFieldOptionsCount());
            $storeInput       = $this->getInput('store');
            $legacyStores     = $this->filter->getStores();

            /** @var Store $store */
            foreach ($stores as $store) {
                if ($this->filter->context === 'com_hyperpc.position') {
                    $stockStoreId = $storeHelper->convertToLagacyId((int) $store->id);
                    if (!$store->published || !array_key_exists($stockStoreId, $legacyStores)) {
                        continue;
                    }
                } else {
                    $stockStoreId = $store->id;
                }

                $defaultCount = $defaultFilter->findPropertyCount('stock.store_id', $store->id);
                if ((bool) $defaultCount === false || isset($html[$stockStoreId])) {
                    continue;
                }

                $optionChecked = false;
                // $selectData[]  = HTMLHelper::_('select.option', $store->id, $store->name);

                $filteredCount = $fieldOptionCount->find('store.' . $stockStoreId . '.count');

                if (is_array($storeInput) && count($storeInput) > 0) {
                    foreach ($storeInput as $queryValue) {
                        if ((int) $queryValue === $stockStoreId) {
                            $optionChecked = true;
                        }
                    }
                } elseif ($filteredCount > 0) { // set store checked if not specified
                    $optionChecked = true;
                    $storeArray = $this->filter->_filterData->get(self::STORE_FIELD_NAME);
                    $storeArray[] = $stockStoreId;
                    $this->filter->_filterData->set(self::STORE_FIELD_NAME, $storeArray);
                }

                $html[$stockStoreId] = $filterHelper->renderField(
                    'checkbox',
                    $stockStoreId,
                    $filteredCount,
                    $legacyStores[$stockStoreId]->name,
                    ['isChecked' => $optionChecked]
                );
            }
        }

        return $html;
    }

    /**
     * Get render items for view.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getViewItems()
    {
        $this->filter->setFilterData($this->getUrlQueryData());
        $this->filter->find();

        return $this->filter->getItems();
    }

    /**
     * Magic method on initialize class.
     *
     * @param   AbstractFilter  $filter
     * @param   null            $groupId
     *
     * @since   2.0
     */
    public function initialize(AbstractFilter $filter, $groupId = null)
    {
        $this->filter           = $filter;
        $this->groupId          = $groupId;
        $this->helper           = $this->filter->getFilterHelper();
        $this->groupHelper      = $this->filter->getGroupHelper();
        $this->optionsHelper    = $this->filter->getOptionsHelper();

        $this->loadAssets();
    }

    /**
     * Load filter assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $uri  = Uri::getInstance();
        $cols = HP_DEFAULT_ROW_COLS;

        if (!$this->hyper['detect']->isMobile()) {
            $this->hyper['wa']->useScript('jquery-sticky-sidebar');
        }

        $this->hyper['helper']['assets']
            ->js('js:widget/site/group-filter-ajax.js')
            ->js('js:widget/site/product-filter.js')
            ->widget('.jsInstockProducts', 'HyperPC.GroupFilterAjax.SiteProductFilter', [
                'token'               => Session::getFormToken(),
                'clearAllFiltersText' => Text::_('COM_HYPERPC_CLEAR_ALL_FILTERS'),
                'uriPath'             => rtrim(Uri::base(), '/') . $uri->getPath()
            ]);
    }

    /**
     * Render price range form element.
     *
     * @return  string|null
     *
     * @since   2.0
     */
    public function renderPriceRage()
    {
        $enablePrice = UFilter::bool($this->hyper['params']->get('filter_enabled_price', 0));
        if ($enablePrice === true) {
            return $this->render('product/filter/elements/price-range');
        }

        return null;
    }

    /**
     * Add category filter element for filter render.
     *
     * @param   array  $elements
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _addCategoryFilterElement(array &$elements)
    {
        $type = 'category';

        if ($this->helper->enableCategoryFilter()) {
            $hasFilter = $this->filter->hasFilter(Filter::CATEGORY_FIELD_NAME);

            $category = [
                'type'       => 'checkbox',
                'hasActive'  => $hasFilter,
                'hasFilters' => $hasFilter,
                'name'       => self::CATEGORY_FIELD_NAME,
                'title'      => Text::_('COM_HYPERPC_FILTER_CATEGORY_TITLE'),
                'html'       => $this->getCategoryListHtml()
            ];

            $elements[$type] = new Element($category);
        }

        return $this;
    }

    /**
     * Add price range filter element for filter render.
     *
     * @param   $elements
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _addPriceRangeFilterElement(array &$elements)
    {
        $type      = 'price-range';
        $hasFilter = $this->filter->hasFilter(Filter::PRICE_FIELD_NAME);

        $priceRangeRVal = new JSON([
            'from' => 0,
            'to'   => 0
        ]);

        $priceRangeVal  = $this->hyper['input']->get(self::PRICE_FIELD_NAME, ':', 'string');
        if (preg_match('/:/', $priceRangeVal)) {
            list ($vFrom, $vTo) = (array) explode(':', $priceRangeVal);

            $priceRangeRVal
                ->set('to', $vTo)
                ->set('from', $vFrom);
        }

        $moneyHelper = $this->hyper['helper']['money'];
        $currencySymbol = $moneyHelper->getCurrencySymbol($moneyHelper->get(0));

        $priceRange = [
            'type'       => $type,
            'hasActive'  => $hasFilter,
            'hasFilters' => $hasFilter,
            'name'       => self::PRICE_FIELD_NAME,
            'title'      => Text::sprintf('COM_HYPERPC_FILTER_PRICE_RANGE_TITLE', $currencySymbol),
            'html'       => [
                $this->helper->renderField(
                    $type,
                    $priceRangeRVal,
                    0,
                    Text::sprintf('COM_HYPERPC_FILTER_PRICE_RANGE_TITLE', $currencySymbol)
                )
            ]
        ];

        $elements[$type] = new Element($priceRange);

        return $this;
    }

    /**
     * @param   array   $elements
     *
     * @return  $this
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _addStoreFilterElement(array &$elements)
    {
        $hasFilter = $this->filter->hasFilter(Filter::STORE_FIELD_NAME);

        $storeData = [
            'hasActive'  => $hasFilter,
            'hasFilters' => $hasFilter,
            'type'       => 'checkbox',
            'name'       => self::STORE_FIELD_NAME,
            'title'      => Text::_('COM_HYPERPC_FILTER_STORE_TOGGLE_TITLE'),
            'html'       => $this->getStoreListHtml()
        ];

        if (empty($this->getInput(self::STORE_FIELD_NAME))) {
            $storeData['hasActive'] = true;
            $storeData['hasFilters'] = true;
        }

        $elements[Filter::STORE_FIELD_NAME] = new Element($storeData);

        return $this;
    }
}
