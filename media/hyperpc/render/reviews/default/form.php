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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\ReviewHelper;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * @var         RenderHelper    $this
 * @var         ReviewHelper    $helper
 * @var         Entity          $item
 * @var         Entity          $order
 */

$helper = $this->hyper['helper']['review'];
$form   = $helper->getForm();

if (isset($order)) {
    $form->setFieldAttribute('order_id', 'default', $order->id);
    $form->setFieldAttribute('order_id', 'type', 'hidden');

    $form->setFieldAttribute('item_id', 'default', $item->id);
    $form->setFieldAttribute('item_id', 'type', 'hidden');
}

$formAttrs = [
    'class'  => 'uk-form-horizontal uk-margin-large jsReviewForm',
    'action' => $this->hyper['route']->build([
        'task' => 'review.ajax-save'
    ]),
    'data-task' => 'review.ajax-save'
];
?>

<form <?= $this->hyper['helper']['html']->buildAttrs($formAttrs) ?>>
    <div><?= Text::_('COM_HYPERPC_COMPUTER_REVIEW') ?>:</div>
    <div class="uk-h3 uk-margin-small"><?= $item->name ?></div>
    <div class="uk-margin">
        <?= $form->getLabel('rating') ?>
        <div class="uk-form-controls">
            <?= $form->getInput('rating') ?>
        </div>
    </div>
    <div class="uk-margin">
        <?= $form->getLabel('virtues') ?>
        <div class="uk-form-controls">
            <?= $form->getInput('virtues') ?>
        </div>
    </div>
    <div class="uk-margin">
        <?= $form->getLabel('limitations') ?>
        <div class="uk-form-controls">
            <?= $form->getInput('limitations') ?>
        </div>
    </div>
    <div class="uk-margin">
        <?= $form->getLabel('comment') ?>
        <div class="uk-form-controls">
            <?= $form->getInput('comment') ?>
        </div>
    </div>

    <?php if (isset($order)) : ?>
        <div class="uk-margin">
            <?= $form->getLabel('item_id') ?>
            <div class="uk-form-controls">
                <?= $form->getInput('item_id') ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="uk-margin">
        <?= $form->getLabel('order_id') ?>
        <div class="uk-form-controls">
            <?= $form->getInput('order_id') ?>
        </div>
    </div>
    <div class="uk-margin">
        <div class="uk-form-controls">
            <button type="submit" class="uk-button-primary uk-button">
                <?= Text::_('COM_HYPERPC_REVIEW_BTN_SUBJECT') ?>
            </button>
        </div>
    </div>
    <?= $form->getInput('context', null, $item->getReviewsContext()) ?>
    <div class="jsFormToken"><?= HTMLHelper::_('form.token') ?></div>
</form>
