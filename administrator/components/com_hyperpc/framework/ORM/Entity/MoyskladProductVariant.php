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

namespace HYPERPC\ORM\Entity;

use \Exception;
use MoySklad\Entity\Meta;
use MoySklad\Entity\Price;
use Joomla\CMS\Date\Date;
use MoySklad\Entity\Variant;
use HYPERPC\Money\Type\Money;
use MoySklad\Entity\PriceType;
use HYPERPC\Helper\MoySkladHelper;
use HYPERPC\Helper\PositionHelper;
use MoySklad\Entity\Characteristic;
use MoySklad\Entity\Product\ProductFolder;
use HYPERPC\MoySklad\Entity\Product\Product;
use HYPERPC\Helper\MoyskladCustomerOrderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use MoySklad\Entity\Product\Product as BaseProduct;

/**
 * MoyskladProductVariant class.
 *
 * @property    int         $id
 * @property    string      $uuid
 * @property    string      $context
 * @property    int         $product_id
 * @property    string      $name
 * @property    Money       $list_price
 * @property    Money       $sale_price
 * @property    Date        $created_time
 * @property    Date        $modified_time
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class MoyskladProductVariant extends Entity
{

    /**
     * Uuid of a product variant characteristic for a config id
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_configIdCharacteristicUuid;

    /**
     * Uuid of list price type
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_listPriceTypeUuid;

    /**
     * Custom field types.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldTypes = [
        'list_price' => 'money',
        'sale_price' => 'money'
    ];

    /**
     * Field list of json type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldJsonType = [];

    /**
     * Field list of boolean type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldBooleanType = [];

    /**
     * Get admin (backend) edit url.
     *
     * @return  null
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
        return null;
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTablePrefix()
            ->setTableType('Moysklad_Product_Variants');

        parent::initialize();

        $params = $this->hyper['params'];

        $this->_configIdCharacteristicUuid = $params->get('moysklad_config_id_characteristic_field_uuid', '');
        $this->_listPriceTypeUuid = $params->get('moysklad_list_price_type_uuid', '');
    }

    /**
     * Get moysklad entity type
     *
     * @return  string (variant|product)
     *
     * @since   2.0
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get parent product
     *
     * @return  MoyskladProduct
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getProduct()
    {
        return $this->hyper['helper']['moyskladProduct']->findById($this->product_id);
    }

    /**
     * Build moysklad varian entity from ORM object
     *
     * @param   Variant|null $variant
     *
     * @return  Variant
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function toMoyskladEntity(?Variant $variant = null): Variant
    {
        if (empty($this->id)) {
            return new Variant();
        }

        /** @var MoySkladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];

        if (!$variant) {
            $meta = $this->uuid ? $moyskladHelper->buildEntityMeta(
                'variant',
                $this->uuid
            )->toBaseMeta() : null;
            $variant = new Variant($meta);
        }

        $variant->name = $this->name;

        $listPrice = new Price();
        $listPrice->value = $this->list_price->val() * 100;

        $priceTypeMeta = new Meta();
        $priceTypeMeta->mediaType = 'application/json';
        $priceTypeMeta->type = 'pricetype';
        $priceTypeMeta->href = $moyskladHelper->getApiPath() . '/context/companysettings/' . $priceTypeMeta->type . '/' . $this->_listPriceTypeUuid;

        $listPrice->priceType = new PriceType($priceTypeMeta);

        $variant->salePrices = [$listPrice];
        $variant->externalCode = ltrim($this->name, '0');
        $characteristic = new Characteristic();
        $characteristic->id = $this->_configIdCharacteristicUuid;
        $characteristic->value = $this->name;

        $variant->characteristics = [
            $characteristic
        ];

        $product = $this->getProduct();

        $variant->product = new BaseProduct(
            $moyskladHelper->buildEntityMeta(
                'product',
                $product->uuid
            )->toBaseMeta()
        );

        return $variant;
    }

    /**
     * Build moysklad product entity from ORM object
     *
     * @param   Product|null $variant
     *
     * @return  Product
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function toMoyskladProductEntity(?Product $product = null): Product
    {
        if (empty($this->id)) {
            return new Product();
        }

        /** @var MoySkladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];

        if (!$product) {
            $meta = $this->uuid ? $moyskladHelper->buildEntityMeta(
                'product',
                $this->uuid
            )->toBaseMeta() : null;
            $product = new Product($meta);
        }

        $product->uom = $moyskladHelper->getUom();

        $parent = $this->getProduct();
        $parent->saved_configuration = $this->id;

        $product->name = "{$parent->name} ({$this->name})";

        $modelNameSegments = explode(' ', $parent->name);
        $articlePrefix = join('', array_map(function ($segment) {
            return substr($segment, 0, 1);
        }, $modelNameSegments));

        $product->article = "{$articlePrefix}-{$parent->saved_configuration}";

        $listPrice = new Price();
        $listPrice->value = $this->list_price->val() * 100;

        $priceTypeMeta = new Meta();
        $priceTypeMeta->mediaType = 'application/json';
        $priceTypeMeta->type = 'pricetype';
        $priceTypeMeta->href = $moyskladHelper->getApiPath() . '/context/companysettings/' . $priceTypeMeta->type . '/' . $this->_listPriceTypeUuid;

        $listPrice->priceType = new PriceType($priceTypeMeta);

        $product->salePrices = [$listPrice];

        $product->externalCode = $parent->id;

        $folderUuid = $this->hyper['helper']['productFolder']->getOrderProductsFolderUuid();
        $folderMeta = $moyskladHelper->buildEntityMeta('productfolder', $folderUuid)->toBaseMeta();
        $product->productFolder = new ProductFolder($folderMeta);

        /** @var PositionHelper */
        $positionHelper = $this->hyper['helper']['position'];

        $product->attributes[] = $moyskladHelper->buildAttribute(
            'product',
            $positionHelper->getTypeFieldUuid(),
            'customentity',
            $positionHelper->getMoyskladTypeValue(PositionHelper::POSITION_TYPE_PRODUCT)
        );

        $product->attributes[] = $moyskladHelper->buildAttribute(
            'product',
            $positionHelper->getConfigIdFieldUuid(),
            'string',
            $this->id
        );

        $product->isSerialTrackable = true;

        return $product;
    }

    /**
     * Convert to Moysklad entity and update by API
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function updateInMoysklad()
    {
        /** @var MoySkladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];
        try {
            if ($this->getContext() === MoyskladCustomerOrderHelper::PRODUCT_CREATE_MODE_PRODUCT) {
                $msProduct = $this->toMoyskladProductEntity();
                $moyskladHelper->updateProduct($msProduct);
            } else {
                $msVariant = $this->toMoyskladEntity();
                $moyskladHelper->updateVariant($msVariant);
            }
        } catch (\Throwable $th) {
            return false;
        }
        
        return true;
    }
}
