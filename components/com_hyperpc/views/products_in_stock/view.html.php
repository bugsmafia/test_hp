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
     * Display the view.
     *
     * @param string $tpl
     * @return void
     */
    public function display($tpl = null)
    {
        try {
            $this->filterData = $this->_getFilterData();
            $this->groups = $this->_getGroups();
            $this->options = $this->_getOptions();
            $this->items = $this->filter->getItems($this->filterData['filters']['current'] ?? []);
            $this->pagination = $this->get('Pagination');

            Log::add('filterHelper: ' . get_class($this->filter), Log::DEBUG, 'com_hyperpc');
            Log::add('renderHelper: ' . get_class($this->hyper['helper']['renderHelper']), Log::DEBUG, 'com_hyperpc');
            Log::add('uikitHelper: ' . get_class($this->hyper['helper']['uikitHelper']), Log::DEBUG, 'com_hyperpc');
        } catch (\Throwable $e) {
            Log::add('Error in display: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            throw $e;
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
     * Get options for filters.
     *
     * @return array
     */
    protected function _getOptions()
    {
        if (empty($this->hyper['helper']['optionsHelper'])) {
            Log::add('optionsHelper is null in HyperPcViewProducts_In_Stock::_getOptions', Log::ERROR, 'com_hyperpc');
            return [];
        }

        $optionsHelper = $this->hyper['helper']['optionsHelper'];
        return $optionsHelper->getOptions();
    }

    /**
     * Get groups for filters.
     *
     * @return array
     */
    protected function _getGroups()
    {
        if (empty($this->hyper['helper']['groupHelper'])) {
            Log::add('groupHelper is null in HyperPcViewProducts_In_Stock::_getGroups', Log::ERROR, 'com_hyperpc');
            return [];
        }

        $groupHelper = $this->hyper['helper']['groupHelper'];
        return $groupHelper->getGroups();
    }

    /**
     * Get filter data for products in stock view.
     *
     * @return array
     */
    protected function _getFilterData()
    {
        // Попытка получить фильтры из модели
        $filters = $this->get('Filters', 'HyperPCModelProducts_In_Stock');
        if (is_string($filters)) {
            Log::add('Invalid filters type in _getFilterData, expected array, got string', Log::WARNING, 'com_hyperpc');
            $filters = [];
        }

        $this->filterData = ['filters' => ['available' => [], 'current' => $filters, 'prices' => ['min' => 0, 'max' => 0]]];

        Log::add('Filters passed to _getFilterData: ' . json_encode($filters), Log::DEBUG, 'com_hyperpc');

        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(['MIN(p.price_a) AS min', 'MAX(p.price_a) AS max'])
                ->from($db->quoteName('#__hp_moysklad_products_index', 'p'))
                ->join('LEFT', $db->quoteName('#__hp_positions', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('p.product_id'))
                ->where($db->quoteName('pos.product_folder_id') . ' = 116');

            $prices = $db->setQuery($query)->loadObject();

            $this->filterData['filters']['prices'] = [
                'min' => $prices->min ?? 0,
                'max' => $prices->max ?? 0
            ];
            Log::add('Prices query: ' . $query->dump(), Log::DEBUG, 'com_hyperpc');
        } catch (\Throwable $e) {
            Log::add('Error fetching prices: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
        }

        Log::add('Filter data generated: ' . json_encode($this->filterData), Log::DEBUG, 'com_hyperpc');
        return $this->filterData;
    }
}
