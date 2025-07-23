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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Object\PriceList\OfferCollection;
use HYPERPC\XML\PriceList\Elements\RssPriceList;

/**
 * Class ElementPriceListCriteo
 *
 * @since 2.0
 */
class ElementPriceListCriteo extends RssPriceList
{

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
        return new OfferCollection(array_map(function ($offer) {
            $utmParams = [
                'utm_medium'   => 'cpc',
                'utm_source'   => 'criteo',
                'utm_term'     => $offer->id,
                'utm_campaign' => 'accessories_HYPERPC'
            ];

            $offer->link .= (strpos($offer->link, '?') === false ? '?' : '&') . http_build_query($utmParams);

            return $offer;
        }, $offers->items()));
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
        return new OfferCollection(array_map(function ($offer) {
            $utmParams = [
                'utm_medium'   => 'cpc',
                'utm_source'   => 'criteo',
                'utm_term'     => $offer->id,
                'utm_campaign' => 'computers_HYPERPC'
            ];

            $offer->link .= (strpos($offer->link, '?') === false ? '?' : '&') . http_build_query($utmParams);

            return $offer;
        }, $offers->items()));
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
        return new OfferCollection(array_map(function ($offer) {
            $utmParams = [
                'utm_medium'   => 'cpc',
                'utm_source'   => 'criteo',
                'utm_term'     => $offer->id,
                'utm_campaign' => 'services_HYPERPC'
            ];

            $offer->link .= (strpos($offer->link, '?') === false ? '?' : '&') . http_build_query($utmParams);

            return $offer;
        }, $offers->items()));
    }
}
