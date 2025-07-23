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

$position ??= 'bottom-center';

$geoType = $this->hyper['params']->get('geo_type', 'cities');

$dropClass = 'uk-drop uk-dropdown';
if ($geoType === 'cities') {
    $dropClass .= ' uk-dropdown-large uk-width-xlarge';
}
?>
<div data-uk-drop="mode: click;<?= !empty($toggleSelector) ? ' toggle: ' . $toggleSelector . ';' : '' ?> pos: <?= $position ?>; offset: <?= $offset ?>" class="<?= $dropClass ?>">
    <button class="uk-drop-close" type="button" data-uk-close></button>

    <?php if ($geoType === 'cities') : ?>
        <div class="uk-h4 uk-margin-remove-top">
            <?= Text::_('COM_HYPERPC_DELIVERY_DROP_HEADING') ?>
        </div>

        <div class="uk-margin-bottom uk-inline uk-width-1-1">
            <span class="uk-form-icon uk-icon" data-uk-icon="icon: search; ratio: .8"></span>
            <input type="text" class="jsGeoCityInput uk-input" placeholder="<?= Text::_('COM_HYPERPC_SEARCH') ?>..." />
        </div>

        <?= $this->render('common/geo/2024/location_list', ['ulCssClass' => 'uk-nav uk-navbar-dropdown-nav uk-column-1-2 uk-column-1-3@s']) ?>
    <?php elseif ($geoType === 'countries') : ?>
        <?= $this->render('common/geo/2024/location_list', ['ulCssClass' => 'uk-nav uk-navbar-dropdown-nav']) ?>
    <?php endif; ?>
</div>
