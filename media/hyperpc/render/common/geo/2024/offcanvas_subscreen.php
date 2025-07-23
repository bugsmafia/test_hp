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
$subscreenPadding = $geoType === 'cities' ? ' tm-padding-32-left tm-padding-32-right' : '';
?>
<div id="<?= ltrim($toggleSelector, '#') ?>" class="jsOffcanvasSubscreen jsOffcanvasLocation tm-offcanvas-subscreen<?= $subscreenPadding ?>" hidden>
    <a class="tm-offcanvas-subscreen__back" href="<?= $toggleSelector ?>" data-uk-toggle="animation: uk-animation-slide-right-small">
        <span class="uk-icon" data-uk-icon="chevron-left"></span>
    </a>

    <div class="tm-color-gray-100 tm-text-medium uk-text-default@s">
        <span class="uk-icon tm-margin-8-right">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M0.788698 6.32142C-0.441353 6.88638 -0.184087 8.61356 1.22283 8.62163L7.20426 8.64585C7.32485 8.64585 7.36505 8.69427 7.36505 8.81534L7.38113 14.7798C7.38113 16.168 9.10963 16.4827 9.72064 15.1429L15.8066 1.92276C16.4578 0.510347 15.3644 -0.425882 14.0218 0.19558L0.788698 6.32142ZM2.70211 7.16887C2.63779 7.16887 2.61368 7.09623 2.68603 7.05587L14.0218 1.85012C14.1183 1.80977 14.1745 1.86626 14.1263 1.96311L8.93276 13.3108C8.9006 13.3916 8.82021 13.3754 8.82021 13.3028L8.85236 7.87104C8.85236 7.37871 8.61922 7.13658 8.11273 7.13658L2.70211 7.16887Z" fill="#9C9C9C"/>
            </svg>
        </span>
        <span class="jsCityLabel"></span>
    </div>

    <div class="uk-h2 uk-margin-top tm-margin-16-bottom">
        <?= Text::_('COM_HYPERPC_DELIVERY_DROP_HEADING') ?>
    </div>

    <?php if ($geoType === 'cities') : ?>
        <div class="tm-margin-40-bottom">
            <input type="text" class="jsGeoCityInput uk-input" placeholder="<?= Text::_('COM_HYPERPC_SEARCH') ?>..." />
        </div>

        <?= $this->render('common/geo/2024/location_list', ['ulCssClass' => 'uk-nav uk-navbar-dropdown-nav uk-column-1-2']) ?>
    <?php else : ?>
        <?= $this->render('common/geo/2024/location_list', ['ulCssClass' => 'uk-nav uk-navbar-dropdown-nav tm-margin-32-top']) ?>
    <?php endif; ?>
</div>
