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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\XML\PriceList\Elements;

use HYPERPC\Elements\Manager as ElementsManager;
use HYPERPC\XML\PriceList\PriceListsBuilderInterface;

/**
 * Interface PriceList
 *
 * @package HYPERPC\XML\PriceList
 *
 * @since   2.0
 */
class ElementsPriceListsBuilder implements PriceListsBuilderInterface
{
    /**
     * @var priceListInterface[]
     */
    protected array $_priceLists;

    public function __construct()
    {
        $this->_priceLists = ElementsManager::getInstance()->getByPosition(ElementsManager::ELEMENT_TYPE_PRICE_LIST);
    }

    public function buildPriceLists()
    {
        foreach ($this->_priceLists as $priceList) {
            $priceList->export();
        }
    }
}
