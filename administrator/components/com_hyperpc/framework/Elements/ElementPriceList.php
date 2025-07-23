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

namespace HYPERPC\Elements;

use JBZoo\Utils\Exception;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoneyHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;
use HYPERPC\Helper\MoyskladPartHelper;
use HYPERPC\Helper\MoyskladStockHelper;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Helper\MoyskladServiceHelper;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Object\PriceList\PriceListData;
use HYPERPC\Object\PriceList\OfferParamData;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\XML\PriceList\Elements\CategoryTrait;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\XML\PriceList\Elements\PriceListInterface;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * Class ElementPriceList
 *
 * @since   2.0
 */
abstract class ElementPriceList extends Element implements PriceListInterface
{
    use CategoryTrait;

    private const CONTEXT_MOYSKLAD = 'moysklad';

    protected const FORMAT              = 'yml';
    protected const YML_TYPE_SIMPLIFIED = 'simplified';
    protected const YML_TYPE_COMBINED   = 'combined';

    private const OFFERS_KIND_CATALOG = 'catalog';
    private const OFFERS_KIND_STOCK   = 'stock';

    protected const VENDOR   = 'HYPERPC';

    private string $_categoriesFieldType;
    private string $_groupsFieldType;
    private string $_partFieldsContext;

    private string $_vendorsListRegex;

    /**
     * @var MoyskladProductHelper
     */
    protected $_productHelper;

    /**
     * @var MoyskladPartHelper
     */
    protected $_partHelper;

    /**
     * @var MoyskladServiceHelper
     */
    protected $_serviceHelper;

    /**
     * @var ProductFolderHelper
     */
    protected $_groupHelper;

    /**
     * @var ProductFolderHelper
     */
    protected $_categoryHelper;

    /**
     * @var MoyskladStockHelper
     */
    private $_stockHelper;

    /**
     * @var string
     */
    protected string $_currency;

    /**
     * @var string
     */
    protected string $_publishedKey;

    /**
     * @var string
     */
    protected string $_categoryIdKey;
    
    /**
     * @var string
     */
    protected string $_groupIdKey;

    /**
     * @var (CategoryMarker[])[]
     */
    private array $_foldersCache = [];

    /**
     * Callback after create element.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function onAfterCreate()
    {
        parent::onAfterCreate();

        $this->_categoriesFieldType = 'hpfolders';
        $this->_groupsFieldType     = 'hpfolders';
        $this->_partFieldsContext   = 'com_hyperpc.position';

        $this->_productHelper       = $this->hyper['helper']['moyskladProduct'];
        $this->_partHelper          = $this->hyper['helper']['moyskladPart'];
        $this->_serviceHelper       = $this->hyper['helper']['moyskladService'];
        $this->_groupHelper         = $this->hyper['helper']['productFolder'];
        $this->_categoryHelper      = $this->hyper['helper']['productFolder'];
        $this->_stockHelper         = $this->hyper['helper']['moyskladStock'];

        $this->_publishedKey        = 'a.state';
        $this->_categoryIdKey       = 'a.product_folder_id';
        $this->_groupIdKey          = 'a.product_folder_id';

        /** @var MoneyHelper */
        $moneyHelper = $this->hyper['helper']['money'];
        $price = $moneyHelper->get(0);

        $this->_currency = $moneyHelper->getCurrencyIsoCode($price);
    }

    /**
     * Get product list.
     *
     * @return  ProductMarker[]
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getProducts()
    {
        $categories = $this->_getCategories();
        if (!count($categories)) {
            return [];
        }

        $categoryIds = array_keys($categories);

        $products     = [];
        $productsKind = $this->getConfig('products_kind', [], 'arr');

        if (in_array(self::OFFERS_KIND_CATALOG, $productsKind)) {
            $db       = $this->hyper['db'];
            $products = $this->_productHelper->findAll([
                'conditions' => [
                    $db->qn($this->_publishedKey) . ' = ' . $db->q(HP_STATUS_PUBLISHED),
                    $db->qn('a.on_sale') . ' = ' . $db->q(HP_STATUS_PUBLISHED),
                    $db->qn($this->_categoryIdKey) . ' IN (' . implode(', ', $categoryIds) . ')'
                ]
            ]);
        }

        if (in_array(self::OFFERS_KIND_STOCK, $productsKind)) {
            /** @todo consider store */
            $stockProducts = $this->_stockHelper->getProducts([], $categoryIds);
            $products = array_merge($products, $stockProducts);
        }

        return $products;
    }

    /**
     * Get product params for price list
     *
     * @param   ProductMarker $product
     *
     * @return  array
     *
     * @throws  Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getProductParams(ProductMarker $product)
    {
        $params = [];

        if (!$this->getConfig('enable_product_params', false, 'bool')) {
            return $params;
        }

        $groups      = $this->_groupHelper->getList();
        $paramGroups = $this->getConfig('param_groups', [], 'arr');
        $paramFields = $this->getConfig('param_fields', [], 'arr');

        $parts = $product->getConfigParts(true, 'a.product_folder_id ASC', false, false, true);

        foreach ($parts as $groupId => $groupParts) {
            if (!isset($groups[$groupId])) {
                continue;
            }

            /** @var ProductFolder $group */
            $group = $groups[$groupId];

            /** @var PartMarker $part */
            foreach ($groupParts as $part) {
                if (in_array((string) $groupId, $paramGroups)) {
                    $partName = $part->getName();
                    $params[] = new OfferParamData([
                        'name'  => $group->title,
                        'value' => $partName,
                    ]);
                }

                $partFields = $group->getPartFields($part->id, ['part_fields' => $paramFields]);
                foreach ($partFields as $partField) {
                    $fieldValue = StringHelper::trim($partField->getValue());

                    if (!empty($fieldValue) && $fieldValue !== '-') {
                        $params[] = new OfferParamData([
                            'name'  => $partField->title,
                            'value' => $fieldValue,
                        ]);
                    }
                }
            }
        }

        return $params;
    }

    /**
     * Get product title
     *
     * @param   ProductMarker $product
     *
     * @return  string
     *
     * @throws  Exception|\JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getProductTitle(ProductMarker $product)
    {
        $platform  = $this->getConfig('product_title_platform', false, 'bool');
        $fieldsKey = 'product_title_fields';
        $vendor    = static::VENDOR;

        return $this->_buildTitle($product, $platform, $fieldsKey, $vendor);
    }

    /**
     * Get product short title
     *
     * @param   ProductMarker $product
     *
     * @return  string
     *
     * @throws  Exception|\JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getProductShortTitle(ProductMarker $product)
    {
        $platform  = $this->getConfig('product_title_platform', false, 'bool');
        $fieldsKey = 'short_title_fields';

        return $this->_buildTitle($product, $platform, $fieldsKey);
    }

    /**
     * Get product model
     *
     * @param   ProductMarker $product
     *
     * @return  string
     *
     * @throws  Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getProductModel(ProductMarker $product)
    {
        return $this->_buildTitle($product, false, 'product_model_fields');
    }

    /**
     * Build product title
     *
     * @param  ProductMarker $product
     * @param  bool          $platform
     * @param  string        $fieldsKey
     * @param  string        $vendor
     *
     * @return string
     *
     * @throws Exception
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since  2.0
     */
    private function _buildTitle(ProductMarker $product, $platform = false, $fieldsKey = '', $vendor = '')
    {
        if ($platform === true) {
            $folder    = $product->getFolder();
            $itemsType = $folder->getItemsType();
            $title[]   = Text::_("COM_HYPERPC_PRODUCT_TYPE_{$itemsType}");
        }

        if (!empty($vendor)) {
            $title[] = static::VENDOR;
        }

        $title[] = trim(ucwords(strtolower(str_replace(static::VENDOR, '', $product->name))));

        $titleFields = $this->getConfig($fieldsKey, [], 'arr');
        if (count($titleFields)) {
            $title[] = trim($this->_getFieldParamsString($product, $titleFields));
        }

        return implode(' ', $title);
    }

    /**
     * Get Product description
     *
     * @param   ProductMarker $product
     *
     * @return  string
     *
     * @throws  Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getProductDescription(ProductMarker $product)
    {
        $description = HTMLHelper::_('content.prepare', trim(strip_tags($product->description)));

        if ($product->isFromStock() || empty($description)) {
            $descriptionGroups = $this->getConfig('description_groups', [], 'arr');
            if (!empty($descriptionGroups)) {
                $description = $this->hyper['helper']['moyskladProduct']->getMiniDescription(
                    $product,
                    $this->_groupHelper->findById($descriptionGroups)
                );
            }
        }

        return $description;
    }

    /**
     * Get market part list.
     *
     * @return  PartMarker[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getParts()
    {
        $groups = $this->_getPartGroups();
        if (!count($groups)) {
            return [];
        }

        $parts = [];
        $db    = $this->hyper['db'];

        $conditions = [
            $db->qn($this->_publishedKey) . ' NOT IN (' . $db->q(HP_STATUS_UNPUBLISHED) . ',' . $db->q(HP_STATUS_TRASHED) . ')',
            $db->qn($this->_groupIdKey) . ' IN (' . implode(', ', array_keys($groups)) . ')'
        ];

        $onlyRetail = $this->getConfig('only_onsale_parts', false, 'bool');
        $retailPreorder = $this->getConfig('onsale_preorder_parts', false, 'bool');

        $_parts = $this->_partHelper->findAll([
            'conditions' => $conditions
        ]);

        /** @var PartMarker $part */
        foreach ($_parts as $part) {
            if ($part->isDiscontinued() || ($onlyRetail && (!$part->isForRetailSale() || $part->isOnlyForUpgrade()))) {
                continue;
            }

            $options = $part->getOptions();
            if (count($options)) {
                foreach ($options as $option) {
                    if ($option->isDiscontinued() || ($onlyRetail && !$retailPreorder && !$option->isInStock())) {
                        /** @todo consider store */
                        continue;
                    }

                    $newPart = clone $part;
                    $newPart->set('option', $option);
                    $newPart->setListPrice($option->getListPrice());
                    $newPart->setSalePrice($option->getSalePrice());

                    $parts[] = $newPart;
                }
            } else {
                if ($onlyRetail && !$retailPreorder && !$part->isInStock()) {
                    /** @todo consider store */
                    continue;
                }

                $parts[] = $part;
            }
        }

        return $parts;
    }

    /**
     * Get services list.
     *
     * @return  MoyskladService[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getServices()
    {
        $folders = $this->_getServiceFolders();
        if (!count($folders)) {
            return [];
        }

        $db = $this->hyper['db'];

        $conditions = [
            $db->qn($this->_publishedKey) . ' = ' . $db->q(HP_STATUS_PUBLISHED),
            $db->qn($this->_groupIdKey) . ' IN (' . implode(', ', array_keys($folders)) . ')'
        ];

        return $this->_serviceHelper->findAll([
            'conditions' => $conditions
        ]);
    }

    /**
     * Get export product folders.
     *
     * @return  CategoryMarker[]
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getCategories()
    {
        if (!$this->getConfig('include_products', false, 'bool')) {
            return [];
        }

        $ids   = $this->getConfig('product_folders', [], 'arr');
        $logic = $this->getConfig('product_folders_logic', 'NOT IN');

        return $this->_getFolders($ids, $logic, $this->_categoryHelper);
    }

    /**
     * Get export part folders
     *
     * @return  CategoryMarker[]
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getPartGroups()
    {
        if (!$this->getConfig('include_parts', false, 'bool')) {
            return [];
        }

        $ids   = $this->getConfig('part_folders', [], 'arr');
        $logic = $this->getConfig('part_folders_logic', 'NOT IN');

        return $this->_getFolders($ids, $logic, $this->_groupHelper);
    }

    /**
     * Get export service folders
     *
     * @return  CategoryMarker[]
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getServiceFolders()
    {
        if (!$this->getConfig('include_services', false, 'bool')) {
            return [];
        }

        $ids   = $this->getConfig('service_folders', [], 'arr');
        $logic = $this->getConfig('service_folders_logic', 'NOT IN');

        return $this->_getFolders($ids, $logic, $this->_groupHelper);
    }

    /**
     * Get folders list
     *
     * @param   array $ids
     * @param   string $logic
     * @param   EntityContext $folderHelper
     *
     * @return  CategoryMarker[]
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function _getFolders(array $ids, string $logic, EntityContext $folderHelper)
    {
        sort($ids, SORT_NUMERIC);
        $hash = md5(implode(',', $ids) . $logic . get_class($folderHelper));
        if (array_key_exists($hash, $this->_foldersCache)) {
            return $this->_foldersCache[$hash];
        }

        if ($logic === 'IN' && !count($ids)) {
            return [];
        }

        $db = $this->hyper['db'];
        $conditions = [
            $db->qn('a.published') . ' = ' . $db->q(HP_STATUS_PUBLISHED),
            $db->qn('a.alias') . ' != ' . $db->q('root'),
        ];

        if (count($ids)) {
            $conditions[] = $db->qn('a.id') . ' ' . $logic . '(' . implode(', ', $ids) . ')';
        }

        $this->_foldersCache[$hash] = $folderHelper->findAll([
            'conditions' => $conditions,
            'order'      => $db->qn('a.lft') . ' ASC'
        ]);

        return $this->_foldersCache[$hash];
    }

    /**
     * Get part type prefix
     *
     * @param   PartMarker $part
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getPartTypePrefix(PartMarker $part)
    {
        $folders = $this->_getPartGroups();
        $folder  = $folders[$part->getFolderId()];

        return $folder->getYandexMarketXmlName();
    }

    /**
     * Get a regex to search for a vendor
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getVendorsListRegex()
    {
        if (isset($this->_vendorsListRegex)) {
            return $this->_vendorsListRegex;
        }

        $vendorsList = [];

        $vendorFieldId = $this->getConfig('vendor_field');
        if (!empty($vendorFieldId)) {
            $fields = $this->hyper['helper']['fields']->getFieldsById([$vendorFieldId]);
            $field = array_shift($fields);
            if ($field) {
                $fieldOptions = $field->fieldparams->get('options', []);
                foreach ($fieldOptions as $option) {
                    if ($option['name'] !== '-') {
                        $vendorsList[] = $option['name'];
                    }
                }
            }
        }

        $regexContent = implode('|', array_map(function ($vendor) {
            return preg_replace('/\s/', '\s', preg_quote($vendor));
        }, $vendorsList));

        $this->_vendorsListRegex = '/' . $regexContent . '/iu';

        return $this->_vendorsListRegex;
    }

    /**
     * Get product type prefix
     *
     * @param   ProductMarker $product
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getProductTypePrefix(ProductMarker $product)
    {
        $folders = $this->_getCategories();
        $folder  = $folders[$product->getFolderId()];

        return $folder->getYandexMarketName() ?: Text::_('COM_HYPERPC_PRODUCT_TYPE_' . strtoupper($folder->getItemsType()));
    }

    /**
     * Get string from field params
     *
     * @param   ProductMarker $product
     * @param   array         $fieldIds
     *
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getFieldParamsString($product, $fieldIds)
    {
        $fieldString = '';

        if ($fieldIds) {
            $groups = $this->_groupHelper->getList(true);
            $parts  = $product->getConfigParts(true, 'a.product_folder_id ASC', false, false, true);

            foreach ($parts as $groupId => $groupParts) {
                if (!isset($groups[$groupId])) {
                    continue;
                }

                /** @var ProductFolder $group */
                $group = $groups[$groupId];

                /** @var PartMarker $part */
                foreach ($groupParts as $part) {
                    foreach ($group->getPartFields($part->id, ['part_fields' => $fieldIds]) as $partField) {
                        if (in_array((string) $partField->id, $fieldIds)) {
                            $fieldValue = trim($partField->getValue());
                            if (empty($fieldValue) || $fieldValue === '-') {
                                continue;
                            }

                            $fieldValues[] = trim($partField->getValue());
                        }
                    }
                }
            }

            if (isset($fieldValues) && count($fieldValues)) {
                $fieldString = ' ' . implode(' / ', $fieldValues);
            }
        }

        return $fieldString;
    }

    /**
     * Export price list to file.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function export()
    {
        if (!$this->getConfig('state', true, 'bool')) {
            return;
        }

        $fileName = $this->_getFileName();
        $ext = File::getExt($fileName);
        if (empty($ext) || $ext !== 'xml') {
            $fileName .= '.xml';
        }

        File::write(JPATH_ROOT . '/' . $fileName, $this->_renderTemplate());
    }

    /**
     * Get price list format
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getFormat(): string
    {
        return static::FORMAT;
    }

    /**
     * Get barcode for entity
     *
     * @param  $entity
     * @param  $offerData
     *
     * @throws Exception
     *
     * @since  2.0
     */
    protected function _getBarcode($entity, &$offerData)
    {
        if ($this->getConfig('barcode', 0, 'bool')) {
            $barcodeType = $this->getConfig('barcode_type');
            $barcodes    = $entity->getBarcodesByType($barcodeType);

            $offerData['barcode'] = implode(',', $barcodes);
        }
    }

    /**
     * Get context
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    private function _getContext()
    {
        return $this->getConfig('products_context');
    }

    /**
     * Get rendered layout.
     *
     * @return  string
     *
     * @since   2.0
     */
    private function _renderTemplate()
    {
        $path = 'xml/formats/' . $this->_getFormat();

        return (string) $this->hyper['helper']['render']->render($path, [
            'priceListData' => $this->_getPriceListData()
        ]);
    }

    /**
     * Get price list data object
     *
     * @return  PriceListData
     *
     * @since   2.0
     */
    abstract protected function _getPriceListData(): PriceListData;

    /**
     * Get file name
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getFileName(): string
    {
        return $this->getConfig('price_file_name');
    }

    /**
     * Element edit common fields.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _commonEditFields()
    {
        $commonFields = parent::_commonEditFields();

        unset($commonFields['for_manager']);

        $commonFields['state'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_STATE_LABEL',
            'class'   => 'btn-group btn-group-yesno',
            'default' => 1,
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['price_file_name'] = [
            'type'     => 'text',
            'label'    => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FILE_NAME_LABEL',
            'required' => 'required',
            'default'  => $this->_getDefaultFileName()
        ];

        $format = $this->_getFormat();

        if ($format === 'yml') {
            $commonFields['yml_type'] = [
                'type'        => 'list',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_YML_TYPE_LABEL',
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_YML_TYPE_DESC',
                'default'     => self::YML_TYPE_SIMPLIFIED,
                'options'     => [
                    self::YML_TYPE_SIMPLIFIED => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_YML_TYPE_SIMPLIFIED',
                    self::YML_TYPE_COMBINED   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_YML_TYPE_COMBINED'
                ]
            ];

            $commonFields['vendor_field'] = [
                'type'      => 'fields',
                'context'   => $this->_partFieldsContext,
                'label'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_VENDOR_FIELD_LABEL',
                'class'     => 'input-large-text',
                'showon'    => 'yml_type:' . self::YML_TYPE_COMBINED,
            ];
        } elseif ($format === 'atom') {
            $commonFields['google_category_id'] = [
                'type'    => 'radio',
                'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SHOW_GOOGLE_CATEGORY_ID_LABEL',
                'class'   => 'btn-group btn-group-yesno',
                'default' => 0,
                'options' => [
                    0 => 'JNO',
                    1 => 'JYES'
                ]
            ];

            $commonFields['vendor_field'] = [
                'type'      => 'fields',
                'context'   => $this->_partFieldsContext,
                'label'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_VENDOR_FIELD_LABEL',
                'class'     => 'input-large-text',
            ];
        }

        if ($format === 'atom' || $format === 'yml' || $format === 'csv') {
            $commonFields['barcode'] = [
                'type'    => 'radio',
                'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_BARCODE_LABEL',
                'class'   => 'btn-group btn-group-yesno',
                'default' => 0,
                'options' => [
                    0 => 'JNO',
                    1 => 'JYES'
                ]
            ];

            $commonFields['barcode_type'] = [
                'type'     => 'list',
                'label'    => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_BARCODE_TYPE_LABEL',
                'options' => [
                    'ean13'   => 'ean13',
                    'ean8'    => 'ean8',
                    'code128' => 'code128',
                    'gtin'    => 'gtin'
                ],
                'showon'   => 'barcode:1'
            ];
        }

        $this->_productEditFields($format, $commonFields);
        $this->_partEditFields($format, $commonFields);
        $this->_serviceEditFields($format, $commonFields);

        return $commonFields;
    }

    /**
     * Element edit product fields
     *
     * @param string $format
     * @param array  $commonFields
     *
     * @since 2.0
     */
    protected function _productEditFields(string $format, array &$commonFields)
    {
        $commonFields['separator_products'] = [
            'type'  => 'hpseparator',
            'title' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SEPARATOR_PRODUCTS'
        ];

        $commonFields['include_products'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_INCLUDE_PRODUCTS_LABEL',
            'class'   => 'btn-group btn-group-yesno',
            'default' => 0,
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['product_folders'] = [
            'type'     => $this->_categoriesFieldType,
            'label'    => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCT_FOLDERS_LABEL',
            'class'    => 'input-xxlarge input-large-text',
            'multiple' => 'true',
            'showon'   => 'include_products:1'
        ];

        $commonFields['product_folders_logic'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_LABEL',
            'default' => 'NOT IN',
            'class'   => 'btn-group',
            'showon'  => 'include_products:1',
            'options' => [
                'IN'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_INCLUDE',
                'NOT IN' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_EXCLUDE'
            ]
        ];

        $commonFields['products_kind'] = [
            'type'    => 'checkboxes',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCTS_KIND_LABEL',
            'showon'  => 'include_products:1',
            'default' => self::OFFERS_KIND_CATALOG,
            'options' => [
                self::OFFERS_KIND_CATALOG => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCTS_KIND_CATALOG',
                self::OFFERS_KIND_STOCK   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCTS_KIND_STOCK'
            ]
        ];

        if ($format === 'yml') {
            $commonFields['rewrite_availability'] = [
                'type'          => 'radio',
                'label'         => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_REWRITE_AVAILABILITY_LABEL',
                'description'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_REWRITE_AVAILABILITY_DESC',
                'class'         => 'btn-group btn-group-yesno',
                'showon'        => 'include_products:1[AND]products_kind:' . self::OFFERS_KIND_CATALOG,
                'default'       => 0,
                'options'       => [
                    0 => 'JNO',
                    1 => 'JYES'
                ]
            ];
        }

        $commonFields['product_title_platform'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCT_TITLE_PLATFORM_LABEL',
            'class'   => 'btn-group btn-group-yesno',
            'showon'  => 'include_products:1',
            'default' => 0,
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['product_title_fields'] = [
            'type'        => 'fields',
            'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCT_TITLE_FIELDS_DESC',
            'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PRODUCT_TITLE_FIELDS_LABEL',
            'class'       => 'input-xxlarge input-large-text',
            'context'     => $this->_partFieldsContext,
            'multiple'    => 'true',
            'showon'      => 'include_products:1'
        ];

        if ($format === 'atom') {
            $commonFields['enable_short_title'] = [
                'type'        => 'radio',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ENABLE_SHORT_TITLE_LABEL',
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ENABLE_SHORT_TITLE_DESC',
                'class'       => 'btn-group btn-group-yesno',
                'showon'      => 'include_products:1',
                'default'     => 0,
                'options'     => [
                    0 => 'JNO',
                    1 => 'JYES'
                ],
            ];

            $commonFields['short_title_fields'] = [
                'type'        => 'fields',
                'context'     => $this->_partFieldsContext,
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SHORT_TITLE_FIELDS_DESC',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SHORT_TITLE_FIELDS_LABEL',
                'class'       => 'input-xxlarge input-large-text',
                'multiple'    => 'true',
                'showon'      => 'include_products:1[AND]enable_short_title:1',
            ];
        }

        $commonFields['description_groups'] = [
            'type'        => $this->_groupsFieldType,
            'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_DESCRIPTION_GROUPS_DESC',
            'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_DESCRIPTION_GROUPS_LABEL',
            'class'       => 'input-xxlarge input-large-text',
            'multiple'    => 'true',
            'showon'      => 'include_products:1'
        ];

        if ($format === 'yml') {
            $commonFields['product_model_fields'] = [
                'type'        => 'fields',
                'context'     => $this->_partFieldsContext,
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_MODEL_FIELDS_DESC',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_MODEL_FIELDS_LABEL',
                'class'       => 'input-xxlarge input-large-text',
                'multiple'    => 'true',
                'showon'      => 'include_products:1[AND]yml_type:' . self::YML_TYPE_COMBINED
            ];
        }

        if ($format === 'atom') {
            $commonFields['show_product_type'] = [
                'type'        => 'radio',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SHOW_PRODUCT_TYPE_LABEL',
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SHOW_PRODUCT_TYPE_DESC',
                'class'       => 'btn-group btn-group-yesno',
                'default'     => 0,
                'options'     => [
                    0 => 'JNO',
                    1 => 'JYES'
                ]
            ];
        }

        if ($format === 'atom' || $format === 'yml') {
            $commonFields['enable_product_params'] = [
                'type'    => 'radio',
                'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ENABLE_PARAMS_LABEL',
                'class'   => 'btn-group btn-group-yesno',
                'showon'  => 'include_products:1',
                'default' => 0,
                'options' => [
                    0 => 'JNO',
                    1 => 'JYES'
                ],
            ];

            $commonFields['param_groups'] = [
                'type'        => $this->_groupsFieldType,
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_GROUPS_DESC',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_GROUPS_LABEL',
                'class'       => 'input-xxlarge input-large-text',
                'multiple'    => 'true',
                'showon'      => 'include_products:1[AND]enable_product_params:1',
            ];

            $commonFields['param_fields'] = [
                'type'        => 'fields',
                'context'     => $this->_partFieldsContext,
                'description' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FIELDS_DESC',
                'label'       => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FIELDS_LABEL',
                'class'       => 'input-xxlarge input-large-text',
                'multiple'    => 'true',
                'showon'      => 'include_products:1[AND]enable_product_params:1',
            ];
        }
    }

    /**
     * Element edit part fields
     *
     * @param string $format
     * @param array  $commonFields
     *
     * @since 2.0
     */
    protected function _partEditFields(string $format, array &$commonFields)
    {
        $commonFields['separator_parts'] = [
            'type'  => 'hpseparator',
            'title' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SEPARATOR_PARTS'
        ];

        $commonFields['include_parts'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_INCLUDE_PARTS_LABEL',
            'class'   => 'btn-group btn-group-yesno',
            'default' => 0,
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ]
        ];

        $commonFields['only_onsale_parts'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ONLY_ONSALE_PARTS_LABEL',
            'class'   => 'btn-group',
            'showon'  => 'include_parts:1',
            'default' => 0,
            'options' => [
                0 => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ONLY_ONSALE_PARTS_ALL',
                1 => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ONLY_ONSALE_PARTS_ONSALE'
            ]
        ];

        $commonFields['onsale_preorder_parts'] = [
            'type'          => 'checkbox',
            'label'         => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ONSALE_PREORDER_PARTS_LABEL',
            'description'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_ONSALE_PREORDER_PARTS_DESC',
            'showon'        => 'include_parts:1[AND]only_onsale_parts:1'
        ];

        $commonFields['part_folders'] = [
            'type'      => $this->_groupsFieldType,
            'label'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_PART_FOLDERS_LABEL',
            'class'     => 'input-xxlarge input-large-text',
            'multiple'  => 'true',
            'showon'    => 'include_parts:1'
        ];

        $commonFields['part_folders_logic'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_LABEL',
            'default' => 'NOT IN',
            'class'   => 'btn-group',
            'showon'  => 'include_parts:1',
            'options' => [
                'IN'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_INCLUDE',
                'NOT IN' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_EXCLUDE'
            ]
        ];
    }

    /**
     * Element edit service fields
     *
     * @param string $format
     * @param array  $commonFields
     *
     * @since 2.0
     */
    protected function _serviceEditFields(string $format, array &$commonFields)
    {
        $commonFields['separator_services'] = [
            'type'  => 'hpseparator',
            'title' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SEPARATOR_SERVICES'
        ];

        $commonFields['include_services'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_INCLUDE_SERVICES_LABEL',
            'class'   => 'btn-group btn-group-yesno',
            'default' => 0,
            'options' => [
                0 => 'JNO',
                1 => 'JYES'
            ],
        ];

        $commonFields['service_folders'] = [
            'type'      => $this->_groupsFieldType,
            'label'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_SERVICE_FOLDERS_LABEL',
            'class'     => 'input-xxlarge input-large-text',
            'multiple'  => 'true',
            'showon'    => 'include_services:1'
        ];

        $commonFields['service_folders_logic'] = [
            'type'    => 'radio',
            'label'   => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_LABEL',
            'default' => 'NOT IN',
            'class'   => 'btn-group',
            'showon'  => 'include_services:1',
            'options' => [
                'IN'     => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_INCLUDE',
                'NOT IN' => 'COM_HYPERPC_ELEMENT_PRICE_LIST_PARAM_FOLDERS_LOGIC_EXCLUDE'
            ]
        ];
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
        return 'xml.' . preg_replace('/_/', '-', strtolower($this->getType())) . '.xml';
    }
}
