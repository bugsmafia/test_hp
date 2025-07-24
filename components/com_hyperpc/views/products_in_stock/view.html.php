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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

use HYPERPC\App;
use HYPERPC\Helper\GoogleHelper;
use HYPERPC\Helper\FilterHelper;
use HYPERPC\Helper\UikitHelper;
use HYPERPC\Helper\RenderHelper;

use HYPERPC\Joomla\Model\Entity\ProductFolder;

use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\View\Html\Data\Manager;
use HYPERPC\Joomla\View\Html\Data\Product\Filter;
use HYPERPC\Filters\FilterFactory;
use HYPERPC\Filters\MoyskladProductIndexFilter;

use Joomla\CMS\Log\Log;


use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use HYPERPC\ORM\Filter\Manager as FilterManager;
//use HYPERPC\Filters\Manager as FilterManager;
use Joomla\CMS\MVC\View\HtmlView;



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
    protected $filterData;

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

    public function __construct($config = [])
    {
        parent::__construct($config);
        $app = Factory::getApplication();
        $this->hyper = App::getInstance();
        dump(__LINE__.__DIR__." --- this->hyper --- ");
        dump($this->hyper);

        
        // Обеспечиваем наличие необходимых сервисов
        if (!$this->hyper->offsetExists('input')) {
            $this->hyper['input'] = Factory::getApplication()->input;
            Log::add('Инициализирован отсутствующий сервис input в hyper', Log::WARNING, 'com_hyperpc');
        }
        if (!$this->hyper->offsetExists('app')) {
            $this->hyper['app'] = Factory::getApplication();
            Log::add('Инициализирован отсутствующий сервис app в hyper', Log::WARNING, 'com_hyperpc');
        }
        if (!$this->hyper->offsetExists('params')) {
            $this->hyper['params'] = new Registry();
            Log::add('Инициализирован отсутствующий сервис params в hyper', Log::WARNING, 'com_hyperpc');
        }
        if (!$this->hyper->offsetExists('helper')) {
            dump(__LINE__.__DIR__." --- this->hyper['helper'] --- ");
            // Инициализируем необходимые сервисы в hyper['helper']
            $this->hyper['helper'] = [
                'google' => new GoogleHelper(),
                'moyskladProduct' => null,
                'money' => null,
                'fields' => null,
                'string' => null,
                'productFolder' => new \HYPERPC\Helper\ProductFolderHelper(),
                'options' => null,
                'render' => new RenderHelper(),
                'uikit' => new UikitHelper(),
                'html' => null,
                'filter' => new FilterHelper()
            ];
            Log::add('Инициализирован отсутствующий сервис helper в hyper', Log::WARNING, 'com_hyperpc');
        } else {
            // Проверяем наличие необходимых ключей в helper

            if (empty($this->hyper['helper']['productFolder'])) {
                $this->hyper['helper']['productFolder'] = new \HYPERPC\Helper\ProductFolderHelper();
                Log::add('Инициализирован отсутствующий сервис productFolder в hyper[helper]', Log::WARNING, 'com_hyperpc');
            }
            if (empty($this->hyper['helper']['options'])) {
                $this->hyper['helper']['options'] = null;
                Log::add('Инициализирован отсутствующий сервис options в hyper[helper]', Log::WARNING, 'com_hyperpc');
            }
            if (empty($this->hyper['helper']['filter'])) {
                $this->hyper['helper']['filter'] = new FilterHelper();
                Log::add('Инициализирован отсутствующий сервис filter в hyper[helper]', Log::WARNING, 'com_hyperpc');
            }
        }
        // Log::add('hyper инициализирован: ' . print_r($this->hyper, true), Log::DEBUG, 'com_hyperpc');
    }

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
        try {
            // Данные берет верно из нужного Filter
            $this->filterData = $this->_getFilterData();
            Log::add('filterData инициализирован: ' . (is_object($this->filterData) ? get_class($this->filterData) : 'null'), Log::DEBUG, 'com_hyperpc');
            

            dump(__LINE__.__DIR__." --- this->filterData --- ");
            dump($this->filterData);
            

            $this->groups   = $this->_getGroups();
            dump(__LINE__.__DIR__." --- this->groups --- ");
            dump($this->groups);
            
            $this->options  = $this->_getOptions();
            dump(__LINE__.__DIR__." --- this->options --- ");
            dump($this->options);

            $this->products = $this->filterData && method_exists($this->filterData, 'getViewItems') && !empty($this->filterData->filter) ? $this->filterData->getViewItems() : [];
            dump(__LINE__.__DIR__." --- this->filterData->getViewItems --- ");
            

            

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

            
        } catch (\Throwable $e) {
            Log::add('Error in display: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            $this->filterData = new Filter();
            $this->groups = [];
            $this->options = [];
            $this->products = [];
        }
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
        parent::_loadAssets();

        if (!count($this->products)) {
            if (isset($this->hyper['wa'])) {
                $this->hyper['wa']->useScript('product.teaser');
            } else {
                Log::add('wa service is missing in hyper', Log::ERROR, 'com_hyperpc');
            }
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
        try {
            if (empty($this->filterData->optionsHelper)) {
                Log::add('optionsHelper is null in HyperPcViewProducts_In_Stock::_getOptions', Log::ERROR, 'com_hyperpc');
                return [];
            }
            return $this->filterData->optionsHelper->getVariants();
        } catch (\Throwable $e) {
            Log::add('Error in _getOptions: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }
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
        try {
            if (empty($this->filterData->groupHelper)) {
                Log::add('groupHelper is null in HyperPcViewProducts_In_Stock::_getGroups', Log::ERROR, 'com_hyperpc');
                return [];
            }
            return $this->filterData->groupHelper->getList();
        } catch (\Throwable $e) {
            Log::add('Error in _getGroups: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }
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
        try {
            $factory = \HYPERPC\Filters\FilterFactory::getInstance('products_in_stock', $this->app);
            $this->filter = $factory->get('moysklad_product_index', $this->app);
            $this->filter->find();
            $this->filterData = $this->filter->getFilterDataJson();
            Log::add('filterData инициализирован: ' . ($this->filterData ? print_r($this->filterData->toArray(), true) : 'null'), Log::DEBUG, 'com_hyperpc');
        } catch (\Throwable $e) {
            Log::add('Error in _getFilterData: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), Log::ERROR, 'com_hyperpc');
            $this->filterData = null;
        }
    }
}
