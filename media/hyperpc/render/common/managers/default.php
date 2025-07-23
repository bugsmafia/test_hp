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
 *
 * @var         array $managersIds
 * @var         integer $managersCount
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');
?>

<div class="uk-section uk-section-small uk-background-muted">
    <div class="uk-container uk-container-small uk-text-center uk-margin-bottom">
        <h2 class="uk-margin-small-bottom">
            <?= Text::_('COM_HYPERPC_MANAGERS_HEADING') ?>
        </h2>
        <p class="uk-margin-small-top">
            <?= Text::_('COM_HYPERPC_MANAGERS_LEAD_TEXT') ?>
        </p>
    </div>
    <div>
        <div class="uk-container uk-container-large uk-slider uk-slider-container" uk-slider="finite: true; draggable: true; velocity: 10;">
            <div class="uk-position-relative">
                <div class="hp-employee-cards-wrapper uk-slider-items uk-grid uk-grid-small uk-child-width-1-2@m uk-child-width-1-3@xl uk-grid-match uk-flex-center@m uk-margin-bottom" uk-grid>
                    <?= HTMLHelper::_('content.prepare', '{loadworkers id=' . implode(',', $managersIds) . ' limit=' . $managersCount . '}') ?>
                </div>
                <a class="uk-position-center-left uk-position-small uk-slidenav-large uk-visible@s uk-hidden@m uk-slidenav-previous uk-icon uk-slidenav" href="#" uk-slidenav-previous uk-slider-item="previous"></a>
                <a class="uk-position-center-right uk-position-small uk-slidenav-large uk-visible@s uk-hidden@m uk-slidenav-next uk-icon uk-slidenav" href="#" uk-slidenav-next uk-slider-item="next"></a>
            </div>
            <ul class="uk-slider-nav uk-flex-center uk-margin uk-dotnav uk-visible@s uk-hidden@m"></ul>
        </div>
    </div>
</div>
