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
use HYPERPC\Helper\AuthHelper;
use HYPERPC\Helper\RenderHelper;

/**
 * @var RenderHelper    $this
 */

$codeLength = AuthHelper::PASS_CODE_LENGTH;
?>

<div class="uk-modal-title"><?= Text::_('COM_HYPERPC_AUTH_MODAL_SECOND_STEP_HEADING') ?></div>

<div>
    <span class="jsAuthBeforeFormText"></span>
    <button type="button" class="jsAuthGoBack uk-button uk-button-link">
        <?= Text::_('COM_HYPERPC_AUTH_MODAL_CHANGE') ?>
    </button>
</div>
<div class="tm-margin-24 tm-margin-30-bottom">
    <div class="uk-grid uk-grid-small tm-grid-otp uk-flex-center">
        <?php for ($i = 0; $i < $codeLength; $i++) : ?>
            <div>
                <input class="uk-input uk-form-width-xsmall uk-form-large uk-text-center"
                       required="required" data-code="<?= $i ?>" name="pwd[<?= $i ?>]"
                       inputmode="numeric" maxlength="1" type="text" autocomplete="off" pattern="\d*">
            </div>
        <?php endfor; ?>
    </div>
    <div class="jsAuthFormError uk-form-danger uk-text-center" style="display: none"></div>
    <div>
        <div class="jsAuthResendDelayMessage tm-input-sub uk-text-center">
            <?= Text::sprintf('COM_HYPERPC_AUTH_WAIT_MESSAGE', '<span class="jsAuthResendDelayTime"></span>') ?>
        </div>
        <div class="tm-margin-8-top uk-text-center" hidden>
            <button type="button" class="jsAuthResendButton uk-button uk-button-link">
                <?= Text::_('COM_HYPERPC_AUTH_RESEND_CODE') ?>
            </button>
        </div>
    </div>
</div>
<div class="uk-text-center">
    <button class="uk-button uk-button-primary uk-width-1-1 uk-width-medium@s" type="submit">
        <?= Text::_('COM_HYPERPC_CONFIRM') ?>
    </button>
</div>
