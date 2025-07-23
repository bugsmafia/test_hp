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

use HYPERPC\MoySklad\Http\RequestExecutor;
use HYPERPC\MoySklad\Entity\StockCurrentItem;
use MoySklad\Client\StockClient as BaseStockClient;

/**
 * Class StockClient
 *
 * @package     HYPERPC\MoySklad\Client
 *
 * @copyright   HYPERPC
 * @author      Artem Vyshnevskiy
 *
 * @since       2.0
 */
class StockClient extends BaseStockClient
{
    /**
     * Get current stock by store
     *
     * @param   Param[] $params
     *
     * @return  StockCurrentItem[]
     *
     * @throws  ApiClientException
     *
     * @since   2.0
     *
     * @see     https://dev.moysklad.ru/doc/api/remap/1.2/reports/#otchety-otchet-ostatki-tekuschie-ostatki
     */
    public function getCurrentByStore(array $params = [])
    {
        $stocks = RequestExecutor::path($this->getApi(), $this->getPath() . '/bystore/current')->params($params)->get('array<HYPERPC\MoySklad\Entity\StockCurrentItem>');

        return $stocks;
    }
}
