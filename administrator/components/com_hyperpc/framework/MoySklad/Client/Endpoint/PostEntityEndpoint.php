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
use MoySklad\Entity\MetaEntity;
use HYPERPC\MoySklad\Http\RequestExecutor;
use MoySklad\Util\Exception\ApiClientException;

trait PostEntityEndpoint
{
    /**
     * @param MetaEntity $newEntity
     * @return MetaEntity
     * @throws ApiClientException
     * @throws \Exception
     */
    public function create(MetaEntity $newEntity): MetaEntity
    {
        if (!is_subclass_of($this, EntityClientBase::class)) {
            throw new \Exception('The trait cannot be used outside the EntityClientBase class');
        }

        return RequestExecutor::path($this->getApi(), $this->getPath())->body($newEntity)->post($this->getMetaEntityClass());
    }
}
