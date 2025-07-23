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

namespace HYPERPC\MoySklad\Client\Document;

use HYPERPC\MoySklad\Entity\Document\CustomerOrder;
use HYPERPC\MoySklad\Client\Endpoint\DeleteEntitiesEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\DeleteEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetAdditionalMetadataEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetEntitiesListEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetMetadataAttributeEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\PostEntitiesEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\PostEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\PutEntityEndpoint;
use MoySklad\Client\CustomerOrderClient as CustomerOrderClientBase;

class CustomerorderClient extends CustomerOrderClientBase
{
    use
        GetAdditionalMetadataEndpoint,
        GetEntitiesListEndpoint,
        GetEntityEndpoint,
        PutEntityEndpoint,
        PostEntityEndpoint,
        DeleteEntityEndpoint,
        GetMetadataAttributeEndpoint,
        PostEntitiesEndpoint,
        DeleteEntitiesEndpoint;

    /**
     * @return string
     */
    protected function getMetaEntityClass(): string
    {
        return CustomerOrder::class;
    }
}
