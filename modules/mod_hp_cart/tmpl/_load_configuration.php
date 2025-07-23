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
 * @author      Artem Vyshnevskiy
 *
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var string $configuratorRoute
 */
?>

<div id="load-configuration-modal" class="uk-flex-top uk-modal" data-uk-modal="bg-close: false">
    <div class="uk-modal-dialog uk-margin-auto-vertical uk-margin-remove-bottom uk-margin-auto-bottom@s">
        <button class="uk-modal-close-default uk-close uk-icon" type="button" data-uk-close></button>
        <div class="uk-modal-body">

            <div class="uk-modal-title">
                <?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION') ?>
            </div>

            <?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION_MODAL_TEXT') ?>

            <form class="uk-form" action="/index.php?option=com_hyperpc&amp;task=configurator.find_configuration" method="post">
                <div class="tm-margin-30 tm-margin-30-top">
                    <label for="load-configuration-modal-input"><?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION_INPUT_LABEL') ?> *</label>
                    <input
                        id="load-configuration-modal-input"
                        type="text"
                        name="configuration_id"
                        required
                        maxlength="7"
                        pattern="^[0-9]{3,7}$"
                        inputmode="numeric"
                        class="uk-input uk-width-1-1"
                        placeholder="<?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION_INPUT_PLACEHOLDER') ?>"
                        >
                </div>
                <div class="uk-grid uk-flex-middle" uk-grid>
                    <div class="uk-width-1-1 uk-width-2-5@s">
                        <button class="uk-button uk-button-primary uk-width-1-1" title="<?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION') ?>" type="submit">
                            <?= Text::_('MOD_HP_CART_LOAD_CONFIGURATION_BUTTON') ?>
                        </button>
                    </div>
                    <div class="uk-width-expand uk-text-center uk-text-left@s">
                        <a href="<?= $configuratorRoute ?>" title="<?= Text::_('MOD_HP_CART_GO_TO_CONFIGURATOR') ?>">
                            <?= Text::_('MOD_HP_CART_CREATE_NEW_CONFIGURATION') ?>
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
