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

use MoySklad\Util\Param\Param;
use MoySklad\Entity\ListEntity;
use MoySklad\Client\EntityClientBase;
use MoySklad\Entity\AbstractListEntity;
use HYPERPC\MoySklad\Http\RequestExecutor;
use MoySklad\Util\Exception\ApiClientException;

/**
 * Trait GetEntitiesListEndpoint
 *
 * @package HYPERPC\MoySklad\Client\Endpoint
 *
 * @since 2.0
 */
trait GetEntitiesListEndpoint
{
    /**
     * @param Param[] $params
     * @return ListEntity
     * @throws ApiClientException
     * @throws \Exception
     */
    public function getList(array $params = []): AbstractListEntity
    {
        if (!is_subclass_of($this, EntityClientBase::class)) {
            throw new \Exception('The trait cannot be used outside the EntityClientBase class');
        }

        /** @var ListEntity */
        $listEntity = RequestExecutor::path(
            $this->getApi(),
            $this->getPath()
        )
        ->params($params)
        ->get($this->getListEntityClass());

        return $listEntity;
    }

    /**
     * Класс списка для данной выборки
     *
     * @return string|AbstractListEntity
     */
    protected function getListEntityClass() : string
    {
        return ListEntity::class;
    }
}
