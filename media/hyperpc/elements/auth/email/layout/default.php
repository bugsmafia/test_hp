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

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;

/**
 * @var     \ElementAuthEmail   $this
 * @var     JSON                $params
 */

$form = $this->getAuthForm();
$form->setFieldAttribute('type', 'id', uniqid('type_email'));
$form->setFieldAttribute('email', 'id', uniqid('email_'));

$allowCreateNewUser = $this->getConfig('create_new_profile', false, 'bool');
$emailField = $form->getField('email');
?>
<?php if ($allowCreateNewUser) : ?>
    <div class="uk-modal-title">
        <?= Text::_('COM_HYPERPC_AUTH_MODAL_HEADING') ?>
    </div>
<?php else : ?>
    <div class="uk-modal-title">
        <?= Text::_('COM_HYPERPC_AUTH_MODAL_HEADING_EMAIL') ?>
    </div>
    <?= Text::_('COM_HYPERPC_AUTH_MODAL_ONLY_FOR_REGISTRED') ?>
<?php endif; ?>

<form class="jsAuthFirstStepEmail" novalidate="novalidate">

    <?= $form->getInput('type') ?>

    <div class="tm-margin-30 tm-margin-30-top">
        <label for="<?= $emailField->id ?>"><?= Text::_('COM_HYPERPC_AUTH_EMAIL_LABEL') ?> *</label>
        <?= $form->getInput('email') ?>
        <div id="<?= $emailField->id ?>-error" class="jsAuthFormError uk-form-danger" style="display: none"></div>
        <div class="tm-input-sub"><?= Text::_('COM_HYPERPC_AUTH_CONCENT_TO_PROCESSING_PERSONAL_DATA') ?></div>

        <div class="uk-margin jsAuthCaptcha">
            <?= $form->getInput('captcha') ?>
        </div>
    </div>

    <div class="uk-grid uk-flex-middle" data-uk-grid>
        <div class="uk-width-1-1 uk-width-2-5@s uk-first-column">
            <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                <?= Text::_('COM_HYPERPC_AUTH_SIGN_IN') ?>
                <span class="jsAuthResendDelayMessage" hidden>(<span class="jsAuthResendDelayTime"></span>)</span>
            </button>
        </div>
        <?php if ($params->get('countElements', 1) > 1) : ?>
            <div class="uk-width-expand uk-text-center uk-text-left@s">
                <a href="#" data-uk-switcher-item="previous">
                    <?= Text::_('COM_HYPERPC_AUTH_MODAL_PHONE_ENTER') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

</form>
