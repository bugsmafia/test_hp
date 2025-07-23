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

/**
 * @var ElementAuthEmail   $this
 */

// При обработке формы проверяем залогинен ли пользователь. Если нет, то сразу шлем лесом.
// Предварительно при отправке формы редактирования почты сохраняем в сессию была ли пройдена капча при отправке формы редактирования с этими данными.
// Если да, выводим форму без капчи. Если нет, то с капчей. Иначе можно будет бесконтрольно отправлять письма используя куки одной сессии

$editValue = $this->getEditRequestValue();
$captchaCount = $this->hyper['helper']['session']->get()->captcha;
?>

<form class="jsMergeConfirmForm">
    <input class="jsMergeConfirmValue" type="hidden" name="<?= $this->getType() ?>" value="<?= $editValue ?>">
    <div class="uk-alert uk-alert-warning">
        <?= Text::sprintf('HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_FORM_MERGE_CONFIRM_TEXT', $editValue) ?>
    </div>

    <div class="uk-margin jsAuthCaptcha">
        <?= $captchaCount === 0 ?? $this->getAuthForm()->getInput('captcha') ?>
    </div>
    <hr>
    <div class="uk-text-center">
        <button type="submit" class="uk-width-1-1 uk-button uk-button-primary uk-button-large@s uk-margin">
            <?= Text::_('HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_FORM_MERGE_CONFIRM_SUBMIT') ?>
        </button>
        <button type="button" class="jsMergeConfirmBack uk-button uk-button-text" data-type="<?= $this->getType() ?>">
            <?= Text::_('HYPER_ELEMENT_AUTH_EMAIL_EDIT_ACCOUNT_FORM_MERGE_CONFIRM_BACK') ?>
        </button>
    </div>
</form>
