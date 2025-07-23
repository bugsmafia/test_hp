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
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var RenderHelper    $this
 * @var PriceListData   $priceListData
 */

/** @var DateHelper $dateHelper */
$dateHelper = $this->hyper['helper']['date'];
$date = $dateHelper->getCurrentDateTime();

$priceListCategoriesData = $priceListData->categories ?? [];

$categories = [];
foreach ($priceListCategoriesData as $category) {
    $parent = $category->parentId ? ' parentId="' . $category->parentId . '"' : '';
    $categories[] = "\t\t\t" . '<category id="' . $category->id . '"' . $parent  . '>' . $category->title . '</category>';
}

$priceListOffersData = $priceListData->offers;

$offers = [];
foreach ($priceListOffersData as $offer) {
    $offerContent = [
        "\t\t\t\t" . '<name>' . $offer->title . '</name>',
        "\t\t\t\t" . '<price>' . $offer->price . '</price>',
        "\t\t\t\t" . '<currencyId>' . $priceListData->currencyId . '</currencyId>',
        "\t\t\t\t" . '<categoryId>' . $offer->categoryId . '</categoryId>',
        "\t\t\t\t" . '<manufacturer_warranty>' . ($offer->manufacturerWarranty ? 'true' : 'false') . '</manufacturer_warranty>',
        "\t\t\t\t" . '<delivery>' . ($offer->delivery ? 'true' : 'false') . '</delivery>',
        "\t\t\t\t" . '<pickup>' . ($offer->pickup ? 'true' : 'false') . '</pickup>',
        "\t\t\t\t" . '<url>' . htmlentities($offer->link) . '</url>',
        "\t\t\t\t" . '<picture>' . htmlentities($offer->imageLink) . '</picture>',
        "\t\t\t\t" . '<description><![CDATA[' . $offer->description . ']]></description>',
    ];

    if ($offer->oldPrice !== null) {
        $offerContent[] = "\t\t\t\t" . "<oldprice>{$offer->oldPrice}</oldprice>";
    }

    if ($offer->minPrice !== null) {
        $offerContent[] = "\t\t\t\t" . "<min_price>{$offer->minPrice}</min_price>";
    }

    foreach (['vendorCode', 'vendor', 'model', 'typePrefix', 'barcode'] as $prop) {
        if (property_exists($offer, $prop) && !empty($offer->$prop)) {
            $offerContent[] = "\t\t\t\t" . "<{$prop}>{$offer->$prop}</{$prop}>";
        }
    }

    if ($offer->measurements) {
        $dimensions = [
            $offer->measurements->dimensions->length,
            $offer->measurements->dimensions->width,
            $offer->measurements->dimensions->height
        ];

        $offerContent[] = "\t\t\t\t" . '<weight>' . $offer->measurements->weight . '</weight>';
        $offerContent[] = "\t\t\t\t" . '<dimensions>' . implode('/', $dimensions) . '</dimensions>';
    }

    if ($offer->salesNotes) {
        $offerContent[] = "\t\t\t\t" . '<sales_notes>' . $offer->salesNotes . '</sales_notes>';
    }

    if ($offer->params) {
        foreach ($offer->params as $param) {
            $offerContent[] = "\t\t\t\t" . '<param name="' . $param->name . '">' . $param->value . '</param>';
        }
    }

    $offerAttrs = [
        'id' => $offer->id
    ];

    if ($offer->availability && $offer->availability === Stockable::AVAILABILITY_INSTOCK) {
        $offerAttrs['available'] = 'true';
    }

    if ($offer->vendor && $offer->model && $offer->typePrefix) {
        $offerAttrs['type'] = 'vendor.model';
    }

    $offers[] = implode(PHP_EOL, [
        "\t\t\t" . '<offer ' . $this->hyper['helper']['html']->buildAttrs($offerAttrs) . '>',
        implode(PHP_EOL, $offerContent),
        "\t\t\t" . '</offer>'
    ]);
}

$xml = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<yml_catalog date="' . $date->toISO8601(true) . '">',
        "\t" . '<shop>',
            "\t\t" . '<name>' . $this->hyper['params']->get('yandex_market_name') . '</name>',
            "\t\t" . '<company>' . $this->hyper['params']->get('yandex_market_company') . '</company>',
            "\t\t" . '<url>' . Uri::root() . '</url>',
            "\t\t" . '<currencies>',
            "\t\t\t" . '<currency id="' . $priceListData->currencyId . '" rate="1"/>',
            "\t\t" . '</currencies>',
            "\t\t" . '<categories>',
            implode(PHP_EOL, $categories),
            "\t\t" . '</categories>',
            "\t\t" . '<offers>',
            implode(PHP_EOL, $offers),
            "\t\t" . '</offers>',
        "\t" . '</shop>',
    '</yml_catalog>',
    ''
];

echo implode(PHP_EOL, $xml);
