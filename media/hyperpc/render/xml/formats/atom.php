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

use Joomla\CMS\Uri\Uri;
use HYPERPC\Helper\DateHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Object\PriceList\PriceListData;

/**
 * @var RenderHelper    $this
 * @var PriceListData   $priceListData
 */

/** @var DateHelper $dateHelper */
$dateHelper = $this->hyper['helper']['date'];
$date = $dateHelper->getCurrentDateTime();

$priceListItemsData = $priceListData->offers;

$items = [];
foreach ($priceListItemsData as $offer) {
    $itemContent = [
        "\t\t" . '<g:id>' . $offer->id . '</g:id>',
        "\t\t" . '<g:title>' . $offer->title . '</g:title>',
        "\t\t" . '<g:description><![CDATA[' . $offer->description . ']]></g:description>',
        "\t\t" . '<g:link>' . htmlentities($offer->link) . '</g:link>',
        "\t\t" . '<g:image_link>' . htmlentities($offer->imageLink) . '</g:image_link>',
        "\t\t" . '<g:price>' . $offer->price . ' ' . $priceListData->currencyId . '</g:price>',
        "\t\t" . '<g:condition>' . $offer->condition . '</g:condition>'
    ];

    foreach (['availability', 'shortTitle', 'googleProductCategory'] as $prop) {
        if (property_exists($offer, $prop) && !empty($offer->$prop)) {
            $propName = strtolower(preg_replace('/(?<!^)[A-Z]/m', '_$0', $prop));

            $itemContent[] = "\t\t" . "<g:{$propName}>{$offer->$prop}</g:{$propName}>";
        }
    }

    if ($offer->barcode) {
        $barcodes = explode(',', $offer->barcode);
        foreach ($barcodes as $barcode) {
            $itemContent[] = "\t\t" . '<g:gtin>' . $barcode . '</g:gtin>';
        }
    }

    if ($offer->availabilityDate) {
        $itemContent[] = "\t\t" . '<g:availability_date>' . $offer->availabilityDate->toISO8601(true) . '</g:availability_date>';
    }

    if ($offer->typePrefix) {
        $itemContent[] = "\t\t" . '<g:productType>' . $offer->typePrefix . '</g:productType>';
    }

    if ($offer->vendorCode) {
        $itemContent[] = "\t\t" . '<g:mpn>' . $offer->vendorCode . '</g:mpn>';
    }

    if ($offer->vendor) {
        $itemContent[] = "\t\t" . '<g:brand>' . $offer->vendor . '</g:brand>';
    }

    if ($offer->measurements) {
        $itemContent[] = "\t\t" . '<g:shipping_weight>' . $offer->measurements->weight . ' kg</g:shipping_weight>';
    }

    if ($offer->params) {
        foreach ($offer->params as $param) {
            $itemContent[] = "\t\t" . '<g:product_highlight>' . $param->name . ': ' . $param->value . '</g:product_highlight>';
        }
    }

    $items[] = implode(PHP_EOL, [
        "\t" . '<entry>',
        implode(PHP_EOL, $itemContent),
        "\t" . '</entry>'
    ]);
}

$xml = [
    '<?xml version="1.0"?>',
    '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">',
    "\t" . '<title>' . $this->hyper['params']->get('yandex_market_company') . '</title>',
    "\t" . '<link rel="self" href="' . Uri::root() . '"/>',
    "\t" . '<updated>' . $date->toISO8601(true) . '</updated>',
    implode(PHP_EOL, $items),
    '</feed>',
    ''
];

echo implode(PHP_EOL, $xml);
