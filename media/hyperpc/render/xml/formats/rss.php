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
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Object\PriceList\PriceListData;

/**
 * @var RenderHelper    $this
 * @var PriceListData   $priceListData
 */

$priceListItemsData = $priceListData->offers;

$items = [];
foreach ($priceListItemsData as $offer) {
    $itemContent = [
        "\t\t\t" . '<g:id>' . $offer->id . '</g:id>',
        "\t\t\t" . '<g:title>' . $offer->title . '</g:title>',
        "\t\t\t" . '<g:description><![CDATA[' . $offer->description . ']]></g:description>',
        "\t\t\t" . '<g:link>' . htmlentities($offer->link) . '</g:link>',
        "\t\t\t" . '<g:image_link>' . htmlentities($offer->imageLink) . '</g:image_link>',
        "\t\t\t" . '<g:price>' . $offer->price . ' ' . $priceListData->currencyId . '</g:price>',
        "\t\t\t" . '<g:brand>' . $offer->vendor . '</g:brand>',
    ];

    $items[] = implode(PHP_EOL, [
        "\t\t" . '<item>',
        implode(PHP_EOL, $itemContent),
        "\t\t" . '</item>'
    ]);
}

$xml = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">',
    "\t" . '<channel>',
    "\t\t" . '<title>' . $this->hyper['params']->get('yandex_market_name') . '</title>',
    "\t\t" . '<link>' . Uri::root() . '</link>',
    implode(PHP_EOL, $items),
    "\t" . '</channel>',
    '</rss>',
    ''
];

echo implode(PHP_EOL, $xml);
