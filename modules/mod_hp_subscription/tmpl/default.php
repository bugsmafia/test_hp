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
 *
 * @var         string  $btnTitle
 * @var         string  $modalTitle
 * @var         string  $modalBtnTitle
 * @var         string  $formId
 * @var         bool    $saveUrl
 * @var         \HYPERPC\Joomla\Form\Form $form
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

$modalId = uniqid('hp-modal-')
?>
<div class="hp-module-subscription jsSubscriptionModule">
    <form class="jsSubscriptionStepFirst" data-modal-href="#<?= $modalId ?>">
        <div class="uk-flex uk-flex-middle">
            <div class="uk-inline">
                <span class="uk-form-icon uk-icon" uk-icon="icon: mail"></span>
                <input type="email" class="validate-email uk-input jsSubscriptionEmail"
                       placeholder="<?= Text::_('MOD_HP_SUBSCRIPTION_EMAIL_HINT') ?>" required>
            </div>
            <button type="submit" name="subscribe" class="uk-button uk-button-primary">
                <?= $btnTitle ?>
            </button>
        </div>
    </form>

    <!-- This is the modal -->
    <div id="<?= $modalId ?>" class="uk-modal" uk-modal>
        <div id="hp-subscription-form" class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <div class="uk-h2"><?= $modalTitle ?></div>
            <form id="<?= $formId ?>" class="uk-form-stacked form-validate jsSubscriptionStepSecond" action="<?= $formAction ?>" method="post">
                <div class="uk-margin">
                    <div class="uk-form-label">
                        <?= $form->getLabel('username') ?>
                    </div>
                    <div class="uk-inline">
                        <span class="uk-form-icon uk-icon" uk-icon="icon: user"></span>
                        <?= $form->getInput('username') ?>
                    </div>
                </div>
                <?php if ($params->get('enable_phone', 0)) : ?>
                    <div class="uk-margin">
                        <div class="uk-form-label">
                            <?= $form->getLabel('phone') ?>
                        </div>
                        <div class="uk-inline">
                            <span class="uk-form-icon uk-icon" uk-icon="icon: receiver"></span>
                            <?= $form->getInput('phone') ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="uk-margin">
                    <div>
                        <?= $form->getInput('consent') ?>
                        <?= Text::_($form->getFieldAttribute('consent', 'description')) ?>
                    </div>
                </div>
                <?= $form->getInput('email') ?>
                <?= $form->getInput('type') ?>
                <?= $form->getInput('module_id') ?>
                <?= HTMLHelper::_('form.token') ?>
                <input type="hidden" value="subscription.save" name="task" />
                <input type="hidden" value="<?= $module->get('id') ?>" name="module_id" />
                <?php
                if ($saveUrl) {
                    echo $form->renderField('page_url', 'params');
                }
                ?>
                <div class="uk-margin">
                    <button data-form="<?= $formId ?>" type="submit" class="uk-button uk-button-small uk-button-normal@s uk-button-primary uk-width-1-1">
                        <?= $modalBtnTitle ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
