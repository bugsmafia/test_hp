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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * @var ElementAuthMobile   $this
 */
?>

<div id="hp-modal-edit-mobile" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="uk-modal-title">
            <?= Text::_('HYPER_ELEMENT_AUTH_MOBILE_EDIT_ACCOUNT_FORM_TITLE') ?>
        </h2>
        <form class="jsEditFirstStep" data-element-type="<?= $this->getType() ?>">
            <div class="uk-margin">
                <div class="uk-margin-small uk-text-muted">
                    <?= Text::_('HYPER_ELEMENT_AUTH_MOBILE_EDIT_ACCOUNT_FORM_ENTER_NEW_EMAIL') ?>
                </div>
                <div class="uk-inline uk-width-1-1">
                    <span class="uk-form-icon uk-icon" uk-icon="icon: phone"></span>
                    <input type="tel" name="mobile" class="uk-input hpJsEditValue" required/>
                </div>
            </div>

            <div class="uk-margin jsAuthCaptcha">
                <?= $this->getAuthForm()->getInput('captcha') ?>
            </div>

            <div class="uk-margin-small uk-text-small uk-text-muted uk-text-center">
                <?= Text::_('HYPER_ELEMENT_AUTH_MOBILE_EDIT_ACCOUNT_FORM_CONFIRM_CODE_INFO') ?>
            </div>

            <button type="submit" class="uk-button uk-button-primary uk-button-large@s uk-width-1-1">
                <?= Text::_('COM_HYPERPC_AUTH_SIGN_IN') ?>
            </button>
        </form>
    </div>
</div>
