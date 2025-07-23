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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\DeliveryHelper;

/**
 * @var RenderHelper $this
 */
?>

<div class="uk-margin">
    <div class="uk-inline uk-width-1-1">
        <span class="uk-form-icon" data-uk-icon="location"></span>
        <input type="text" class="jsGeoCityInput uk-input" placeholder="<?= Text::_('COM_HYPERPC_DELIVERY_YOUR_CITY') ?>" />
    </div>
</div>
<div class="uk-margin jsGeoPredefinedLocations">
    <?php
    /** @var DeliveryHelper $deliverHelper */
    $deliverHelper = $this->hyper['helper']['delivery'];
    $cities = $deliverHelper->getPredefinedCities();
    $citiesHtml = [];
    foreach ($cities as $cityData) {
        $citiesHtml[] = '<span data-country="'. $cityData->countryName .'" data-fias-id="' . $cityData->fiasId . '" data-geoid="' . $cityData->geoId .'" class="uk-link uk-text-muted">' . $cityData->name . '</span>';
    }
    echo implode(', ', $citiesHtml);
    ?>
</div>
