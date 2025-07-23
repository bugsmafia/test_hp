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

/**
 * @var         RenderHelper    $this
 * @var         string          $targetId
 */

$geoType = $this->hyper['params']->get('geo_type', 'cities');
$targetId = isset($targetId) ? $targetId : 'location-modal';
?>

<?php if ($geoType === 'cities') : ?>
    <div id="<?= $targetId ?>" class="uk-modal" data-uk-modal>
        <div class="jsGeoCity tm-background-gray-15 uk-card uk-card-small uk-card-body uk-modal-dialog uk-width-large">
            <button class="uk-modal-close-default uk-icon uk-close" type="button" data-uk-close></button>
            <div class="uk-margin">
                <?= Text::_('COM_HYPERPC_DELIVERY_DROP_TEXT') ?>
            </div>
            <?= $this->render('common/geo/city_form') ?>
        </div>
    </div>
<?php elseif ($geoType === 'countries') : ?>
    <div id="<?= $targetId ?>" data-uk-drop="mode: click; pos: bottom-center; flip: x; offset: <?= $offset ?>" class="uk-drop">
        <div class="jsGeoCity tm-background-gray-15 uk-card uk-card-small uk-card-body uk-card-default">
            <?= $this->render('common/geo/country_list') ?>
        </div>
    </div>
<?php endif;
