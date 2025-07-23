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

namespace HYPERPC\Object\PriceList;

use Joomla\CMS\Date\Date;
use HYPERPC\Object\Delivery\MeasurementsData;
use Spatie\DataTransferObject\DataTransferObject;

class OfferData extends DataTransferObject
{
    /**
     * Offer item id
     */
    public string $id;

    /**
     * Offer item title
     */
    public string $title;

    /**
     * Offer item short title
     */
    public ?string $shortTitle;

    /**
     * Offer description
     */
    public string $description = '';

    /**
     * Offer link
     */
    public string $link;

    /**
     * Offer image link
     */
    public string $imageLink;

    /**
     * Offer price
     */
    public int $price;

    /**
     * Offer price without discount
     */
    public ?int $oldPrice;

    /**
     * Minimum offer price (for ozon feeds)
     */
    public ?int $minPrice;

    /**
     * Vendor name
     */
    public ?string $vendor;

    /**
     * Offer item availability
     */
    public ?string $availability;

    /**
     * Offer item availablity date
     */
    public ?Date $availabilityDate;

    /**
     * Item condition
     */
    public string $condition = 'new';

    /**
     * Offer item model
     */
    public ?string $model;

    /**
     * Offer item category id
     */
    public int $categoryId;

    /**
     * Manufacturer warranty
     */
    public bool $manufacturerWarranty = true;

    /**
     * Offer item delivery
     */
    public bool $delivery = true;

    /**
     * Offer item pickup
     */
    public bool $pickup = true;

    /**
     * Vendor code
     */
    public ?string $vendorCode;

    /**
     * Barcodes
     */
    public ?string $barcode;

    /**
     * Offer type prefix
     */
    public ?string $typePrefix;

    /**
     * Offer sales notes
     */
    public ?string $salesNotes;

    /**
     * Google product category
     */
    public ?int $googleProductCategory;

    /**
     * Measurements data
     */
    public ?MeasurementsData $measurements;

    /**
     * Offer params
     */
    public ?OfferParamsCollection $params;
}
