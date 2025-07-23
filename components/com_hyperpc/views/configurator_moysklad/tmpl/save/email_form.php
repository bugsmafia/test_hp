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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\LayoutHelper;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * @var         Form                $form
 * @var         LayoutHelper        $this
 * @var         SaveConfiguration   $configuration
 */

HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('behavior.formvalidator');

$task     = 'sendByEmail';
$btnTitle = (!isset($btnTitle)) ? Text::_('COM_HYPERPC_CONFIG_EMAIL_SEND') : $btnTitle;

$formAction = $this->hyper['helper']['route']->url([
    'task' => 'configurator.' . $task
]);

$formAttrs = [
    'class'  => 'uk-form-horizontal jsSendEmailForm',
    'action' => $formAction,
    'method' => 'post',
    'data'   => [
        'alias' => $task,
        'task'  => 'configurator.' . $task,
        'name'  => 'configForm' . $task
    ]
];

$currentUser = Factory::getUser();
?>

<form <?= $this->hyper['helper']['html']->buildAttrs($formAttrs) ?>>

    <?= $this->hyper['params']->get('conf_save_email_form_content_before', '') ?>

    <div class="uk-grid uk-grid-small uk-child-width-1-2@s" uk-grid>
        <div>
            <div class="tm-label-infield">
                <?= $form->getLabel('username') ?>
                <?= $form->getInput('username') ?>
            </div>
        </div>
        <div>
            <div class="tm-label-infield">
                <?= $form->getLabel('email') ?>
                <?= $form->getInput('email') ?>
            </div>
        </div>
    </div>

    <?= $this->hyper['params']->get('conf_save_email_form_content_after', '') ?>

    <?php if (!$currentUser->id) : ?>
        <div class="uk-margin">
            <?= $form->getInput('captcha') ?>
        </div>
    <?php endif; ?>

    <div class="uk-grid uk-grid-small uk-child-width-1-2@s uk-flex-middle" uk-grid>
        <div>
            <button type="submit" class="uk-button uk-button-primary uk-button-large uk-width-1-1">
                <?= $btnTitle ?>
            </button>
        </div>

        <div class="uk-text-small">
            <?= Text::_('COM_HYPERPC_CONCENT_TO_PROCESSING_PERSONAL_DATA_TEXT') ?>
        </div>
    </div>

    <?= $form->getInput('context') ?>
    <?= $form->getInput('product_id') ?>
</form>
