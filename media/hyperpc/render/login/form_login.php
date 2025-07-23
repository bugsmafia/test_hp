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
 *
 * @var         RenderHelper $this
 */

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
?>

<form class="jsAuthAjaxForm" action="#" method="post">
    <div>
        <div>
            <div class="uk-inline uk-width-1-1">
                <span class="uk-form-icon uk-icon" uk-icon="icon: user"></span>
                <input type="text" name="username" class="uk-input uk-form-large" required placeholder="<?= Text::_('COM_HYPERPC_FORM_PLACEHOLDER_LOGIN') ?>">
            </div>
        </div>
        <div class="uk-margin-top">
            <div class="uk-inline uk-width-1-1">
                <span class="uk-form-icon uk-icon" uk-icon="icon: lock"></span>
                <input type="password" name="password" class="uk-input uk-form-large" required placeholder="<?= Text::_('COM_HYPERPC_FORM_PLACEHOLDER_PASSWORD') ?>">
            </div>
        </div>
        <div class="uk-margin-top">
            <input id="remember-me" type="checkbox" name="remember" class="uk-checkbox">
            <label for="remember-me" class="control-label">
                <?= Text::_('COM_HYPERPC_USERS_LOGIN_REMEMBER_ME'); ?>
            </label>
        </div>

        <div class="uk-margin-top uk-form-controls">
            <button type="submit" name="submit" class="uk-button uk-button-primary uk-button-large uk-width-1-1">
                <?= Text::_('JLOGIN') ?>
            </button>
        </div>

        <ul class="uk-nav uk-nav-default uk-margin-top uk-margin-remove-bottom">
            <li>
                <a href="<?= Route::_('index.php?option=com_users&view=remind') ?>" class="uk-link-muted" target="_blank">
                    <?= Text::_('COM_HYPERPC_USERS_LOGIN_RESET') ?>
                </a>
            </li>
            <li>
                <a href="<?= Route::_('index.php?option=com_users&view=reset') ?>" class="uk-link-muted" target="_blank">
                    <?= Text::_('COM_HYPERPC_USERS_LOGIN_REMIND') ?>
                </a>
            </li>
        </ul>
    </div>
</form>
