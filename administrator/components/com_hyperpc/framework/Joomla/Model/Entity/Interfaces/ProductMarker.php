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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Joomla\Model\Entity\Interfaces;

use JBZoo\Data\Data;
use JBZoo\Data\JSON;
use JBZoo\Image\Image;
use HYPERPC\Helper\AppHelper;
use JBZoo\SimpleTypes\Exception;
use JBZoo\SimpleTypes\Type\Money;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Render\MoyskladProduct as MoyskladProductRender;

/**
 * Interface ProductMarker
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface ProductMarker extends Stockable, Priceable, Categorizable, Entity
{
    /**
     * Get all configurator items.
     *
     * @param   array $params
     *
     * @return  (PartMarker|MoyskladService)[]
     *
     * @since   2.0
     */
    public function getAllConfigParts(array $params = []);

    /**
     * Get cache group for product configurator and single page.
     *
     * @return  bool|string
     *
     * @since   2.0
     */
    public function getCacheGroup();

    /**
     * Get product cache key.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getCacheKey();

    /**
     * Get product's barcodes by type
     *
     * @param   string $type
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getBarcodesByType(string $type);

    /**
     * Get configuration price.
     *
     * @param   bool $includeAccessories    Include part price from group of single part params.
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getConfigPrice($includeAccessories = false);

    /**
     * Get part list by config.
     *
     * @param   bool    $compactByGroup        Compact part by group.
     * @param   string  $partOrder             Part SQL query order.
     * @param   bool    $reOrder               Flag of reorder result
     * @param   bool    $partFormConfig        Flag of load parts from saved configuration or product.
     * @param   bool    $loadUnavailableParts  Load parts which availability is outofstock or uncontinued.
     *
     * @return  (PartMarker|MoyskladService)[]
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getConfigParts($compactByGroup = true, $partOrder = 'a.product_folder_id ASC', $reOrder = false, $partFormConfig = false, $loadUnavailableParts = false);

    /**
     * Get product configuration url.
     *
     * @param   int     $configId   Saved configuration id.
     * @param   string  $configuratorType
     * @param   array   $query
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getConfigUrl($configId = 0, $configuratorType = null, array $query = []);

    /**
     * Get product saved configuration.
     *
     * @param   bool  $isNew
     *
     * @return  SaveConfiguration
     *
     * @since   2.0
     */
    public function getConfiguration($isNew = false);

    /**
     * Get case group id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCaseGroupId();

    /**
     * Get customization group ids
     *
     * @return  int[]
     *
     * @since   2.0
     */
    public function getCustomizationGroupIds();

    /**
     * Get min and max days to build
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getDaysToBuild();

    /**
     * Get min and max days to build string
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getDaysToBuildStr();

    /**
     * Get default part option.
     *
     * @param   PartMarker      $part
     * @param   OptionMarker[]  $options
     * @param   bool            $checkSession
     *
     * @return  OptionMarker
     *
     * @since   2.0
     */
    public function getDefaultPartOption(PartMarker $part, array $options = []);

    /**
     * Get configuration image object
     *
     * @var     int $imageMaxWidth
     * @var     int $imageMaxHeight
     *
     * @return  Image|null
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\Image\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @todo    image for saved configuration
     */
    public function getConfigurationImage($imageMaxWidth = 0, $imageMaxHeight = 0);

    /**
     * Get configuration image path
     *
     * @var     int $imageMaxWidth
     * @var     int $imageMaxHeight
     *
     * @return  string path to image
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\Image\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getConfigurationImagePath($imageMaxWidth = 0, $imageMaxHeight = 0);

    /**
     * Get configuration image from certain part
     *
     * @param   PartMarker $part
     * @param   int        $imageMaxWidth
     * @param   int        $imageMaxHeight
     *
     * @return  array|null
     *
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function getConfigurationImageFromPart(PartMarker $part, $imageMaxWidth = 0, $imageMaxHeight = 0);

    /**
     * Get the number of product galleries.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCountOfGalleries();

    /**
     * Get product images.
     *
     * @param   bool $teaser
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getImages($teaser = false);

    /**
     * Get product page name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPageName();

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams();

    /**
     * Get part options
     *
     * @param   PartMarker $part
     *
     * @return  OptionMarker[]
     *
     * @since   2.0
     */
    public function getPartOptions(PartMarker $part);

    /**
     * Get context for reviews
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getReviewsContext();

    /**
     * Set option from product configuration to the part object
     *
     * @param   PartMarker $part
     *
     * @since   2.0
     */
    public function setOptionFromConfigInPart(PartMarker &$part);

    /**
     * Get render object.
     *
     * @return  MoyskladProductRender|null
     *
     * @since   2.0
     */
    public function getRender();

    /**
     * Get helper object.
     *
     * @return  AppHelper
     *
     * @since   2.0
     */
    public function getHelper();

    /**
     * Get part helper object.
     *
     * @return  AppHelper
     *
     * @since   2.0
     */
    public function getPartHelper();

    /**
     * Get folder helper object.
     *
     * @return  AppHelper
     *
     * @since   2.0
     */
    public function getFolderHelper();

    /**
     * Get product name (with configuration number if set).
     *
     * @param   bool $configLink
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName($configLink = false);

    /**
     * Get product name without brand
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getNameWithoutBrand();

    /**
     * Get data for render group parts in quick configurator.
     *
     * @param   ProductFolder
     *
     * @return  ProductPartData[]
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getGroupPartsData($folder);

    /**
     * Get default parts option list.
     *
     * @param   bool $compactByPart     Compact options by part.
     *
     * @return  JSON
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function findDefaultPartsOptions($compactByPart = false);

    /**
     * Is mini configurator available in group
     *
     * @param   ProductFolder $groupId
     * @param   Position      $defaultPart
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isMiniConfiguratorAvailableInGroup($group, $defaultPart);

    /**
     * Get product folder or category.
     *
     * @return  ProductFolder
     *
     * @since   2.0
     */
    public function getFolder();

    /**
     * Get product folder or category id.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getFolderId();

    /**
     * Get product gallery images.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGalleryImages();

    /**
     * Get item key
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey();

    /**
     * Get stock configuration id
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getStockConfigurationId();

    /**
     * Get stock store id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getStockStoreId();

    /**
     * Is product published
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPublished();

    /**
     * Check mini part and options.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function hasPartsMini();

    /**
     * Checks if the product has stock data
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isFromStock();

    /**
     * Convert product data to save configuration data.
     *
     * @return  Data
     *
     * @throws  \Exception
     * @throws  Exception
     *
     * @since   2.0
     */
    public function toSaveConfiguration();

    /**
     * Get openGraph image url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOGImage();

    /**
     * Get product price list image url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPriceListImage();
}
