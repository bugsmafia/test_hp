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
 * @var         int             $offset
 * @var         string          $toggleSelector
 * @var         ?string         $position
 */

$position = $position ?? 'bottom-center';

$geoType = $this->hyper['params']->get('geo_type', 'cities');
?>
<?php if ($geoType === 'cities') : ?>
    <div data-uk-drop="mode: click;<?= !empty($toggleSelector) ? ' toggle: ' . $toggleSelector . ';' : '' ?> pos: <?= $position ?>; offset: <?= $offset ?>" class="uk-drop uk-dropdown uk-dropdown-large uk-width-xlarge">
        <button class="uk-drop-close" type="button" uk-close></button>
        <div class="tm-text-medium tm-font-semibold uk-text-emphasis tm-line-height-120">
            <?= Text::_('COM_HYPERPC_DELIVERY_DROP_HEADING') ?>
        </div>
        <p class="uk-margin-small-top tm-line-height-120">
            <?= Text::_('COM_HYPERPC_DELIVERY_DROP_EXPLANATION') ?>
        </p>
        <?= $this->render('common/geo/2023/city_form') ?>
    </div>
<?php elseif ($geoType === 'countries') : ?>
    <div data-uk-drop="mode: click;<?= !empty($toggleSelector) ? ' toggle: ' . $toggleSelector . ';' : '' ?> pos: <?= $position ?>; offset: <?= $offset ?>" class="uk-drop uk-dropdown uk-width-medium">
        <?= $this->render('common/geo/2023/country_list') ?>
    </div>
<?php endif;
