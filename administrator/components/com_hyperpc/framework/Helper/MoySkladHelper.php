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

namespace HYPERPC\Helper;

use Exception;
use Joomla\CMS\Log\Log;
use MoySklad\Entity\Uom;
use MoySklad\Entity\State;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use HYPERPC\ORM\Table\Table;
use MoySklad\Entity\Variant;
use MoySklad\Util\Param\Expand;
use HYPERPC\MoySklad\ApiClient;
use MoySklad\Util\Param\Search;
use MoySklad\Entity\Store\Store;
use HYPERPC\MoySklad\Entity\Meta;
use MoySklad\Entity\Product\Product;
use MoySklad\Entity\Product\Service;
use HYPERPC\MoySklad\Entity\WebHook;
use MoySklad\Client\AssortmentClient;
use MoySklad\Client\EntityClientBase;
use HYPERPC\MoySklad\Entity\Attribute;
use MoySklad\Util\Param\StandardFilter;
use HYPERPC\MoySklad\Entity\MetaEntity;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\MoySklad\Http\RequestExecutor;
use HYPERPC\MoySklad\Util\Param\StockType;
use MoySklad\Entity\Product\ProductFolder;
use MoySklad\Util\Exception\ApiClientException;
use HYPERPC\MoySklad\Entity\Agent\Counterparty;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\MoySklad\Entity\Document\CustomerOrder;
use HYPERPC\MoySklad\Entity\Document\ProcessingPlan;
use MoySklad\Entity\MetaEntity as MoyskladMetaEntity;
use HYPERPC\MoySklad\Util\Serializer\SerializerInstance;

/**
 * Class MoySkladHelper
 *
 * @method  \HyperPcTableMoysklad_Webhooks getTable()
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoySkladHelper extends AppHelper
{
    const HOST = 'api.moysklad.ru';
    const API_HREF_REGEX = '/^https?:\/\/[a-z]+\.moysklad\.ru\/api\/remap\/.+\/(.+)\/(.+)$/';

    /**
     * Organization UUID
     *
     * @var     string
     *
     * @since   2.0
     */
    protected static $_organizationUuid;

    /**
     * Uom pcs UUID
     *
     * @var     string
     *
     * @since   2.0
     */
    protected static $_uomPcsUuid;

    /**
     * List of entity types for interacttion via moysklad API
     *
     * @var     array
     *
     * @since   2.0
     */
    public array $entityTypes = [
        'productfolder',
        'product',
        'service',
        'variant',
        'store',
        'enter',
        'loss',
        'move',
        'supply',
        'customerorder',
        'processingplan'
    ];

    /**
     * Hold table
     *
     * @var     \HyperPcTableMoysklad_Webhooks
     *
     * @since   2.0
     */
    protected $_table;

    /**
     * Application entry point.
     *
     * @var     ApiClient
     *
     * @since   2.0
     */
    protected $_apiClient;

    /**
     * Is entity creation enabled in moysklad.
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $_createEntitiesEnabled = false;

    /**
     * Magic overloading method.
     *
     * @param   string $name
     * @param   array $arguments
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     *
     * @since   2.0
     */
    public function __call($name, $arguments)
    {
        $re = '/(.+)(Create|Update|Delete)/';

        if (preg_match($re, $name, $matches)) {
            $entity = $matches[1];
            $action = $matches[2];
        }

        if (in_array($entity, ['enter', 'loss', 'move', 'supply'])) {
            /** @todo update certain positions of the document */
            $this->hyper['helper']['moyskladStock']->updateStocks();
        } else {
            throw new \BadMethodCallException("Method {$name} doesn't exist");
        }

        return;
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->_table = Table::getInstance('Moysklad_Webhooks');

        $params = $this->hyper['params'];

        self::$_organizationUuid = self::$_organizationUuid ?? $params->get('moysklad_organization_uuid', '');
        self::$_uomPcsUuid = self::$_uomPcsUuid ?? $params->get('moysklad_product_uom_pcs_uuid', '');

        $this->_apiClient = new ApiClient(self::HOST, true, [
            'login' => $params->get('moysklad_login', ''),
            'password' => $params->get('moysklad_password', ''),
        ]);

        $this->_createEntitiesEnabled = $params->get('create_entities_in_moysklad', false);
    }

    /**
     * Get API path
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getApiPath()
    {
        return $this->_apiClient->getHost() . RequestExecutor::API_PATH;
    }

    /**
     * Get app path
     *
     * @param   null|string $page
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAppPath(?string $page = null)
    {
        return 'https://online.moysklad.ru/app/' . ($page ? '#' . $page : '');
    }

    /**
     * Build meta object
     *
     * @param   string $metaType type of meta object
     * @param   string $entityType
     * @param   string $uuid moysklad id
     *
     * @return  Meta
     *
     * @since   2.0
     */
    public function buildMeta($metaType, $entityType, $uuid)
    {
        $meta = new Meta();
        $meta->mediaType = 'application/json';
        $meta->type = $metaType;
        $meta->href = $this->_buildEntityHref($entityType, $uuid);

        return $meta;
    }

    /**
     * Build attribute
     *
     * @param   string $metaType type of meta object
     * @param   string $uuid custom field uuid
     * @param   'string'|'long'|'time'|'file'|'double'|'boolean'|'customentity' $type
     * @param   mixed $value
     *
     * @return  Attribute
     *
     * @since   2.0
     */
    public function buildAttribute($metaType, $uuid, $type, $value): Attribute
    {
        $attributeMeta = $this->buildAttributeMeta($metaType, $uuid);
        $attribute = new Attribute($attributeMeta);
        $attribute->id = $uuid;

        switch ($type) {
            case 'long':
                $attribute->value = (int) $value;
                break;
            case 'time':
                /** @todo check */
                break;
            case 'file':
                /** @todo check */
                break;
            case 'double':
                $attribute->value = (double) $value;
                break;
            case 'boolean':
                $attribute->value = (bool) $value;
                break;
            case 'text':
            case 'string':
            case 'link':
                $attribute->value = (string) $value;
                break;
            default:
                $attribute->value = new MetaEntity();
                /** It's enought, but can change */
                $attribute->value->name = $value;
                break;
        }

        return $attribute;
    }

    /**
     * Build attribute meta
     *
     * @param   string $entityType
     * @param   string $uuid attribute moysklad id
     *
     * @return  Meta
     *
     * @since   2.0
     */
    public function buildAttributeMeta($entityType, $uuid)
    {
        return $this->buildMeta('attributemetadata', $entityType . '/metadata/attributes', $uuid);
    }

    /**
     * Build entity meta
     *
     * @param   string $type entity type
     * @param   string $uuid entity moysklad id
     *
     * @return  Meta
     *
     * @since   2.0
     */
    public function buildEntityMeta($type, $uuid)
    {
        return $this->buildMeta($type, $type, $uuid);
    }

    /**
     * Get organization meta object
     *
     * @return  Meta
     *
     * @since   2.0
     */
    public function getOrganizationMeta()
    {
        return $this->buildEntityMeta('organization', self::$_organizationUuid);
    }

    /**
     * Get uom object
     *
     * @return  Uom
     *
     * @since   2.0
     */
    public function getUom()
    {
        return new Uom($this->buildEntityMeta('uom', self::$_uomPcsUuid)->toBaseMeta());
    }

    /**
     * Get status list
     *
     * @return  State[]
     *
     * @since   2.0
     */
    public function getStatusList()
    {
        if (!is_file($this->_getStatusListFilePath())) {
            $this->updateStatusList();
        }

        $content = json_decode(file_get_contents($this->_getStatusListFilePath()));

        $serializer = SerializerInstance::getInstance();

        return array_map(function ($data) use ($serializer) {
            return $serializer->deserialize(json_encode($data), State::class, SerializerInstance::JSON_FORMAT);
        }, $content);
    }

    /**
     * Get webhooks list with actions uuid
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getWebhooks()
    {
        $webhooks = array_fill_keys($this->entityTypes, [
            'create' => '',
            'update' => '',
            'delete' => '',
        ]);

        $db    = $this->hyper['db'];
        $query = $db->getQuery(true);

        $query
            ->select(['a.*'])
            ->from($db->qn(HP_TABLE_MOYSKLAD_WEBHOOKS, 'a'));

        $records = (array) $db->setQuery($query)->loadAssocList();

        foreach ($records as $record) {
            $entityType = $record['entity_type'];
            $action     = strtolower($record['action']);
            $key        = $record['uuid'];

            if (isset($webhooks[$entityType])) {
                $webhooks[$entityType][$action] = $key;
            }
        }

        return $webhooks;
    }

    /**
     * Create webhook
     *
     * @param   string $entityType
     * @param   string $action create, update or delete
     * @param   string $url
     *
     * @return  string webhook uuid
     *
     * @throws  ApiClientException
     * @throws  Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function createWebhook($entityType, $action, $url)
    {
        $webhook = new Webhook();
        $webhook->entityType = $entityType;
        $webhook->action = strtoupper($action);
        $webhook->url = $url;

        if ($webhook->action === 'UPDATE') {
            $webhook->diffType = 'FIELDS';
        }

        $webhook = $this->_apiClient->entity()->webhook()->create($webhook);

        if ($webhook->id) {
            $saveData = [
                'entity_type' => $webhook->entityType,
                'action'      => $webhook->action,
                'url'         => $webhook->url,
                'uuid'        => $webhook->id
            ];

            $this->_table->save($saveData);
        }

        return $webhook->id;
    }

    /**
     * Remove webhook
     *
     * @param   string $uuid
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function removeWebhook(string $uuid)
    {
        $this->_apiClient->entity()->webhook()->delete($uuid);
        $this->_table->deleteByUuid($uuid);
    }

    /**
     * Product folder create
     *
     * @param   array $uuids moysklad ids of product folders for create
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function productfolderCreate(array $uuids)
    {
        $productFolderClient = $this->_apiClient->entity()->productfolder();

        $productFolders = $this->_getEntitiesByKey(
            $productFolderClient,
            $uuids
        );

        /** @var ProductFolder $folder */
        foreach ($productFolders as $folder) {
            $hpFolderId = $this->hyper['helper']['productFolder']->createByMoyskladEntity($folder);

            if (!empty($hpFolderId)) {
                $folder->externalCode = $hpFolderId;
                $productFolderClient->update($folder->id, $folder);
            }
        }
    }

    /**
     * Update product folders
     *
     * @param   array $uuids moysklad ids of product folders for update
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function productfolderUpdate(array $uuids, array $updatedFields = [])
    {
        $productFolderClient = $this->_apiClient->entity()->productfolder();

        $updatedProductFolders = $this->_getEntitiesByKey(
            $productFolderClient,
            $uuids
        );

        /** @var ProductFolder $folder */
        foreach ($updatedProductFolders as $folder) {
            $hpFolderId = $this->hyper['helper']['productFolder']->updateByMoyskladEntity($folder, $updatedFields);
            if ((string) $hpFolderId !== $folder->externalCode) {
                $folder->externalCode = $hpFolderId;
                $productFolderClient->update($folder->id, $folder);
            }
        }
    }

    /**
     * Delete product folders
     *
     * @param   array $uuids moysklad ids of product folders for remove
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function productfolderDelete(array $uuids)
    {
        foreach ($uuids as $uuid) {
            $this->hyper['helper']['productFolder']->moveToTrashByUuid($uuid);
        }
    }

    /**
     * Create products
     *
     * @param   array $keys moysklad ids of products for create
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function productCreate(array $keys)
    {
        $createdProducts = $this->_getEntitiesByKey(
            $this->_apiClient->entity()->assortment(),
            $keys
        );

        /** @var Product $product */
        foreach ($createdProducts as $product) {
            $productType = $this->hyper['helper']['position']->getPositionTypeFromMoyskladEntity($product);
            $helper = $this->hyper['helper']['moysklad' . $productType];

            $hpProductId = $helper->createByMoyskladEntity($product);

            if (!empty($hpProductId)) {
                $product->externalCode = $hpProductId;
                $product->barcodes = null; // Don't affect to the barcodes property
                $this->_apiClient->entity()->product()->update($product->id, $product);
            }
        }
    }

    /**
     * Update products
     *
     * @param   array $uuids moysklad ids of products for update
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function productUpdate(array $uuids, array $updatedFields = [])
    {
        $updatedProducts = $this->_getEntitiesByKey(
            $this->_apiClient->entity()->assortment(),
            $uuids
        );

        /** @var Product $product */
        foreach ($updatedProducts as $product) {
            $productType = $this->hyper['helper']['position']->getPositionTypeFromMoyskladEntity($product);
            if ($productType === PositionHelper::POSITION_TYPE_SERVICE) {
                $logMessage = "{$product->name} ({$product->id}): has type {$productType} when it is acctually a product";
                $this->log($logMessage, Log::ERROR);
                continue;
            }

            /** @var MoyskladPartHelper|MoyskladProductHelper */
            $helper = $this->hyper['helper']['moysklad' . $productType];

            $hpProductId = $helper->updateByMoyskladEntity($product, $updatedFields);
            if ((string) $hpProductId !== $product->externalCode) {
                $product->externalCode = $hpProductId;
                $product->barcodes = null; // Don't affect to the barcodes property
                $this->_apiClient->entity()->product()->update($product->id, $product);
            }
        }
    }

    /**
     * Delete products
     *
     * @param   array $uuids moysklad ids of products for remove
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     *
     * @todo    get types from database
     */
    public function productDelete(array $uuids)
    {
        /** @var Position[] $deletedPositions */
        $deletedPositions = $this->hyper['helper']['position']->findBy('uuid', $uuids);

        foreach ($deletedPositions as $uuid => $position) {
            $positionType = PositionHelper::POSITION_TYPE_SERVICE;
            if ($position->type_id === 2) {
                $positionType = PositionHelper::POSITION_TYPE_PART;
            } elseif ($position->type_id === 3) {
                $positionType = PositionHelper::POSITION_TYPE_PRODUCT;
            }

            $this->hyper['helper']['moysklad' . ucfirst($positionType)]->moveToTrashByUuid($uuid);
        }
    }

    /**
     * Create product in the MoySklad
     *
     * @param   Product $product
     *
     * @return  Product
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function createProduct(Product $product)
    {
        return $this->_createEntity($this->_apiClient->entity()->product(), $product);
    }

    /**
     * Update product in the MoySklad
     *
     * @param   Product $product
     *
     * @return  Product
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function updateProduct(Product $product)
    {
        $product->barcodes = null; // Don't affect to the barcodes property
        return $this->_updateEntity($this->_apiClient->entity()->product(), $product->getMeta()->getId(), $product);
    }

    /**
     * Update product in the MoySklad
     *
     * @param   Product[] $product
     *
     * @return  Product[]
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function massUpdateProducts(array $products)
    {
        foreach ($products as $product) {
            $product->barcodes = null; // Don't affect to the barcodes property
        }

        return $this->_massUpdateEntities($this->_apiClient->entity()->product(), $products);
    }

    /**
     * Create service
     *
     * @param   array $uuids moysklad ids of services for create
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function serviceCreate(array $uuids)
    {
        $serviceClient = $this->_apiClient->entity()->service();

        $createdServices = $this->_getEntitiesByKey(
            $serviceClient,
            $uuids
        );

        /** @var Service $service */
        foreach ($createdServices as $service) {
            $hpServiceId = $this->hyper['helper']['moyskladService']->createByMoyskladEntity($service);

            if (!empty($hpServiceId)) {
                $service->externalCode = $hpServiceId;
                $serviceClient->update($service->id, $service);
            }
        }
    }

    /**
     * Update services
     *
     * @param   array $uuids moysklad ids of services for update
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function serviceUpdate(array $uuids, array $updatedFields = [])
    {
        $serviceClient = $this->_apiClient->entity()->service();

        $updatedServices = $this->_getEntitiesByKey(
            $serviceClient,
            $uuids
        );

        /** @var Service $service */
        foreach ($updatedServices as $service) {
            $hpServiceId = $this->hyper['helper']['moyskladService']->updateByMoyskladEntity($service, $updatedFields);
            if ((string) $hpServiceId !== $service->externalCode) {
                $service->externalCode = $hpServiceId;
                $serviceClient->update($service->id, $service);
            }
        }
    }

    /**
     * Delete services
     *
     * @param   array $uuids moysklad ids of services for remove
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function serviceDelete(array $uuids)
    {
        foreach ($uuids as $uuid) {
            $this->hyper['helper']['moyskladService']->moveToTrashByUuid($uuid);
        }
    }

    /**
     * Create variant
     *
     * @param   array $keys moysklad ids of variants for create
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function variantCreate(array $keys)
    {
        $createdVariants = $this->_getEntitiesByKey(
            $this->_apiClient->entity()->assortment(),
            $keys
        );

        /** @var Variant $variant */
        foreach ($createdVariants as $variant) {
            $hpVariantId = $this->hyper['helper']['moyskladVariant']->createByMoyskladEntity($variant);

            if (!empty($hpVariantId)) {
                $variant->externalCode = $hpVariantId;
                $variant->barcodes = null; // Don't affect to the barcodes property
                $this->_apiClient->entity()->variant()->update($variant->id, $variant);
            }
        }
    }

    /**
     * Update variants
     *
     * @param   array $keys moysklad ids of products for update
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function variantUpdate(array $keys, array $updatedFields = [])
    {
        $updatedVariants = $this->_getEntitiesByKey(
            $this->_apiClient->entity()->assortment(),
            $keys
        );

        /** @var MoyskladVariantHelper */
        $moyskladVariantHelper = $this->hyper['helper']['moyskladVariant'];

        /** @var Variant $variant */
        foreach ($updatedVariants as $variant) {
            $parent = $moyskladVariantHelper->getParentFromMoyskladEntity($variant);
            if ($parent instanceof MoyskladProduct) { // don't update product variants from moysklad
                continue;
            }

            $hpVariantId = $moyskladVariantHelper->updateByMoyskladEntity($variant, $updatedFields);
            if ((string) $hpVariantId !== $variant->externalCode) {
                $variant->externalCode = $hpVariantId;
                $variant->barcodes = null; // Don't affect to the barcodes property
                $this->_apiClient->entity()->variant()->update($variant->id, $variant);
            }
        }
    }

    /**
     * Delete variant
     *
     * @param   array $uuids moysklad ids of variants for remove
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function variantDelete(array $uuids)
    {
        foreach ($uuids as $uuid) {
            $this->hyper['helper']['moyskladVariant']->moveToTrashByUuid($uuid);
        }
    }

    /**
     * Create variant in the MoySklad
     *
     * @param   Variant $variant
     *
     * @return  Variant
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function createVariant(Variant $variant)
    {
        return $this->_createEntity($this->_apiClient->entity()->variant(), $variant);
    }

    /**
     * Update variant in the MoySklad
     *
     * @param   Variant $variant
     *
     * @return  Variant
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function updateVariant(Variant $variant)
    {
        return $this->_updateEntity($this->_apiClient->entity()->variant(), $variant->getMeta()->getId(), $variant);
    }

    /**
     * Create processingplan in the MoySklad
     *
     * @param   ProcessingPlan $processingplan
     *
     * @return  ProcessingPlan
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function createProcessingplan(ProcessingPlan $processingplan)
    {
        return $this->_createEntity($this->_apiClient->entity()->processingplan(), $processingplan);
    }

    /**
     * Store create
     *
     * @param   array $uuids moysklad ids of stores for create
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function storeCreate(array $uuids)
    {
        $storeClient = $this->_apiClient->entity()->store();

        $stores = $this->_getEntitiesByKey(
            $storeClient,
            $uuids
        );

        /** @var Store $store */
        foreach ($stores as $store) {
            $hpStoreId = $this->hyper['helper']['moyskladStore']->createByMoyskladEntity($store);

            if (!empty($hpStoreId)) {
                $store->externalCode = $hpStoreId;
                $storeClient->update($store->id, $store);
            }
        }
    }

    /**
     * Update stores
     *
     * @param   array $uuids moysklad ids of stores for update
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function storeUpdate(array $uuids, array $updatedFields = [])
    {
        $storeClient = $this->_apiClient->entity()->store();

        $updatedStores = $this->_getEntitiesByKey(
            $storeClient,
            $uuids
        );

        /** @var Store $store */
        foreach ($updatedStores as $store) {
            $hpStoreId = $this->hyper['helper']['moyskladStore']->updateByMoyskladEntity($store, $updatedFields);
            if ((string) $hpStoreId !== $store->externalCode) {
                $store->externalCode = $hpStoreId;
                $storeClient->update($store->id, $store);
            }
        }
    }

    /**
     * Delete stores
     *
     * @param   array $uuids moysklad ids of stores for remove
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function storeDelete(array $uuids)
    {
        foreach ($uuids as $uuid) {
            $this->hyper['helper']['moyskladStore']->moveToTrashByUuid($uuid);
        }
    }

    /**
     * Get current free stocks
     *
     * @return  StockCurrentItem[]
     *
     * @throws  ApiClientException
     *
     * @since   2.0
     *
     * @todo    get by specific item or store
     */
    public function getFreeStocks()
    {
        return $this->_apiClient->entity()->stock()->getCurrentByStore([
            StockType::eq('freeStock') // probably there should be used "quantity"
        ]);
    }

    /**
     * Update processingplan
     *
     * @param   array $uuids moysklad ids of processingplans for update
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function processingplanUpdate(array $uuids, array $updatedFields = [])
    {
        if (empty($uuids)) {
            return;
        } else {
            if (!empty($updatedFields) && !in_array('materials', $updatedFields)) {
                $this->log('Processingplan update skipped: materials not changed');
                return; // no need to update the processingplan if its materials have not changed
            }

            if (count($uuids) > 1) {
                $this->log('Processingplan update skipped: count of uuids > 1 (' . count($uuids) . ')');
            }
        }

        /** @var Processingplan $processingplan */
        $processingplan = $this->_apiClient->entity()->processingplan()->get($uuids[0], [
            Expand::eq('materials')
        ]);

        $this->hyper['helper']['processingplan']->updateByMoyskladEntity($processingplan);
    }

    /**
     * Get entity uuid from moysklad href
     *
     * @param   string href
     *
     * @return  string
     *
     * @since 2.0
     */
    public function getEntityUuidFromEditUrl($href)
    {
        preg_match('/\/edit\?id=(.+)$/', $href, $matches);

        return $matches[1] ?? '';
    }

    /**
     * Get entity uuid from moysklad href
     *
     * @param   string href
     *
     * @return  string
     *
     * @since 2.0
     */
    public function getEntityUuidFromHref($href)
    {
        preg_match(self::API_HREF_REGEX, $href, $matches);

        return $matches[2] ?? '';
    }

    /**
     * Get entity type from moysklad href
     *
     * @param   string href
     *
     * @return  string
     *
     * @since 2.0
     */
    public function getEntityTypeFromHref($href)
    {
        preg_match(self::API_HREF_REGEX, $href, $matches);

        return $matches[1] ?? '';
    }

    /**
     * Write file log.
     *
     * @param   string $msg
     * @param   int|null $priority
     *
     * @return  void
     *
     * @since 2.0
     */
    public function log($msg, $priority = Log::INFO)
    {
        $fileName = 'log.php';
        if ($priority <= Log::WARNING) {
            $fileName = 'error.php';
        }

        $this->hyper->log(
            $msg,
            $priority,
            'moysklad/' . date('Y/m/d') . '/' . $fileName
        );
    }

    /**
     * Find conterparties by contact data
     *
     * @param   array $conditions [
     *     'email' => string,
     *     'phone' => string
     * ]
     *
     * @return  Counterparty[]
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getCounterparties(array $conditions = [])
    {
        $params = array_map(function ($condition) {
            return Search::eq((string) $condition);
        }, $conditions);

        $counterparties = $this->_apiClient->entity()->counterparty()->getList($params);

        return $counterparties->rows;
    }

    /**
     * Find conterparties by inn
     *
     * @param   string $inn
     *
     * @return  Counterparty[]
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function findCounterpartiesByInn($inn)
    {
        $counterparties = $this->_apiClient->entity()->counterparty()->getList([
            StandardFilter::eq('inn', $inn)
        ]);

        return $counterparties->rows;
    }

    /**
     * Create counterparty
     *
     * @param   Counterparty $counterparty
     *
     * @return  Counterparty
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function createCounterparty(Counterparty $counterparty)
    {
        return $this->_createEntity($this->_apiClient->entity()->counterparty(), $counterparty);
    }

    /**
     * Update counterparty
     *
     * @param   Counterparty $counterparty
     *
     * @return  Counterparty
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function updateCounterparty(Counterparty $counterparty)
    {
        return $this->_updateEntity($this->_apiClient->entity()->counterparty(), $counterparty->id, $counterparty);
    }

    /**
     * Create customerOrder
     *
     * @param   CustomerOrder $counterparty
     *
     * @return  CustomerOrder
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function createCustomerOrder(CustomerOrder $customerOrder)
    {
        return $this->_createEntity($this->_apiClient->entity()->customerorder(), $customerOrder);
    }

    /**
     * CustomerOrder create hook handler
     *
     * @param   array $uuids
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function customerorderCreate(array $uuids)
    {
        if (empty($uuids)) {
            return;
        }

        $customerOrders = $this->_apiClient->entity()->customerorder()->getList(array_map(function ($uuid) {
            return StandardFilter::eq('id', $uuid);
        }, $uuids));

        /** @var CustomerOrder $customerOrder */
        foreach ($customerOrders->rows as $customerOrder) {
            if (is_numeric($customerOrder->externalCode)) {
                continue; // already bound to the site order
            }

            $crmLeadId = null;

            $crmLinkAttribute = $this->hyper['helper']['moyskladCustomerOrder']->findAmoLeadLinkAttribute($customerOrder);
            if ($crmLinkAttribute && !empty($crmLinkAttribute->value)) {
                $crmLeadId = $this->hyper['helper']['crm']->getLeadIdFromUrl($crmLinkAttribute->value);
            }

            /** @var DealMapHelper $dealMapHelper */
            $dealMapHelper = $this->hyper['helper']['dealMap'];

            $moyskladUuid = $customerOrder->getMeta()->getId();

            if ($crmLeadId) {
                $dealMapHelper->bindMoyskladOrderToCrmLead($moyskladUuid, $crmLeadId);
            } else {
                $dealMapHelper->addMoyskladOrderUuid($moyskladUuid);
            }
        }
    }

    /**
     * CustomerOrder update hook handler
     *
     * @param   array $uuids
     * @param   array $updatedFields list of updated fields
     *
     * @return  void
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function customerorderUpdate(array $uuids, array $updatedFields = [])
    {
        if (empty($uuids)) {
            return;
        }

        if (count($updatedFields) === 1 && $updatedFields[0] === 'operationLink') {
            return; // do nothing if only operationLink has been updated
        }

        foreach ($uuids as $uuid) {
            /** @var CustomerOrder $customerOrder */
            $customerOrder = $this->_apiClient->entity()->customerorder()->get($uuid, [
                Expand::eq('positions.assortment'),
            ]);

            if (is_numeric($customerOrder->externalCode)) {
                try {
                    $updatedOrder = $this->hyper['helper']['order']->updateByMoyskladEntity($customerOrder);
                    $this->hyper['helper']['configuration']->updateServicesFromOrder($updatedOrder);
                } catch (\Throwable $th) {
                    $this->log(__FUNCTION__ . ' throws error: ' . $th->getMessage() . ' in ' . $th->getFile() . ' on line ' . $th->getLine());
                }
            } else {
                $crmLinkAttribute = $this->hyper['helper']['moyskladCustomerOrder']->findAmoLeadLinkAttribute($customerOrder);
                if ($crmLinkAttribute && !empty($crmLinkAttribute->value)) {
                    $crmLeadId = $this->hyper['helper']['crm']->getLeadIdFromUrl($crmLinkAttribute->value);

                    if (!empty($crmLeadId)) {
                        $this->hyper['helper']['dealMap']->bindCrmLeadToMoyskladOrder($crmLeadId, $customerOrder->getMeta()->getId());
                    }
                }
            }
        }
    }

    /**
     * Update customerorder
     *
     * @param   CustomerOrder $counterparty
     *
     * @return  CustomerOrder
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function updateCustomerorder(CustomerOrder $customerorder)
    {
        return $this->_updateEntity($this->_apiClient->entity()->customerorder(), $customerorder->getMeta()->getId(), $customerorder);
    }

    /**
     * Find customerorders by inn
     *
     * @param   int $externalCode
     *
     * @return  CustomerOrder[]
     *
     * @throws  ApiClientException
     * @throws  Exception
     *
     * @since   2.0
     */
    public function findCustomerordersByExternalCode($externalCode)
    {
        $customerorders = $this->_apiClient->entity()->customerorder()->getList([
            StandardFilter::eq('externalCode', $externalCode)
        ]);

        return $customerorders->rows;
    }

    /**
     * Updates characteristic list.
     *
     * @return  int updated characteristics count
     *
     * @throws  \Exception
     */
    public function updateCharacteristicList(): int
    {
        try {
            $metadata = $this->_apiClient->entity()->variant()->getMetadata();
            $characteristics = $metadata->characteristics;
        } catch (\Throwable $th) {
            return 0;
        }

        $data = \array_map(fn($characteristic) => [
                'uuid' => $characteristic->id,
                'name' => $characteristic->name,
            ], $characteristics
        );

        /** @var \HyperPcTableMoysklad_Characteristics */
        $table = Table::getInstance('Moysklad_Characteristics');
        $updated = 0;

        foreach ($data as $value) {
            $result = $table->save(
                src: $value,
                ignore: 'params'
            );

            if ($result) {
                $updated++;
            } else {
                $this->hyper['cms']->enqueueMessage($table->getError());
            }
        }

        return $updated;
    }

    /**
     * Updates characteristics values for all variants.
     *
     * @return int values total count
     */
    public function updateAllCharacteristicValues(): int
    {
        /** @var \HYPERPC\Helper\MoyskladVariantHelper $variantsHelper */
        $variantsHelper = $this->hyper['helper']['moyskladVariant'];

        $variants = $variantsHelper->findAll([
            'select'=> ['a.id', 'a.uuid']
        ]);

        if (empty($variants)) {
            return 0;
        }

        $characteristicsTotal = 0;

        $uuids = \array_map(fn($variant) => $variant->uuid, $variants);
        $uuids = \array_chunk($uuids, 100);
        foreach ($uuids as $chunk) {
            $moyskladVariants = $this->_getEntitiesByKey(
                $this->_apiClient->entity()->assortment(), 
                $chunk
            );

            foreach ($moyskladVariants as $moyskladVariant) {
                $characteristicsTotal += $variantsHelper->updateCharacteristicsFromMoyskladEntity($moyskladVariant);
            }

            \sleep(1);
        }

        return $characteristicsTotal;
    }

    /**
     * Update customerorder status list
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function updateStatusList()
    {
        try {
            $metadata = $this->_apiClient->entity()->customerorder()->getMetadata();
            $statusList = $metadata->states;
        } catch (\Throwable $th) {
            return false;
        }

        $json = json_encode($statusList, JSON_PRETTY_PRINT);

        return File::write($this->_getStatusListFilePath(), $json);
    }

    /**
     * Build entity href
     *
     * @param   string $type entity type
     * @param   string $uuid entity moysklad id
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _buildEntityHref($type, $uuid)
    {
        $segments = [
            $this->getApiPath(),
            'entity',
            $type,
            $uuid
        ];

        return join('/', $segments);
    }

    /**
     * Create moysklad entity
     *
     * @param   EntityClientBase $client
     * @param   MetaEntity|MoyskladMetaEntity $newEntity
     *
     * @return  MetaEntity
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _createEntity(EntityClientBase $client, $newEntity)
    {
        if (!$this->_createEntitiesEnabled) {
            throw new Exception('Creation of moysklad entities is disabled in the component parameters');
        }

        return $client->create($newEntity);
    }

    /**
     * Get entities by key
     *
     * @param   EntityClientBase $client
     * @param   array $keys
     *
     * @return  MetaEntity[]
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getEntitiesByKey(EntityClientBase $client, array $keys)
    {
        $params = [];
        if ($client instanceof AssortmentClient) {
            $params = [ // Load archived positions too
                StandardFilter::eq('archived', 'true'),
                StandardFilter::eq('archived', 'false'),
            ];
        }

        $entityList = $client->getList(array_merge($params, array_map(function ($key) {
            return StandardFilter::eq('id', $key);
        }, $keys)));

        return $entityList->rows;
    }

    /**
     * Get status list file path
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getStatusListFilePath()
    {
        $dirPath  = Path::clean(JPATH_ROOT . '/tmp/moysklad/');
        $fileName = 'status-list.json';

        return $dirPath.$fileName;
    }

    /**
     * Update moysklad entity
     *
     * @param   EntityClientBase $client
     * @param   string $id
     * @param   MetaEntity|MoyskladMetaEntity $updatedEntity
     *
     * @return  MetaEntity
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _updateEntity(EntityClientBase $client, string $id, $updatedEntity)
    {
        if (!$this->_createEntitiesEnabled) {
            throw new Exception('Update of moysklad entities is disabled in the component parameters');
        }

        return $client->update($id, $updatedEntity);
    }

    /**
     * Mass update moysklad entities
     *
     * @param   EntityClientBase $client
     * @param   MetaEntity[]|MoyskladMetaEntity[] $entities
     *
     * @return  MetaEntity[]
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _massUpdateEntities(EntityClientBase $client, array $entities)
    {
        if (!$this->_createEntitiesEnabled) {
            throw new Exception('Update of moysklad entities is disabled in the component parameters');
        }

        $client->massUpdate($entities);
    }
}
