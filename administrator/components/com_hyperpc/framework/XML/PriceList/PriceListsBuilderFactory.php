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

namespace HYPERPC\XML\PriceList;

use HYPERPC\App;
use HYPERPC\XML\PriceList\Elements\ElementsPriceListsBuilder;

/**
 * PriceListsBuilderFactory factory
 *
 * @package HYPERPC\XML\PriceList
 *
 * @since   2.0
 */
final class PriceListsBuilderFactory
{
    private App $hyper;

    public function __construct()
    {
        $this->hyper = App::getInstance();
    }

    public function createBuilder(): PriceListsBuilderInterface
    {
        return new ElementsPriceListsBuilder();
    }
}
