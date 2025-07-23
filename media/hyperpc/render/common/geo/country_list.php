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
 * @var RenderHelper $this
 */
?>

<ul class="uk-list jsGeoPredefinedLocations">
    <?php
    /** @var DeliveryHelper $deliverHelper */
    $deliverHelper = $this->hyper['helper']['delivery'];
    $cities = $deliverHelper->getPredefinedCities();
    $citiesHtml = [];
    foreach ($cities as $cityData) {
        $citiesHtml[] = '<li data-country="'. $cityData->countryName .'" data-fias-id="" data-geoid="' . $cityData->geoId .'" class="uk-link uk-text-muted">' . $cityData->name . '</li>';
    }
    echo implode('', $citiesHtml);
    ?>
</ul>
