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
 * @var         string          $toggleSelector
 */

$geoType = $this->hyper['params']->get('geo_type', 'cities');
?>
<div id="<?= ltrim($toggleSelector, '#') ?>" class="jsOffcanvasSubscreen jsOffcanvasLocation tm-offcanvas-subscreen" hidden>
    <?php if ($geoType === 'cities') : ?>
        <a class="tm-offcanvas-subscreen__back uk-link-muted" href="<?= $toggleSelector ?>" uk-toggle="animation: uk-animation-slide-right">
            <span class="uk-icon uk-text-primary" uk-icon="chevron-left"></span> <?= Text::_('COM_HYPERPC_DELIVERY_DROP_HEADING') ?>
        </a>

        <div class="tm-line-height-140">
            <?= Text::_('COM_HYPERPC_DELIVERY_DROP_EXPLANATION') ?>
        </div>

        <?= $this->render('common/geo/2023/city_form') ?>
    <?php elseif ($geoType === 'countries') : ?>
        <a class="tm-offcanvas-subscreen__back uk-link-muted" href="<?= $toggleSelector ?>" uk-toggle="animation: uk-animation-slide-right">
            <span class="uk-icon uk-text-primary" uk-icon="chevron-left"></span> <?= Text::_('COM_HYPERPC_DELIVERY_DROP_HEADING') ?>
        </a>
 
        <?= $this->render('common/geo/2023/country_list') ?>
    <?php endif; ?>
</div>
