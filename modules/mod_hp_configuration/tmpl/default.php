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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

//  No direct access
defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Language\Text;

/**
 * @var string $configuratorRoute
 */

$hp = App::getInstance();

$dropdownAttr = [
    'class'       => 'uk-dropdown hp-module-configuration__drop tm-card-bordered',
    'uk-dropdown' => json_encode([
        'offset'         => 11,
        'duration'       => 250,
        'mode'           => 'click',
        'pos'            => 'bottom-right',
        'boundary'       => '.uk-navbar',
    ])
];
?>

<div class="jsModuleConfiguration jsNavbarSearchToggles hp-module-configuration">

    <a class="jsModuleConfigurationIcon hp-module-configuration__toggle uk-navbar-toggle" title="<?= Text::_('MOD_HP_CONFIGURATION_LOAD_CONFIG') ?>" uk-tooltip="pos: bottom; delay: 500">
        <span uk-icon="icon: cog; ratio: 1.2"></span>
    </a>
    <div <?= $hp['helper']['html']->buildAttrs($dropdownAttr) ?>>
        <div class="tm-text-medium uk-text-emphasis uk-text-nowrap uk-text-center uk-margin-small-bottom">
            <?= Text::_('MOD_HP_CONFIGURATION_LOAD_CONFIG') ?>
            <span uk-icon="icon: question; ratio: 0.8" uk-tooltip="title: <?= Text::_('MOD_HP_CONFIGURATION_LOAD_CONFIG_HINT') ?>"></span>
        </div>

        <form method="post" class="uk-form uk-navbar-item uk-padding-remove-left" action="/index.php?option=com_hyperpc&task=configurator.find_configuration">
           <div>
                <div class="uk-inline">
                    <span class="uk-form-icon uk-icon" uk-icon="icon:hashtag;ratio:1.5"></span>
                    <input name="configuration_id" id="configuration-id" class="hp-module-configuration__input uk-input" type="text" required pattern="^[0-9]{3,7}$" placeholder="_______" maxlength="7">
                    <button class="hp-module-configuration__submit-btn uk-button uk-button-primary" title="<?= Text::_('MOD_HP_CONFIGURATION_LOAD_CONFIG') ?>" type="submit"><?= Text::_('MOD_HP_CONFIGURATION_LOAD_BUTTON') ?></button>
                </div>
           </div>
        </form>
        <button class="uk-close-large uk-position-top-right uk-position-small uk-drop-close" type="button" uk-close></button>
        <hr>
        <div class="uk-text-center">
            <div class="tm-text-medium uk-text-emphasis uk-text-nowrap uk-margin-small-bottom">
                <?= Text::_('MOD_HP_CONFIGURATION_NEW_CONFIG') ?>
            </div>
            <a href="<?= $configuratorRoute ?>" class="uk-button uk-button-primary" title="<?= Text::_('MOD_HP_CONFIGURATION_GO_TO_CONFIGURATOR') ?>"><?= Text::_('MOD_HP_CONFIGURATION_GO_TO_CONFIGURATOR') ?></a>
        </div>
    </div>
</div>
