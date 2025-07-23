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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\XML\PriceList\Elements;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use HYPERPC\Elements\ElementPriceList;
use HYPERPC\Object\PriceList\OfferData;
use HYPERPC\Object\PriceList\PriceListData;
use HYPERPC\Object\PriceList\OfferCollection;
use HYPERPC\Object\PriceList\OfferParamsCollection;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * Class AtomPriceList
 *
 * @since 2.0
 */
class AtomPriceList extends ElementPriceList
{

    protected const FORMAT = 'atom';

    protected const IN_STOCK     = 'in_stock';
    protected const OUT_OF_STOCK = 'out_of_stock';
    protected const PREORDER     = 'backorder';

    /**
     * Get price list data object
     *
     * @return  PriceListData
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getPriceListData(): PriceListData
    {
        $offers = array_merge(
            $this->_getProductOffers()->items(),
            $this->_getPartOffers()->items(),
            $this->_getServiceOffers()->items()
        );

        return new PriceListData([
            'currencyId' => $this->_currency,
            'offers'     => new OfferCollection($offers)
        ]);
    }

    /**
     * Method for post-process part offers in a child class
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessPartOffers(OfferCollection $offers): OfferCollection
    {
        return $offers;
    }

    /**
     * Method for post-process product offers in a child class
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessProductOffers(OfferCollection $offers): OfferCollection
    {
        return $offers;
    }

    /**
     * Method for post-process service offers in a child class
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessServiceOffers(OfferCollection $offers): OfferCollection
    {
        return $offers;
    }

    /**
     * Get product offers collection
     *
     * @return  OfferCollection
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    private function _getProductOffers(): OfferCollection
    {
        $offers = [];

        $products   = $this->_getProducts();
        foreach ($products as $product) {
            $category   = $product->getFolder();
            $itemsType  = strtoupper($category->getItemsType());
            $categoryId = (int) (TypeId::PRODUCTS_TYPE_ID . constant(TypeId::class . "::PRODUCTS_TYPE_{$itemsType}_ID") . $category->id);

            $availability = $this->_getAvailability($product);

            $offerData = [
                'id'            => $product->getItemKey(),
                'title'         => $this->_getProductTitle($product),
                'shortTitle'    => $this->_getProductShortTitle($product),
                'description'   => $this->_getProductDescription($product),
                'link'          => Uri::root() . trim($product->getViewUrl(), '/'),
                'imageLink'     => $product->getPriceListImage(),
                'categoryId'    => $categoryId,
                'price'         => (int) $product->getListPrice()->val(),
                'vendor'        => self::VENDOR,
                'availability'  => $availability,
                'measurements'  => $product->getDimensions(),
                'params'        => new OfferParamsCollection($this->_getProductParams($product)),
            ];

            if (!empty($product->vendor_code)) {
                $offerData['vendorCode'] = $product->vendor_code;
            }

            if ($this->getConfig('google_category_id', false, 'bool') === true) {
                $offerData['googleProductCategory'] = $category->getGoogleId();
            }

            if ($this->getConfig('show_product_type', false, 'bool') === true) {
                $offerData['typePrefix'] = $this->_getProductTypePrefix($product);
            }

            if ($availability === static::PREORDER) {
                $daysToBuild = $product->getDaysToBuild();
                if (isset($daysToBuild['max'])) {
                    $readyDate = new Date(date('Y-m-d\TH:i:sP', strtotime(date('M d Y') . ' +' . $daysToBuild['max'] . ' day+10hours')));
                    $offerData['availabilityDate'] = $readyDate;
                }
            }

            $this->_getBarcode($product, $offerData);

            $offers[] = new OfferData($offerData);
        }

        $offersCollection = new OfferCollection($offers);

        return $this->_postprocessProductOffers($offersCollection);
    }

    /**
     * Get part offers collection
     *
     * @return  OfferCollection
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    private function _getPartOffers(): OfferCollection
    {
        $offers = [];

        $parts  = $this->_getParts();
        foreach ($parts as $part) {
            $viewPath     = $part->getViewUrl();
            $description  = $part->getParams()->get('short_desc');
            $availability = $this->_getAvailability($part);

            if ($part->option instanceof OptionMarker) {
                $viewPath     = $part->option->getViewUrl();
                $description  = $part->option->getParams()->get('short_desc') ?: $description;
                $availability = $this->_getAvailability($part->option);
            }

            $groupTitle = $this->_getPartTypePrefix($part);
            $partName = htmlspecialchars($part->getName());

            $offerData = [
                'id'            => $part->getItemKey(),
                'title'         => $groupTitle . ' ' . $partName,
                'description'   => htmlspecialchars(trim(strip_tags($description))),
                'categoryId'    => (int) (TypeId::PARTS_TYPE_ID . $part->getFolderId()),
                'link'          => Uri::root() . ltrim($viewPath, '/'),
                'imageLink'     => $part->getPriceListImage(),
                'price'         => (int) $part->getListPrice()->val(),
                'availability'  => $availability,
                'measurements'  => $part->getDimensions()
            ];

            if (preg_match($this->_getVendorsListRegex(), $partName, $matches)) {
                $offerData['vendor'] = $matches[0];
            }

            $this->_getBarcode($part, $offerData);

            $offers[] = new OfferData($offerData);
        }

        $offersCollection = new OfferCollection($offers);

        return $this->_postprocessPartOffers($offersCollection);
    }

    /**
     * Get service offers collection
     *
     * @return  OfferCollection
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    private function _getServiceOffers(): OfferCollection
    {
        $offers = [];

        $services = $this->_getServices();
        foreach ($services as $service) {
            $description = $service->getParams()->get('short_desc');

            $offers[] = new OfferData([
                'id'            => $service->getItemKey(),
                'title'         => $service->getConfigurationName(),
                'description'   => htmlspecialchars(trim(strip_tags($description))),
                'categoryId'    => (int) (TypeId::SERVICES_TYPE_ID . $service->getFolderId()),
                'link'          => $service->getViewUrl([], true),
                'imageLink'     => Uri::root() . ltrim($this->hyper['helper']['image']->getPlaceholderPath(), '/'), /** @todo image for service */
                'price'         => (int) $service->getListPrice()->val(),
                'availability'  => static::IN_STOCK,
                'delivery'      => false,
            ]);
        }

        $offersCollection = new OfferCollection($offers);

        return $this->_postprocessServiceOffers($offersCollection);
    }

    /**
     * Get availability status for items
     *
     * @param   Stockable $item
     *
     * @return  string
     */
    private function _getAvailability($item)
    {
        $availability = static::IN_STOCK;

        switch ($item->getAvailability()) {
            case Stockable::AVAILABILITY_DISCONTINUED:
            case Stockable::AVAILABILITY_OUTOFSTOCK:
                $availability = static::OUT_OF_STOCK;
                break;
            case Stockable::AVAILABILITY_PREORDER:
                $availability = static::PREORDER;
                break;
        }

        return $availability;
    }
}
