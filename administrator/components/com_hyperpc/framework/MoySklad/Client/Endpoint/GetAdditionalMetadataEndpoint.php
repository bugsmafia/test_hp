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

namespace HYPERPC\MoySklad\Client\Endpoint;

use MoySklad\Client\EntityClientBase;
use HYPERPC\MoySklad\Http\RequestExecutor;
use MoySklad\Entity\Metadata\AdditionMetadata;
use MoySklad\Util\Exception\ApiClientException;

trait GetAdditionalMetadataEndpoint
{
    /**
     * @return AdditionMetadata
     *
     * @throws ApiClientException
     * @throws \Exception
     */
    public function getMetadata(): AdditionMetadata
    {
        if (!is_subclass_of($this, EntityClientBase::class)) {
            throw new \Exception('The trait cannot be used outside the EntityClientBase class');
        }

        /** @var AdditionMetadata $metadata */
        $metadata = RequestExecutor::path($this->getApi(), $this->getPath().'metadata')->get(AdditionMetadata::class);

        return $metadata;
    }
}
