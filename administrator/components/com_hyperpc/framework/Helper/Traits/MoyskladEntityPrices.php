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

namespace HYPERPC\Helper\Traits;

use Joomla\Registry\Registry;
use MoySklad\Entity\MetaEntity;

/**
 * Trait MoyskladEntityPrices
 *
 * @package HYPERPC\Helper\Traits
 *
 * @since   2.0
 */
trait MoyskladEntityPrices
{

    /**
     * Get list price from Moysklad entity
     *
     * @param   MetaEntity $position
     *
     * @return  float
     *
     * @since   2.0
     */
    protected function _getListPriceFromMoyskladEntity(MetaEntity $entity)
    {
        $prices = $this->_getPricesFromMoyskladEntity($entity);
        return $prices->get('listPrice', 0.0);
    }

    /**
     * Get sale price from Moysklad entity
     *
     * @param   MetaEntity $position
     *
     * @return  float
     *
     * @since   2.0
     */
    protected function _getSalePriceFromMoyskladEntity(MetaEntity $entity)
    {
        $prices = $this->_getPricesFromMoyskladEntity($entity);
        return $prices->get('salePrice', 0.0);
    }

    /**
     * Get prices from Moysklad entity
     *
     * @param   MetaEntity $entity
     *
     * @return  Registry
     *
     * @since   2.0
     */
    private function _getPricesFromMoyskladEntity(MetaEntity $entity)
    {
        $result = new Registry([
            'listPrice' => 0.0,
            'salePrice' => 0.0
        ]);

        $prices = $entity->salePrices;
        if (is_array($prices) && count($prices)) {
            $result->set('listPrice', $prices[0]->value / 100);
            if (count($prices) >= 2) {
                $result->set('salePrice', $prices[1]->value / 100);
            }
        }

        return $result;
    }
}
