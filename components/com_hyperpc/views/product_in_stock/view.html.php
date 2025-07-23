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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Url;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pathway\Pathway;
use HYPERPC\Helper\GoogleHelper;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Delivery\DeliveryFactory;
use HYPERPC\Html\Data\Product\Review;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class HyperPcViewProduct_In_Stock
 *
 * @property    array           $groups
 * @property    array           $groupTree
 * @property    array           $configParts
 * @property    ProductMarker   $product
 * @property    Review          $reviewsData
 *
 * @since       2.0
 */
class HyperPcViewProduct_In_Stock extends ViewLegacy
{
    /**
     * @var string
     */
    protected $_context = HP_OPTION . '.position';

    /**
     * @var MenuItem|null
     */
    protected $_activeMenuItem = null;

    /**
     * Hook on initialize view.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this->_activeMenuItem = $this->hyper['app']->getMenu()->getActive();
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
        $this->product = $this->_getProduct();

        if (!$this->product || empty($this->product->id)) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_PRODUCT_IN_STOCK_NOT_FOUND'), 'info');
            $this->hyper['cms']->redirect(Route::_('index.php?option=com_hyperpc&view=products_in_stock&context=' . $this->_context), 301);
        }

        if ($this->product->isPublished()) {
            $this->hyper['doc']->addHeadLink(Url::pathToUrl($this->product->getViewUrl()), 'canonical', 'rel');
        }

        /** @var Pathway $pathway */
        $pathway = $this->hyper['cms']->getPathway();
        $pathway->addItem($this->product->getName());

        $this->configParts = $this->product->getConfigParts(true, 'a.product_folder_id ASC', false, false, true);

        $this->reviewsData = new Review($this->product, 'default', 0, 4);

        $this->product->params->set('capability', '');

        $itemType = $this->product->getFolder()->getItemsType();
        if ($meta_title = $this->_activeMenuItem->getParams()->get('meta_title_' . $itemType)) {
            $this->product->metadata->set('meta_title', $meta_title);
        }

        if ($meta_desc = $this->_activeMenuItem->getParams()->get('meta_desc_' . $itemType)) {
            $this->product->metadata->set('meta_desc', $meta_desc);
        }

        $this->hyper['helper']['meta']->setup($this->product);

        $this->hyper['helper']['opengraph']
            ->setTitle($this->product->getName())
            ->setImage($this->product->getOGImage());

        /** @var GoogleHelper */
        $googleHelper = $this->hyper['helper']['google'];
        $googleHelper
            ->setDataLayerViewProduct($this->product)
            ->setJsViewItems([$this->product], false, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_PRODUCT_PAGE'), 'product_page')
            ->setDataLayerAddToCart();

        parent::display();
    }

    /**
     * Get part groups
     *
     * @return  array
     *
     * @throws  \RuntimeException|\Exception
     *
     * @since   2.0
     */
    public function getGroups()
    {
        /** @var ProductFolderHelper */
        $groupHelper = $this->hyper['helper']['productFolder'];

        return $groupHelper->getList();
    }

    /**
     * Get part group tree
     *
     * @return  array
     *
     * @throws  \RuntimeException|\Exception
     *
     * @since   2.0
     */
    public function getGroupTree(array $groups)
    {
        $groupModelName = 'Product_Folder';
        $rootCategoryId = (int) $this->hyper['params']->get('configurator_root_category', 1);

        /** @var HyperPcModelProduct_Folder */
        $groupModel = ModelList::getInstance($groupModelName);

        return $groupModel->buildTree($groups, $rootCategoryId);
    }

    /**
     * Get product
     *
     * @return  ProductMarker|null
     *
     * @since   2.0
     */
    protected function _getProduct()
    {
        $configurationId = $this->hyper['input']->get('id');

        $products = $this->hyper['helper']['moyskladStock']->getProductsByConfigurationId($configurationId);

        if (count($products)) {
            return array_shift($products);
        }

        return null;
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

        $this->hyper['helper']['assets']
            ->js('js:widget/site/product.js')
            ->widget('.hp-product', 'HyperPC.SiteProduct', [
                'id' => $this->product->id
            ]);

        $deliveryType = $this->hyper['params']->get('delivery_type', 'Yandex');
        $delivery     = DeliveryFactory::createDelivery($deliveryType);

        $langParam = [
            'free' => Text::_('COM_HYPERPC_FOR_FREE'),
            'startsFrom' => Text::_('COM_HYPERPC_STARTS_FROM'),
            'methodName' => [
                'todoor' => Text::_('COM_HYPERPC_DELIVERY_STANDARD'),
                'connection' => Text::_('COM_HYPERPC_DELIVERY_WITH_CONNECTION'),
                'pickup' => Text::_('COM_HYPERPC_DELIVERY_GET_IN_PICKUP_POINT'),
                'post' => Text::_('COM_HYPERPC_DELIVERY_METHOD_POST'),
                'express' => Text::_('COM_HYPERPC_DELIVERY_EXPRESS')
            ]
        ];

        $this->hyper['helper']['assets']
            ->js('js:widget/site/geo-yandex-delivery.js')
            ->js('js:widget/site/geo-yandex-delivery-card.js')
            ->widget('.jsGeoDelivery', 'HyperPC.Geo.YandexDelivery.Card', [
                'connectionCost'    => $this->hyper['params']->get('connection_cost', 750, 'int'),
                'cityIdentifier'    => $delivery->getCityIdentifireType(),
                'orderPickingDates' => $this->product->getPickingDates(),
                'langTag'           => $this->hyper->getLanguageCode(),
                'lang'              => $langParam
            ]);
    }
}
