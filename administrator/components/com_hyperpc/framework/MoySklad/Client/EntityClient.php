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

namespace HYPERPC\MoySklad\Client;

use HYPERPC\MoySklad\ApiClient;
use HYPERPC\MoySklad\Client\StockClient;
use HYPERPC\MoySklad\Client\StoreClient;
use HYPERPC\MoySklad\Client\WebHookClient;
use HYPERPC\MoySklad\Client\VariantClient;
use HYPERPC\MoySklad\Client\AssortmentClient;
use HYPERPC\MoySklad\Client\CounterpartyClient;
use HYPERPC\MoySklad\Client\Document\LossClient;
use HYPERPC\MoySklad\Client\Document\MoveClient;
use HYPERPC\MoySklad\Client\ProductFolderClient;
use HYPERPC\MoySklad\Client\Product\BundleClient;
use HYPERPC\MoySklad\Client\Product\ProductClient;
use HYPERPC\MoySklad\Client\Product\ServiceClient;
use MoySklad\Client\EntityClient as EntityClientBase;
use HYPERPC\MoySklad\Client\Document\CustomerorderClient;
use HYPERPC\MoySklad\Client\Document\ProcessingPlanClient;

/**
 * Class EntityClient
 *
 * @package HYPERPC\MoySklad\Client
 *
 * @since 2.0
 */
class EntityClient extends EntityClientBase
{
    /**
     * @var ApiClient
     */
    private $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        parent::__construct($api);
    }

    /**
     * @return LossClient
     */
    public function loss(): LossClient
    {
        return new LossClient($this->api);
    }

    /**
     * @return MoveClient
     */
    public function move(): MoveClient
    {
        return new MoveClient($this->api);
    }

    /**
     * @return StockClient
     */
    public function stock(): StockClient
    {
        return new StockClient($this->api);
    }

    /**
     * @return CounterpartyClient
     */
    public function counterparty(): CounterpartyClient
    {
        return new CounterpartyClient($this->api);
    }

    /**
     * @return CustomerOrderClient
     */
    public function customerorder(): CustomerOrderClient
    {
        return new CustomerOrderClient($this->api);
    }

    /**
     * @return ProcessingPlanClient
     */
    public function processingplan(): ProcessingPlanClient
    {
        return new ProcessingPlanClient($this->api);
    }

    /**
     * @return WebHookClient
     */
    public function webhook(): WebHookClient
    {
        return new WebHookClient($this->api);
    }

    /**
     * @return AssortmentClient
     */
    public function assortment(): AssortmentClient
    {
        return new AssortmentClient($this->api);
    }

    /**
     * @return ProductClient
     */
    public function product(): ProductClient
    {
        return new ProductClient($this->api);
    }

    /**
     * @return ServiceClient
     */
    public function service(): ServiceClient
    {
        return new ServiceClient($this->api);
    }

    /**
     * @return BundleClient
     */
    public function bundle(): BundleClient
    {
        return new BundleClient($this->api);
    }

    /**
     * @return ProductFolderClient
     */
    public function productfolder(): ProductFolderClient
    {
        return new ProductFolderClient($this->api);
    }

    /**
     * @return VariantClient
     */
    public function variant(): VariantClient
    {
        return new VariantClient($this->api);
    }

    /**
     * @return StoreClient
     */
    public function store(): StoreClient
    {
        return new StoreClient($this->api);
    }
}
