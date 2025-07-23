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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Object\PriceList\OfferData;
use HYPERPC\Object\PriceList\OfferCollection;
use HYPERPC\XML\PriceList\Elements\YMLPriceList;

/**
 * Class ElementPriceListOzon
 *
 * @since 2.0
 */
class ElementPriceListOzon extends YMLPriceList
{
    /**
     * Post-process offers
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessOffers(OfferCollection $offers): OfferCollection
    {
        return new OfferCollection(array_map(function ($offer) {
            /** @var OfferData $offer */

            if ($offer->oldPrice === null) {
                $offer->oldPrice = $offer->price;
            }

            $offer->minPrice = $offer->price;

            return $offer;
        }, $offers->items()));
    }

    /**
     * Post-process part offers
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessPartOffers(OfferCollection $offers): OfferCollection
    {
        return $this->_postprocessOffers($offers);
    }

    /**
     * Post-process product offers
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessProductOffers(OfferCollection $offers): OfferCollection
    {
        return $this->_postprocessOffers($offers);
    }

    /**
     * Post-process service offers
     *
     * @param   OfferCollection $offers
     *
     * @return  OfferCollection
     *
     * @since   2.0
     */
    protected function _postprocessServiceOffers(OfferCollection $offers): OfferCollection
    {
        return $this->_postprocessOffers($offers);
    }
}
