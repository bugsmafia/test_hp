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

namespace HYPERPC\XML\PriceList\Elements;

use Joomla\CMS\Uri\Uri;
use JBZoo\Utils\Exception;
use HYPERPC\Elements\ElementPriceList;
use HYPERPC\Object\PriceList\OfferData;
use HYPERPC\Object\PriceList\PriceListData;
use HYPERPC\Object\PriceList\OfferCollection;
use HYPERPC\Object\PriceList\OfferParamsCollection;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * Class YMLPriceList
 *
 * @since 2.0
 */
class YMLPriceList extends ElementPriceList
{
    /**
     * Get price list data object
     *
     * @return  PriceListData
     *
     * @since   2.0
     */
    protected function _getPriceListData(): PriceListData
    {
        $categoryList = [];

        $offers = array_merge(
            $this->_getProductOffers($categoryList)->items(),
            $this->_getPartOffers($categoryList)->items(),
            $this->_getServiceOffers($categoryList)->items()
        );

        return new PriceListData([
            'currencyId' => $this->_currency,
            'categories' => $this->_getCategoryCollection($categoryList),
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
     * @param   array $categoryList [$typeId => CategoryMarker[]]
     *
     * @return  OfferCollection
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  Exception
     *
     * @since   2.0
     */
    private function _getProductOffers(array &$categoryList): OfferCollection
    {
        $offers = [];

        $products   = $this->_getProducts();
        $categories = $this->_getCategories();
        foreach ($products as $product) {
            $category   = $categories[$product->getFolderId()];
            $itemsType  = strtoupper($category->getItemsType());
            $categoryId = (int) (TypeId::PRODUCTS_TYPE_ID . constant(TypeId::class . "::PRODUCTS_TYPE_{$itemsType}_ID") . $category->id);

            $categoryList[TypeId::PRODUCTS_TYPE_ID][$categoryId] = $category;

            $offerSalePrice = (int) $product->getSalePrice()->val();
            $offerListPrice = (int) $product->getListPrice()->val();

            $rewriteAvailability = $this->getConfig('rewrite_availability', false, 'bool');

            $offerData = [
                'id'            => $product->getItemKey(),
                'title'         => $this->_getProductTitle($product),
                'description'   => $this->_getProductDescription($product),
                'link'          => Uri::root() . trim($product->getViewUrl(), '/'),
                'imageLink'     => $product->getPriceListImage(),
                'price'         => $offerSalePrice,
                'availability'  => $rewriteAvailability ? Stockable::AVAILABILITY_INSTOCK : $product->getAvailability(),
                'categoryId'    => $categoryId,
                'measurements'  => $product->getDimensions(),
                'params'        => new OfferParamsCollection($this->_getProductParams($product))
            ];

            if ($offerListPrice !== $offerSalePrice) {
                $offerData['oldPrice'] = $offerListPrice;
            }

            if ($this->getConfig('yml_type') === self::YML_TYPE_COMBINED) {
                $offerData['vendor'] = self::VENDOR;
                $offerData['model'] = $this->_getProductModel($product);
                $offerData['typePrefix'] = $this->_getProductTypePrefix($product);
            }

            if ($utp = $category->get('metadata')->utp) {
                $offerData['salesNotes'] = $utp;
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
     * @param   array $categoryList [$typeId => CategoryMarker[]]
     *
     * @return  OfferCollection
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    private function _getPartOffers(&$categoryList): OfferCollection
    {
        $offers = [];

        $parts      = $this->_getParts();
        $partGroups = $this->_getPartGroups();
        foreach ($parts as $part) {
            $group = $partGroups[$part->getFolderId()];
            $groupId = (int) (TypeId::PARTS_TYPE_ID . $group->id);

            $categoryList[TypeId::PARTS_TYPE_ID][$groupId] = $group;

            $viewPath     = $part->getViewUrl();
            $description  = $part->getParams()->get('short_desc');
            $vendorCode   = $part->vendor_code;
            $availability = $part->getAvailability();

            if ($part->option instanceof OptionMarker) {
                $viewPath     = $part->option->getViewUrl();
                $availability = $part->option->getAvailability();
                $description  = $part->option->getParams()->get('short_desc') ?: $description;
                $vendorCode   = $part->option->vendor_code;
            }

            $groupTitle = $this->_getPartTypePrefix($part);
            $partName = htmlspecialchars($part->getName());

            $offerSalePrice = (int) $part->getSalePrice()->val();
            $offerListPrice = (int) $part->getListPrice()->val();

            $offerData = [
                'id'            => $part->getItemKey(),
                'title'         => $groupTitle . ' ' . $partName,
                'vendorCode'    => $vendorCode,
                'description'   => htmlspecialchars(trim(strip_tags($description))),
                'link'          => Uri::root() . ltrim($viewPath, '/'),
                'imageLink'     => $part->getPriceListImage(),
                'price'         => $offerSalePrice,
                'availability'  => $availability,
                'categoryId'    => $groupId,
                'measurements'  => $part->getDimensions()
            ];

            if ($offerListPrice !== $offerSalePrice) {
                $offerData['oldprice'] = $offerListPrice;
            }

            if ($this->getConfig('yml_type') === self::YML_TYPE_COMBINED) {
                $vendor = null;
                $model = preg_replace_callback($this->_getVendorsListRegex(), function ($matches) use (&$vendor) {
                    $vendor = $matches[0];
                    return '';
                }, $partName, 1);

                if ($vendor && $model) {
                    $offerData['vendor'] = $vendor;
                    $offerData['model'] = trim(preg_replace('/\s{2}/', ' ', $model));
                    $offerData['typePrefix'] = $groupTitle;
                }
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
     * @param   array $categoryList [$typeId => CategoryMarker[]]
     *
     * @return  OfferCollection
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    private function _getServiceOffers(&$categoryList): OfferCollection
    {
        $offers = [];

        $services      = $this->_getServices();
        $serviceGroups = $this->_getServiceFolders();
        foreach ($services as $service) {
            $group = $serviceGroups[$service->getFolderId()];
            $groupId = (int) (TypeId::SERVICES_TYPE_ID . $group->id);

            $categoryList[TypeId::SERVICES_TYPE_ID][$groupId] = $group;

            $description = $service->getParams()->get('short_desc');

            $offerSalePrice = (int) $service->getSalePrice()->val();
            $offerListPrice = (int) $service->getListPrice()->val();

            $offers[] = new OfferData([
                'id'                   => $service->getItemKey(),
                'title'                => $service->getConfigurationName(),
                'description'          => htmlspecialchars(trim(strip_tags($description))),
                'link'                 => $service->getViewUrl([], true),
                'imageLink'            => Uri::root() . ltrim($this->hyper['helper']['image']->getPlaceholderPath(), '/'), /** @todo image for service */
                'price'                => $offerSalePrice,
                'categoryId'           => $groupId,
                'delivery'             => false,
                'manufacturerWarranty' => false
            ]);

            if ($offerListPrice !== $offerSalePrice) {
                $offerData['oldprice'] = $offerListPrice;
            }
        }

        $offersCollection = new OfferCollection($offers);

        return $this->_postprocessServiceOffers($offersCollection);
    }
}
