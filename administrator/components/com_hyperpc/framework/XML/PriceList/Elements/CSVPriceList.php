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

namespace HYPERPC\XML\PriceList\Elements;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use JBZoo\Utils\Exception;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CsvHelper;
use Joomla\CMS\Filesystem\File;
use HYPERPC\Elements\ElementPriceList;
use HYPERPC\Object\PriceList\OfferData;
use HYPERPC\Object\PriceList\PriceListData;
use HYPERPC\Object\PriceList\OfferCollection;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * Class AtomPriceList
 *
 * @since 2.0
 */
class CSVPriceList extends ElementPriceList
{
    protected const FORMAT       = 'csv';

    protected const IN_STOCK     = 'in_stock';
    protected const OUT_OF_STOCK = 'out_of_stock';
    protected const PREORDER     = 'backorder';

    /**
     * Get price list data object
     *
     * @return  PriceListData
     *
     * @throws  Exception
     * @throws  \JBZoo\SimpleTypes\Exception
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
     * Element edit common fields.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _commonEditFields()
    {
        $commonFields = parent::_commonEditFields();

        $commonFields['separator_csv'] = [
            'type'  => 'hpseparator',
            'title' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SEPARATOR_CSV'
        ];

        $commonFields['purchase_price_factor'] = [
            'type'        => 'number',
            'min'         => '0',
            'max'         => '1',
            'step'        => '0.001',
            'default'     => '1',
            'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PURCHASE_PRICE_FACTOR_LABEL',
            'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PURCHASE_PRICE_FACTOR_DESC'
        ];

        $commonFields['csv_fields'] = [
            'type'       => 'subform',
            'label'      => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_CSV_FIELDS_LABEL',
            'formsource' => 'administrator/components/com_hyperpc/models/forms/subforms/csv_fields.xml',
            'multiple'   => 'true',
            'layout'     => 'joomla.form.field.subform.repeatable-table'
        ];

        return $commonFields;
    }

    /**
     * Get default file name
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getDefaultFileName()
    {
        return 'price_list.csv';
    }

    /**
     * Export price list to file.
     *
     * @return  void
     *
     * @throws  Exception|\JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function export()
    {
        if (!$this->getConfig('state', true, 'bool')) {
            return;
        }

        $fileName  = $this->_getFileName();
        $ext = File::getExt($fileName);
        if (empty($ext) || $ext !== 'csv') {
            $fileName .= '.csv';
        }

        $csvFields = $this->getConfig('csv_fields', []);
        $csvHead   = [];
        $csvData   = [];

        foreach ($this->_getPriceListData()->offers as $offer) {
            foreach ($csvFields as $field) {
                $fieldName   = $field['field_name'];
                $entityField = $field['entity_field'];

                if (!in_array($fieldName, $csvHead)) {
                    $csvHead[] = $fieldName;
                }

                if (property_exists($offer, $entityField)) {
                    $data[$fieldName] = strval($offer->$entityField);
                }

                if ($entityField === 'emptyField') {
                    $data[$fieldName] = '';
                }

                if ($entityField === 'purchasePrice') {
                    $data[$fieldName] = ceil($offer->price * $this->getConfig('purchase_price_factor', 1.0, 'float'));
                }
            }

            if (!empty($data)) {
                $csvData[] = $data;
            }
        }

        if (!empty($csvHead)) {
            array_unshift($csvData, $csvHead);
        }

        /** @var CsvHelper */
        $csvHelper = $this->hyper['helper']['csv'];

        $csvHelper->setMode('w+');
        $csvHelper->toFile($fileName, $csvData);
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
            ];

            if (!empty($product->vendor_code)) {
                $offerData['vendorCode'] = $product->vendor_code;
            }

            if ($this->getConfig('google_category_id', false, 'bool') === true) {
                $offerData['googleProductCategory'] = $category->getGoogleId();
            }

            if ($this->getConfig('show_product_type', false, 'bool') === true) {
                $itemsType                = $category->getItemsType();
                $offerData['productType'] = Text::_("COM_HYPERPC_PRODUCT_TYPE_{$itemsType}");
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

            $offerData = [
                'id'            => $part->getItemKey(),
                'title'         => $groupTitle . ' ' . $partName,
                'vendorCode'    => $vendorCode,
                'description'   => htmlspecialchars(trim(strip_tags($description))),
                'link'          => Uri::root() . ltrim($viewPath, '/'),
                'imageLink'     => $part->getPriceListImage(),
                'price'         => (int) $part->getListPrice()->val(),
                'availability'  => $availability,
                'categoryId'    => $groupId,
                'measurements'  => $part->getDimensions()
            ];

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

            $offers[] = new OfferData([
                'id'                   => $service->getItemKey(),
                'title'                => $service->getConfigurationName(),
                'description'          => htmlspecialchars(trim(strip_tags($description))),
                'link'                 => $service->getViewUrl([], true),
                'imageLink'            => Uri::root() . ltrim($this->hyper['helper']['image']->getPlaceholderPath(), '/'), /** @todo image for service */
                'price'                => (int) $service->getListPrice()->val(),
                'categoryId'           => $groupId,
                'delivery'             => false,
                'manufacturerWarranty' => false
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
