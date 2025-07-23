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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\Joomla\Model\Entity\Interfaces;

use JBZoo\SimpleTypes\Type\Money;
use HYPERPC\Render\Part as PartRender;
use HYPERPC\Render\MoyskladPart as MoyskladPartRender;

/**
 * Interface PartMarker
 *
 * @package HYPERPC\Joomla\Model\Entity\Interfaces
 *
 * @since   2.0
 */
interface PartMarker extends Stockable, Priceable, Categorizable, Entity
{
    const PART_IMG_WIDTH   = 780;
    const PART_IMG_HEIGHT  = 439;

    /**
     * Get part configurator name.
     *
     * @param   mixed $productId
     * @param   bool $considerOption
     * @param   bool $considerQuantity
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfiguratorName($productId = null, $considerOption = false, $considerQuantity = false);

    /**
     * Get part's barcodes by type
     *
     * @param   string $type
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getBarcodesByType(string $type);

    /**
     * Get export image path.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getExportImage();

    /**
     * Get parent group id
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getGroupId(): int;

    /**
     * Get item key
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey();

    /**
     * Get default option.
     *
     * @return  OptionMarker
     *
     * @since   2.0
     */
    public function getDefaultOption(): OptionMarker;

    /**
     * Get default option id.
     *
     * @return  bool|int
     *
     * @since   2.0
     */
    public function getDefaultOptionId();

    /**
     * Get part advantages array
     *
     * @return array
     *
     * @since 2.0
     */
    public function getAdvantages();

    /**
     * Get part price by quantity for order.
     *
     * @param   bool $checkRate
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getQuantityPrice($checkRate = true);

    /**
     * Get part name with option name
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName();

    /**
     * Get part options
     *
     * @todo refactor code and use loadFields and archive
     *
     * @param   bool $loadFields    Flag of load option fields.
     * @param   bool $archive       Load archive options.
     *
     * @return  OptionMarker[]
     *
     * @since   2.0
     */
    public function getOptions($loadFields = false, $archive = true);

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams();

    /**
     * Get product price list image url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPriceListImage();

    /**
     * Get render object.
     *
     * @return  PartRender|MoyskladPartRender|null
     *
     * @since   2.0
     */
    public function getRender();

    /**
     * Get image assembled
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getImageAssembled();

    /**
     * Get sorting review array.
     *
     * @param   string $sorting
     * @param   string $order
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getReview($order = 'asc', $sorting = '{n}.sorting');

    /**
     * Site view part url.
     *
     * @param   array   $query
     * @param   bool    $isFull
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [], $isFull = false);

    /**
     * Check part is archived
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isArchived();

    /**
     * Take out part from the configuration?
     *
     * @return bool
     *
     * @since   2.0
     */
    public function isDetached();

    /**
     * Check if the part can be bought.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isForRetailSale();

    /**
     * Is part available only for upgrade.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOnlyForUpgrade();

    /**
     * Check part is published
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPublished();

    /**
     * Check is reload content form product by id.
     *
     * @param   int $productId
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isReloadContentForProduct(int $productId);

    /**
     * Check part is trashed
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isTrashed();

    /**
     * Prepare folders. Add primary parent folder in list.
     *
     * @param   CategoryMarker[] $folderList This list of group entity.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function prepareGroups(array $folderList = []);
}
