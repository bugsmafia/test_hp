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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pathway\Pathway;
use JBZoo\SimpleTypes\Exception;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Delivery\DeliveryFactory;
use HYPERPC\Html\Data\Product\Review;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Joomla\Model\Entity\PromoCode;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Object\MiniConfigurator\ProductServiceData;

/**
 * Class HyperPcViewMoysklad_Product
 *
 * @property array           $partsRenderData
 * @property array           $foldersTree
 * @property array           $configParts
 * @property MoyskladProduct $product
 * @property ProductFolder   $folder
 * @property Review          $reviewsData
 * @property array           $folders
 * @property string          $moduleId
 *
 * @since       2.0
 */
class HyperPcViewMoysklad_Product extends ViewLegacy
{
    const PART_IMG_WIDTH   = 780;
    const PART_IMG_HEIGHT  = 439;

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
        $db         = $this->hyper['db'];
        $product_id = $this->hyper['input']->get('id');
        $folder_id  = $this->hyper['input']->get('product_folder_id');

        $this->product = $this->hyper['helper']['moyskladProduct']->findById($product_id);

        if (empty($this->product->id) || $this->product->isTrashed()) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_PRODUCT'), 404);
        } elseif (!$this->product->isPublished()) {
            $this->hyper['cms']->redirect(Route::_('index.php?option=com_hyperpc&view=product_folder&id=' . $folder_id), 301);
        }

        $this->configParts = $this->product->getConfigParts(true, 'a.product_folder_id ASC', false, false, false);

        $publishStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
        $this->folders   = $this->hyper['helper']['productFolder']->findAll([
            'conditions' => [$db->quoteName('a.published') . ' IN (' . implode(',', $publishStatuses) . ')'],
            'order'      => $db->quoteName('a.lft') . ' ASC'
        ]);

        $rootCategory = (int) $this->hyper['params']->get('configurator_root_category', 1);

        /** @var HyperPcModelProduct_Folder $model */
        $model = ModelList::getInstance('Product_folder');
        $this->foldersTree = $model->buildTree($this->folders, $rootCategory);

        $this->folder = $this->product->getFolder();
        if (!in_array($this->folder->published, [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED])) {
            throw new Exception(Text::_('COM_HYPERPC_ERROR_PAGE_NOT_FOUND'), 404);
        }

        $this->reviewsData = new Review($this->product, 'default', 0, 4);

        if ($this->folder->getItemsType() === ProductFolderHelper::ITEMS_TYPE_NOTEBOOK) {
            $this->moduleId = $this->hyper['params']->get('notebook_certificates');
        } else {
            $this->moduleId = $this->hyper['params']->get('product_certificates');
        }

        $this->hyper['helper']['meta']->setup($this->product);
        $this->hyper['helper']['opengraph']
            ->setImage($this->product->getOGImage());

        if ($this->getLayout() !== 'landing') {
            /** @var Pathway $pathway */
            $pathway = $this->hyper['cms']->getPathway();
            $pathway->addItem($this->product->name);
        }

        $this->hyper['helper']['google']
            ->setDataLayerViewProduct($this->product)
            ->setJsViewItems([$this->product], false, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_PRODUCT_PAGE'), 'product_page')
            ->setDataLayerAddToCart();

        parent::display($tpl);
    }

    /**
     * Display group parts.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function displayGroupParts()
    {
        $this->product = $this->get('Item');

        if ($this->product->id === null) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_PRODUCT'), 404);
        }

        $this->folder = $this->hyper['helper']['productFolder']->findById($this->hyper['input']->get('folder_id'));
        if (!$this->folder->id) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_GROUP'), 404);
        }

        $this->hyper['doc']->setMetaData('robots', 'noindex');

        $this->partsRenderData = $this->product->getGroupPartsData($this->folder);

        echo $this->loadTemplate('folder_parts');

        $jsItems = [];
        foreach ($this->partsRenderData as $item) {
            $jsItems[$item->itemKey] = $item->jsData;
        }

        $this->hyper['helper']['assets']
            ->js('js:widget/site/product_mini_configurator.js')
            ->widget('.jsChangeGroupPart', 'HyperPC.SiteProductMiniConfigurator', [
                'items'      => $jsItems,
                'product_id' => $this->product->id
            ]);
    }

    /**
     * Action display service.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function displayService()
    {
        $this->product = $this->get('Item');

        if ($this->hyper['input']->get('config_id')) {
            $this->product->set('saved_configuration', $this->hyper['input']->get('config_id'));
        }

        if ($this->product->id === null) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_PRODUCT'), 404);
        }

        $this->folder = $this->hyper['helper']['productFolder']->findById($this->hyper['input']->get('group_id'));
        if (!$this->folder->id) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_GROUP'), 404);
        }

        $this->hyper['doc']->setMetaData('robots', 'noindex');

        $this->hyper['helper']['assets']
            ->js('js:widget/site/cart/product-service.js')
            ->widget('#hp-product-service', 'HyperPC.CartProductService', [
                'group_id'   => $this->folder->id,
                'product_id' => $this->product->id,
                'item_key'   => $this->hyper['input']->get('item-key'),
                'def_part'   => $this->hyper['input']->get('d_pid'),
                'config_id'  => $this->product->saved_configuration
            ]);

        echo $this->loadTemplate('service');
    }

    /**
     * Get service parts data.
     *
     * @return  ProductServiceData[]
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getServicePartsData()
    {
        $services = $this->product->getAllConfigParts([
            'groupIds' => [$this->folder->id]
        ]);

        $defPartId = $this->hyper['input']->get('d_pid', 0, 'int');
        if (!isset($services[$defPartId])) {
            throw new Exception(Text::_('COM_HYPERPC_NOT_FOUND_DEFAULT_PART'), 404);
        }

        $itemsData    = [];
        $defPart      = $services[$defPartId];
        $defaultPrice = clone $defPart->getListPrice();
        $productParts = $this->getProductParts();

        $promoCode = $this->hyper['helper']['promocode']->getSessionData();
        $hasPromo  = $promoCode->find('positions.' . $this->product->id) && $promoCode->get('type') !== PromoCode::TYPE_SALE_FIXED;
        $promoRate = $hasPromo ? $this->hyper['helper']['cart']->getPositionRate($this->product) : 0;

        $serviceElement = $defPart->getServiceElement();
        if ($serviceElement instanceof ElementProductServicePercentProductPrice) {
            $defaultPrice = $serviceElement->processPrice($defPart, $productParts);
        }

        /** @var MoyskladService $service */
        foreach ($services as $service) {
            $advantages  = $service->getAdvantages();

            $service->set('is_default', false);
            if ($service->id === $defPartId) {
                $service->set('is_default', true);
            }

            $serviceElement = $service->getServiceElement();
            $priceValue     = clone $service->getPrice();
            if ($serviceElement instanceof ElementProductServicePercentProductPrice) {
                $priceValue = $serviceElement->processPrice($service, $productParts);
            }

            $priceDifference = clone $priceValue;
            $priceDifference
                ->add('-' . $defaultPrice->val());

            if (strpos($promoRate, '%') !== false) {
                $priceDifference->add('-' . $promoRate);
            }

            $partName = $service->name;
            $image    = (array) $service->getRender()->image(self::PART_IMG_WIDTH, self::PART_IMG_HEIGHT);

            $isContentOverriden = $service->isReloadContentForProduct($this->product->id);
            if ($isContentOverriden) {
                $partName = ($service->getParams()->get('reload_content_name')) ? $service->getParams()->get('reload_content_name') : $partName;
            }

            $overrideParam = [];
            if (in_array($this->folder->id, (array) $this->hyper['params']->get('package_group_moysklad', [])) && !$service->get('is_default')) {
                /** @var MeasurementsData $defaultDimensions */
                $defaultDimensions = $this->product->getDefaultDimensions();

                if (in_array($service->id, $this->hyper['params']->get('hyperbox_parts_moysklad', []))) { // current service is hyperbox
                    /** @var MeasurementsData $boxDimensions */
                    $boxDimensions = $this->hyper['helper']['moyskladProduct']->getHyperboxDimensions($this->product);

                    $overrideParam['dimensions'] = $boxDimensions->dimensions;
                    $overrideParam['weight'] = $defaultDimensions->weight + $boxDimensions->weight;
                } else { // current service is not hyperbox
                    $overrideParam['dimensions'] = $defaultDimensions->dimensions;
                    $overrideParam['weight'] = $defaultDimensions->weight;
                }
            }

            $partData = new ProductServiceData([
                'itemKey'            => $service->id,
                'image'              => $image,
                'name'               => $partName,
                'isDefault'          => $service->get('is_default'),
                'isContentOverriden' => $isContentOverriden,
                'priceDifference'    => $priceDifference,
                'priceValue'         => $priceValue,
                'overrideParams'     => $overrideParam,
                'advantages'         => $advantages,
                'fields'             => []
            ]);

            $itemsData[] = $partData;
        }

        return $itemsData;
    }

    /**
     * Get product parts.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getProductParts()
    {
        $partOrder      = $this->hyper['params']->get('product_teaser_parts_order', 'a.product_folder_id ASC');
        $loadFromConfig = ($this->product->saved_configuration) ? true : false;

        return $this->product->getConfigParts(true, $partOrder, true, $loadFromConfig);
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
                'id'       => $this->product->id,
                'moysklad' => true
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
