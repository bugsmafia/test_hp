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
use Joomla\CMS\Component\ComponentHelper;

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

    protected $filter;
    protected $items;
    protected $pagination;
    protected $groups;
    protected $options;


    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->hyper = App::getInstance();

        // Инициализация filter
        if (empty($this->filter)) {
            try {
                $this->filter = new \HYPERPC\Filters\MoyskladProductIndexFilter([
                    'hyper' => [
                        'params' => ComponentHelper::getParams('com_hyperpc'),
                        'helper' => $this->hyper['helper']
                    ]
                ]);
                Log::add('filter initialized: ' . get_class($this->filter), Log::DEBUG, 'com_hyperpc');
            } catch (\Throwable $e) {
                Log::add('Error initializing filter: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
                $this->filter = null;
            }
        }

        // Инициализация filterData->helper для шаблона
        $this->filterData = new \stdClass();
        try {
            $this->filterData->helper = $this->hyper['helper']['filter'] ?? new FilterHelper();
            Log::add('FilterHelper initialized for filterData', Log::DEBUG, 'com_hyperpc');
        } catch (\Throwable $e) {
            Log::add('Error initializing FilterHelper: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            $this->filterData->helper = null;
        }
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
            $this->filterData->filters = $this->_getFilterData() ?? ['available' => [], 'current' => [], 'prices' => ['min' => 0, 'max' => 0]];
            $this->groups = $this->_getGroups() ?? [];
            $this->options = $this->_getOptions() ?? [];
            $this->products = $this->filter ? $this->_prepareProducts($this->filter->getItems($this->filterData->filters['current'] ?? []) ?? []) : [];
            $this->items = $this->filter ? ($this->filter->getItems($this->filterData->filters['current'] ?? []) ?? []) : [];
            $this->pagination = $this->get('Pagination') ?? null;
            $this->showFps = $this->hyper['params']->get('show_fps', false);

            Log::add('filterHelper: ' . ($this->filterData->helper ? get_class($this->filterData->helper) : 'null'), Log::DEBUG, 'com_hyperpc');
            Log::add('renderHelper: ' . get_class($this->hyper['helper']['render']), Log::DEBUG, 'com_hyperpc');
            Log::add('uikitHelper: ' . get_class($this->hyper['helper']['uikit']), Log::DEBUG, 'com_hyperpc');
            Log::add('Products count: ' . count($this->products), Log::DEBUG, 'com_hyperpc');
            Log::add('AssetsHelper available: ' . (isset($this->hyper['helper']['assets']) ? 'yes' : 'no'), Log::DEBUG, 'com_hyperpc');
            Log::add('Filters data: ' . json_encode($this->filterData->filters), Log::DEBUG, 'com_hyperpc');
            Log::add('Options data: ' . json_encode($this->options), Log::DEBUG, 'com_hyperpc');
            Log::add('Show FPS: ' . ($this->showFps ? 'true' : 'false'), Log::DEBUG, 'com_hyperpc');

            $this->_loadAssets();
        } catch (\Throwable $e) {
            Log::add('Error in display: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), Log::ERROR, 'com_hyperpc');
            throw $e;
        }

        parent::display($tpl);
    }

    /**
     * Prepare products with necessary methods.
     *
     * @param array $items
     * @return array
     */
    protected function _prepareProducts(array $items): array
    {
        $db = Factory::getDbo();
        $products = [];

        foreach ($items as $item) {
            $product = new class($item) {
                private $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function __get($name)
                {
                    return $this->data->$name ?? null;
                }

                public function getFolder()
                {
                    $db = Factory::getDbo();
                    $query = $db->getQuery(true)
                        ->select(['id', 'title', 'alias'])
                        ->from($db->quoteName('#__hp_product_folders'))
                        ->where($db->quoteName('id') . ' = ' . (int)($this->data->product_folder_id ?? 116));
                    $folder = $db->setQuery($query)->loadObject() ?? new \stdClass();
                    Log::add('Folder fetched for product ID ' . ($this->data->id ?? 'unknown') . ': ' . json_encode($folder), Log::DEBUG, 'com_hyperpc');
                    return $folder;
                }

                public function getRender($type = 'teaser')
                {
                    $renderData = new class {
                        public $html;
                        public $data;
                        private $entity;

                        public function __construct($html = '', $data = null)
                        {
                            $this->html = $html;
                            $this->data = $data ?? new \stdClass();
                        }

                        public function setEntity($entity)
                        {
                            $this->entity = $entity;
                            Log::add('setEntity called with entity: ' . (is_object($entity) ? get_class($entity) : gettype($entity)), Log::DEBUG, 'com_hyperpc');
                            return $this;
                        }
                    };

                    // Генерация HTML с учетом данных изображения
                    $html = '<div class="product-render-' . htmlspecialchars($type) . '">';
                    $html .= '<h3>' . htmlspecialchars($this->data->name ?? '') . '</h3>';
                    if (!empty($this->data->images)) {
                        $images = json_decode($this->data->images, true);
                        if (!empty($images['image_teaser'])) {
                            $html .= '<img src="' . htmlspecialchars($images['image_teaser']) . '" alt="' . htmlspecialchars($this->data->name ?? '') . '">';
                        }
                    }
                    $html .= '</div>';

                    $renderData = new $renderData($html, $this->data);

                    Log::add('Render data for product ID ' . ($this->data->id ?? 'unknown') . ': ' . json_encode(['html' => $renderData->html, 'data' => $renderData->data]), Log::DEBUG, 'com_hyperpc');
                    return $renderData;
                }
            };

            $products[] = $product;
        }

        Log::add('Prepared products: ' . count($products), Log::DEBUG, 'com_hyperpc');
        return $products;
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
        $this->hyper['wa']->useScript('product.teaser');
        if (!count($this->items)) {
            $this->hyper['wa']->useScript('product.teaser');
        }
    }


    /**
     * Get options for filters.
     *
     * @return array
     */
    protected function _getOptions()
    {
        $db = Factory::getDbo();
        $allowedFilters = $this->hyper['params']->get('filter_product_allowed_moysklad', []);

        Log::add('Allowed filters in _getOptions: ' . json_encode($allowedFilters), Log::DEBUG, 'com_hyperpc');

        if (empty($allowedFilters)) {
            Log::add('No allowed filters for options in _getOptions', Log::WARNING, 'com_hyperpc');
            return [];
        }

        $fieldIds = array_column($allowedFilters, 'id');
        if (empty($fieldIds)) {
            Log::add('No field IDs found in allowed filters for options', Log::WARNING, 'com_hyperpc');
            return [];
        }

        try {
            $fieldQuery = $db->getQuery(true)
                ->select(['id', 'name', 'title'])
                ->from($db->quoteName('#__fields'))
                ->where($db->quoteName('id') . ' IN (' . implode(',', array_map('intval', $fieldIds)) . ')')
                ->where($db->quoteName('context') . ' = ' . $db->quote('com_hyperpc.product'));
            $fields = $db->setQuery($fieldQuery)->loadObjectList('id');

            Log::add('Fields query: ' . $fieldQuery->dump(), Log::DEBUG, 'com_hyperpc');
            Log::add('Fields fetched for options: ' . json_encode($fields), Log::DEBUG, 'com_hyperpc');

            if (empty($fields)) {
                Log::add('No fields found for IDs: ' . implode(',', $fieldIds), Log::WARNING, 'com_hyperpc');
                return [];
            }

            $query = $db->getQuery(true)
                ->select(['fv.field_id', 'fv.value', 'fv.value AS label'])
                ->from($db->quoteName('#__fields_values', 'fv'))
                ->where($db->quoteName('fv.field_id') . ' IN (' . implode(',', array_map('intval', $fieldIds)) . ')');
            
            $results = $db->setQuery($query)->loadObjectList();
            Log::add('Options query: ' . $query->dump(), Log::DEBUG, 'com_hyperpc');

            $options = [];
            foreach ($results as $row) {
                $fieldName = $fields[$row->field_id]->name ?? null;
                if ($fieldName) {
                    $options[$fieldName][] = [
                        'value' => $row->value,
                        'label' => $row->value
                    ];
                }
            }

            Log::add('Options fetched: ' . json_encode($options), Log::DEBUG, 'com_hyperpc');
            return $options;
        } catch (\Throwable $e) {
            Log::add('Error fetching options: ' . $e->getMessage(), Log::ERROR, 'com_hyperpc');
            return [];
        }
    }

    /**
     * Get groups for filters.
     *
     * @return array
     */
    protected function _getGroups()
    {
        if (empty($this->hyper['helper']['groupHelper'])) {
            Log::add('groupHelper is not available, returning empty array', Log::WARNING, 'com_hyperpc');
            return [];
        }

        $groupHelper = $this->hyper['helper']['groupHelper'];
        $groups = $groupHelper->getGroups() ?? [];
        Log::add('Groups fetched: ' . json_encode($groups), Log::DEBUG, 'com_hyperpc');
        return $groups;
    }

    /**
     * Get filter data.
     *
     * @return array
     */
    protected function _getFilterData()
    {
        $db = Factory::getDbo();
        $input = $this->hyper['input'];
        $filterHelper = $this->hyper['helper']['filter'] ?? null;

        // Используем FilterHelper для получения фильтров, если метод доступен
        $filters = [];
        if ($filterHelper && method_exists($filterHelper, 'getFilterData')) {
            $filters = $filterHelper->getFilterData([], [])['current'] ?? [];
            Log::add('Filters from FilterHelper::getFilterData: ' . json_encode($filters), Log::DEBUG, 'com_hyperpc');
        } else {
            $filters = $input->get('filter', [], 'array');
            Log::add('Raw filters from input: ' . json_encode($filters), Log::DEBUG, 'com_hyperpc');
        }

        if (empty($filters)) {
            Log::add('No filters available in _getFilterData', Log::WARNING, 'com_hyperpc');
        }

        $query = $db->getQuery(true)
            ->select(['MIN(p.price_a) AS min', 'MAX(p.price_a) AS max'])
            ->from($db->quoteName('#__hp_moysklad_products_index', 'p'))
            ->join('LEFT', $db->quoteName('#__hp_positions', 'pos') . ' ON pos.id = p.product_id')
            ->where($db->quoteName('pos.product_folder_id') . ' = ' . (int)($input->getInt('folder_id', 116)));

        Log::add('Prices query: ' . $query->dump(), Log::DEBUG, 'com_hyperpc');
        $prices = $db->setQuery($query)->loadObject();

        $filterData = [
            'available' => [],
            'current' => $filters,
            'prices' => [
                'min' => $prices->min ?? 0,
                'max' => $prices->max ?? 0
            ]
        ];

        Log::add('Filter data generated: ' . json_encode($filterData), Log::DEBUG, 'com_hyperpc');
        return $filterData;
    }
}
