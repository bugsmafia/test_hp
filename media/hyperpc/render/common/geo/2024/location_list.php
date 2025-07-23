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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\DeliveryHelper;

/**
 * @var     RenderHelper    $this
 * @var     ?string         $ulCssClass
 */

$ulCssClass = 'jsGeoPredefinedLocations' . (!empty($ulCssClass) ? ' ' . $ulCssClass : '');
?>

<ul class="<?= $ulCssClass ?>">
    <?php
    /** @var DeliveryHelper $deliverHelper */
    $deliverHelper = $this->hyper['helper']['delivery'];
    $cities = $deliverHelper->getPredefinedCities();
    $citiesHtml = [];
    foreach ($cities as $cityData) {
        $citiesHtml[] =
            "<li data-country=\"{$cityData->countryName}\" data-fias-id=\"{$cityData->fiasId}\" data-geoid=\"{$cityData->geoId}\"><a href=\"#\">{$cityData->name}</a></li>";
    }
    echo implode(PHP_EOL, $citiesHtml);
    ?>
</ul>
