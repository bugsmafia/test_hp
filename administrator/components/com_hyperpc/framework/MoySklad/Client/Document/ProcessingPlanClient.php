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

use HYPERPC\MoySklad\ApiClient;
use MoySklad\Client\EntityClientBase;
use HYPERPC\MoySklad\Entity\Document\ProcessingPlan;
use HYPERPC\MoySklad\Client\Endpoint\GetEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\PutEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\PostEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetMetadataEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\DeleteEntityEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\PostEntitiesEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\DeleteEntitiesEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetEntitiesListEndpoint;
use HYPERPC\MoySklad\Client\Endpoint\GetMetadataAttributeEndpoint;

/**
 * Class ProcessingPlanClient
 *
 * @package HYPERPC\MoySklad\Client\Document
 *
 * @since   2.0
 */
final class ProcessingPlanClient extends EntityClientBase
{
    use
        GetEntitiesListEndpoint,
        PostEntityEndpoint,
        PostEntitiesEndpoint,
        DeleteEntityEndpoint,
        DeleteEntitiesEndpoint,
        GetMetadataEndpoint,
        GetMetadataAttributeEndpoint,
        GetEntityEndpoint,
        PutEntityEndpoint;

    /**
     * ProcessingPlanClient constructor.
     *
     * @param ApiClient $api
     */
    public function __construct(ApiClient $api)
    {
        parent::__construct($api, '/entity/processingplan/');
    }

    /**
     * @return string
     */
    protected function getMetaEntityClass(): string
    {
        return ProcessingPlan::class;
    }
}
