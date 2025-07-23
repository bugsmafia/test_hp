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

namespace HYPERPC\Joomla\Model\Entity;

use Exception;
use JBZoo\Utils\Slug;
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\ImageHelper;
use JBZoo\SimpleTypes\Type\Money;
use HYPERPC\Object\Product\StockData;
use HYPERPC\Helper\MoyskladStockHelper;
use HYPERPC\Helper\MoyskladStoreHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\Traits\PriceTrait;
use HYPERPC\Render\MoyskladProduct as ProductRender;
use HYPERPC\Joomla\Model\Entity\Traits\AvailabilityTrait;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Traits\ConfigurationTrait;

/**
 * Class MoyskladProduct
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @property    ProductRender $_render
 *
 * @method      ProductRender getRender()
 *
 * @since       2.0
 */
class MoyskladProduct extends Position implements ProductMarker
{
    use PriceTrait, AvailabilityTrait, ConfigurationTrait;

    const PART_IMG_WIDTH   = 780;
    const PART_IMG_HEIGHT  = 439;

    /**
     * Sale status.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $on_sale;

    /**
     * Review part tabs.
     *
     * @var     \JBZoo\Data\JSON
     *
     * @since   2.0
     */
    public $configuration;

    /**
     * Hold vendor code.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $vendor_code;

    /**
     * Virtual field for order.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $quantity = 0;

    /**
     * Virtual field for order.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $saved_configuration;

    /**
     * Get product availability.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAvailability()
    {
        if ($this->hasBalance()) {
            return self::AVAILABILITY_INSTOCK;
        }

        if ((bool) $this->on_sale === false) {
            return self::AVAILABILITY_OUTOFSTOCK;
        }

        return self::AVAILABILITY_PREORDER;
    }

    /**
     * Get availability products by store.
     *
     * @param   null|int $storeId
     *
     * @return  JSON|mixed
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getAvailabilityByStore($storeId = null)
    {
        static $output = [];
        $hash = md5((new JSON([$this->id]))->write());

        if (!array_key_exists($hash, $output)) {
            /** @var MoyskladStockHelper */
            $stockHelper = $this->hyper['helper']['moyskladStock'];
            $stocks = $stockHelper->getItems([
                'itemIds'   => [$this->id],
                'optionIds' => [(int) $this->saved_configuration]
            ]);

            $_items = [];

            /** @var MoyskladStoreHelper */
            $storeHelper = $this->hyper['helper']['moyskladStore'];
            foreach ($stocks as $storeItem) {
                $stockStoreId = $storeHelper->convertToLagacyId((int) $storeItem->store_id);
                if (!array_key_exists($stockStoreId, $_items)) {
                    $_items[$stockStoreId] = ['available' => 0];
                }

                $_items[$stockStoreId]['available'] += $storeItem->balance;
            }

            $output[$hash] = new JSON($_items);
        }

        return ($storeId !== null) ? $output[$hash]->find($storeId . '.available') : $output[$hash];
    }

    /**
     * Get assembly kit part
     *
     * @return  MoyskladPart|null
     *
     * @since   2.0
     */
    public function getAssemblyKit()
    {
        $assemblyKitId = $this->getAssemblyKitId();

        if (empty($assemblyKitId)) {
            return null;
        }

        $assemblyKit = $this->hyper['helper']['moyskladPart']->findById($assemblyKitId);

        if (empty($assemblyKit->id)) {
            return null;
        }

        return $assemblyKit;
    }

    /**
     * Get assembly kit part id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getAssemblyKitId()
    {
        $assemblyKitId = $this->params->get('assembly_kit');
        if (empty($assemblyKitId)) {
            $parent = $this->getFolder();
            $assemblyKitId = $parent->params->get('assembly_kit');
        }

        return (int) $assemblyKitId;
    }

    /**
     * Get helper object.
     *
     * @return  AppHelper
     *
     * @since   2.0
     */
    public function getHelper()
    {
        return $this->hyper['helper']['moyskladProduct'];
    }

    /**
     * Get part helper object.
     *
     * @return  AppHelper
     *
     * @since   2.0
     */
    public function getPartHelper()
    {
        return $this->hyper['helper']['moyskladPart'];
    }

    /**
     * Get folder helper object.
     *
     * @return  AppHelper
     *
     * @since   2.0
     */
    public function getFolderHelper()
    {
        return $this->hyper['helper']['productFolder'];
    }

    /**
     * Get order price by quantity.
     *
     * @param   bool $includeAccessories          Include part price from group of single part params.
     *
     * @return  Money
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getQuantityPrice($includeAccessories = false)
    {
        $price = clone $this->getConfigPrice($includeAccessories);
        if ($this->quantity) {
            $price->multiply($this->quantity);
        }

        return $price;
    }

    /**
     * Get the number of product galleries.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCountOfGalleries()
    {
        $count = 0;
        for ($i = 0; $i < 6; $i++) {
            if (!empty($this->images->get('gallery_' . $i))) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get product gallery images.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGalleryImages()
    {
        $images = [];
        for ($i = 0; $i < 6; $i++) {
            $image = $this->images->get('gallery_' . $i);
            if (!empty($image)) {
                $images['gallery_' . $i] = $image;
            }
        }

        return $images;
    }

    /**
     * Get product images.
     *
     * @param   bool $teaser
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getImages($teaser = false)
    {
        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];
        if ($teaser) {
            $colorVariants = $this->images->get('teaser_color_variants', [], 'arr');

            if (!empty($colorVariants)) {
                $images = [];
                foreach ($colorVariants as $data) {
                    $img = HTMLHelper::_('cleanImageURL', trim($data['image']));
                    if ($data['color'] && $imageHelper->isExistingImage($img->url)) {
                        $images[$data['color']] = '/' . ltrim($img->url, '/');
                    }
                }

                if (!empty($images)) {
                    return $images;
                }
            }

            // For backward compatibility
            $path = $this->images->get('image_teaser', '', 'hpimagepath');
            if ($imageHelper->isExistingImage($path)) {
                return ['/' . ltrim($path, '/')];
            }
        } else {
            $path = $this->images->get('image_full', '', 'hpimagepath');
            if ($imageHelper->isExistingImage($path)) {
                return ['/' . ltrim($path, '/')];
            }
        }

        return [];
    }

    /**
     * Get product name without brand
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getNameWithoutBrand()
    {
        if (preg_match('/^(hyperpc|epix)\s(.+)$/i', $this->name, $matches)) {
            return $matches[2];
        }

        return $this->name;
    }

    /**
     * Get product page name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPageName()
    {
        $name = trim($this->getParams()->get('title', ''));
        if (empty($name)) {
            $name = $this->name;
        }

        $name = HTMLHelper::_('content.prepare', $name);
        $name = str_replace('-', '&#8209;', $name);

        $folder = $this->getFolder();
        switch ($folder->getItemsType()) {
            case 'notebook':
                $name =
                    '<span>' . Text::_('COM_HYPERPC_NOTEBOOK') . '</span> ' .
                    preg_replace('/(.*)PLAY\s(\d{2})(\s.+)?/', '$1PLAY&nbsp;$2 <span>$3</span>', $name);
                break;
            default:
                if (strpos($name, 'HYPERPC ') === 0) {
                    $name = '<span>HYPERPC</span> ' . substr($name, 8);
                }
                break;
        }

        return $name;
    }

    /**
     * Get context for reviews
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getReviewsContext()
    {
        return HP_OPTION . '.position';
    }

    /**
     * Get item dimensions and weight
     *
     * @return  MeasurementsData
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getDimensions(): MeasurementsData
    {
        $parts = $this->getConfigParts(true, 'a.product_folder_id ASC', true);

        $result = $this->getDefaultDimensions($parts);

        $packageGroupId = $this->hyper['params']->get('package_group_moysklad');
        if (array_key_exists((int) $packageGroupId, $parts)) {
            $packagePart = array_shift($parts[$packageGroupId]);
            $hyperboxParts = $this->hyper['params']->get('hyperbox_parts_moysklad', []);

            if ($packagePart instanceof MoyskladPart && in_array((string) $packagePart->id, $hyperboxParts)) {
                $boxDimensions = $this->hyper['helper']['moyskladProduct']->getHyperboxDimensions($this);

                $result->weight += $boxDimensions->weight;
                $result->dimensions = $boxDimensions->dimensions;
            }
        }

        return $result;
    }

    /**
     * Get item default dimensions and weight
     *
     * @return MeasurementsData
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getDefaultDimensions($parts = [])
    {
        $caseGroupId = $this->getCaseGroupId();

        $case           = null;
        $defaultWeight  = 15.0;
        $defaultLength  = 55;
        $defaultWidth   = 30;
        $defaultHeight  = 55;

        if ($this->getFolder()->getItemsType() === 'notebook') {
            $defaultWeight  = 7.0;
            $defaultLength  = 55;
            $defaultWidth   = 35;
            $defaultHeight  = 13;
        }

        $result         = [
            'weight' => $defaultWeight,
            'dimensions' => [
                'length' => $defaultLength,
                'width'  => $defaultWidth,
                'height' => $defaultHeight
            ]
        ];

        if (array_key_exists((int) $caseGroupId, $parts)) {
            $case = array_shift($parts[$caseGroupId]);
        }

        if ($case instanceof MoyskladPart) {
            $result['weight'] = $case->weight > 0 ? $case->weight : $defaultWeight;
            $result['dimensions'] = [
                'length' => $case->length > 0 ? $case->length : $defaultLength,
                'width'  => $case->width > 0 ? $case->width : $defaultWidth,
                'height' => $case->height > 0 ? $case->height : $defaultHeight
            ];
        }

        return new MeasurementsData($result);
    }

    /**
     * Get cache group for product configurator and single page.
     *
     * @return  bool|string
     *
     * @since   2.0
     */
    public function getCacheGroup()
    {
        $enableCache = $this->hyper['params']->get('product_cache', true, 'bool');
        if ($enableCache === true) {
            return $this->getCacheKey();
        }

        return false;
    }

    /**
     * Get product cache key.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCacheKey()
    {
        return HP_CACHE_POSITION_GROUP . '_' . Slug::filter($this->name, '_') . '_' . $this->id;
    }

    /**
     * Get moysklad product name.
     *
     * @param   bool $configLink
     *
     * @return  string
     *
     * @throws  Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getName($configLink = false)
    {
        $configuration = $this->getConfiguration();
        if ($configuration->id > 0) {
            $name = Text::_('COM_HYPERPC_NUM') . $configuration->getName();
            if ($configLink === true) {
                $name = sprintf('<a href="%s">%s</a>', $this->getConfigUrl($configuration->id), $name);
            }

            return $this->name . ' (' . Text::_('COM_HYPERPC_SPECIFICATION') . ' ' . $name . ')';
        }

        return $this->name;
    }

    /**
     * Get min and max days to build string
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getDaysToBuildStr()
    {
        $result = $this->getDaysToBuild();

        if ($result['min'] === $result['max']) {
            return $result['min'];
        }

        return $result['min'] . ' - ' . $result['max'];
    }

    /**
     * Get item key
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey()
    {
        $key = parent::getItemKey();

        if ($this->saved_configuration) {
            $key .= '-' . $this->saved_configuration;
        }

        return $key;
    }

    /**
     * Get site view url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [], $isFull = false)
    {
        $options = [
            'view' => 'moysklad_product',
            'id' => $this->id,
            'product_folder_id' => $this->product_folder_id,
        ];

        if ($this->isFromStock()) {
            $options = [
                'view'    => 'product_in_stock',
                'id'      => $this->getStockConfigurationId(),
                'context' => HP_OPTION . '.position'
            ];
        }

        return $this->hyper['route']->build(array_replace($query, $options), $isFull);
    }

    /**
     * Check has balance.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasBalance()
    {
        return $this->isFromStock();
    }

    /**
     * Get stock configuration id
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getStockConfigurationId()
    {
        if ($this->isFromStock()) {
            return (string) $this->saved_configuration;
        }

        return '';
    }

    /**
     * Get stock store id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getStockStoreId()
    {
        if ($this->isFromStock()) {
            return $this->params->get('stock')->storeId;
        }

        return 0;
    }

    /**
     * Get openGraph image url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOGImage()
    {
        if ($this->hasNonDefaultImageParts()) {
            $image = $this->getConfigurationImage();
            if (!empty($image)) {
                return $image->getUrl();
            }
        }

        $ogImagePath = $this->images->get('image_og', '', 'hpimagepath');
        if (empty($ogImagePath)) {
            $ogImagePath = $this->images->get('image_teaser', '', 'hpimagepath');
        }

        return !empty($ogImagePath) ? Uri::root() . ltrim($ogImagePath, '/') : '';
    }

    /**
     * Get product price list image url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPriceListImage()
    {
        $imagePath = Uri::root() . ltrim($this->images->get('image_y_market', '', 'hpimagepath'), '/');
        if (!$this->hasNonDefaultImageParts()) {
            return $imagePath;
        }

        $image = $this->getConfigurationImage();
        if (!empty($image)) {
            return $image->getUrl();
        }

        return $imagePath;
    }

    /**
     * Checks if the product has stock data
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isFromStock()
    {
        $stockData = $this->params->get('stock');
        if ($stockData instanceof StockData) {
            return $stockData->storeId && $stockData->balance;
        }

        $inStockNow = $this->hyper['helper']['moyskladStock']->getItems([
            'itemIds'   => [$this->id],
            'optionIds' => [(int) $this->saved_configuration]
        ]);

        return count($inStockNow) > 0;
    }

    /**
     * Check if position can buy.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOnSale()
    {
        return (bool) $this->on_sale;
    }

    /**
     * Is in cart
     *
     * @return  boolean
     *
     * @since   2.0
     */
    public function isInCart()
    {
        $cartItems = $this->hyper['helper']['cart']->getSessionItems();
        $itemKey = $this->getItemKey();

        return array_key_exists($itemKey, $cartItems);
    }

    /**
     * Get customization group ids
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCaseGroupId()
    {
        return $this->hyper['params']->get('cases_folder', 0, 'int');
    }

    /**
     * Get customization group ids
     *
     * @return  int[]
     *
     * @since   2.0
     */
    public function getCustomizationGroupIds()
    {
        $ids = $this->hyper['params']->get('product_customization_folders', [], 'arr');
        return array_map(function ($id) {
            return (int) $id;
        }, $ids);
    }

    /**
     * Fields of boolean data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldBoolean()
    {
        $parentFields = parent::_getFieldBoolean();
        return array_merge(
            ['on_sale'],
            $parentFields
        );
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        $parentFields = parent::_getFieldInt();
        return array_merge(
            ['quantity'],
            $parentFields
        );
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return array_merge(parent::_getFieldJsonData(), ['configuration']);
    }
}
