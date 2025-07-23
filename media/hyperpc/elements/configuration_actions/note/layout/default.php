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
 * @var         ElementConfigurationActionsNote $this
 */

use JBZoo\Utils\Filter;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$note           = $this->getNote();
$configuration  = $this->getConfiguration();

$textareaAttrs = [
    'class'  => 'uk-textarea hp-element-note__textarea',
    'data'   => [
        'item_id'        => $configuration->id,
        'id'             => Filter::int($note->id),
        'context'        => $this->getNoteContext(),
        'crated_user_id' => ($note->created_user_id) ? $note->created_user_id : $this->hyper['user']->id
    ]
];

$formAttrs = [
    'class' => 'uk-text-small',
    'hidden' => 'hidden'
];
?>
<div class="jsElementNote hp-element-note">
    <a href="#" class="jsElementNoteToggler hp-element-note__leave-btn uk-link-muted uk-text-small"<?= !empty(trim($note->note)) ? ' hidden' : '' ?>>
        <?= $this->getAccountActionTile() ?>
    </a>
    <div class="hp-element-note__text-wrapper uk-text-muted uk-text-small jsElementNoteTextWrapper"<?= empty(trim($note->note)) ? ' hidden' : '' ?>>
        <span class="jsElementNoteText"><?= $note->note ?></span>
        <span class="uk-text-nowrap">
            <button class="uk-icon tm-button-icon jsEditNote" uk-icon="pencil" title="<?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_NOTE_EDIT') ?>" type="button"></button>
            <button class="uk-icon tm-button-icon jsRemoveNote" uk-icon="trash" title="<?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_NOTE_REMOVE') ?>" type="button"></button>
        </span>
    </div>
    <form <?= $this->hyper['helper']['html']->buildAttrs($formAttrs) ?>>
        <a href="#" class="jsElementNoteCancelForm uk-display-inline-block uk-link-muted uk-text-small uk-margin-small">
            <span uk-icon="close" style="margin-inline-end: 2px"></span><span class="uk-text-middle"><?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_NOTE_CANCEL_EDITING') ?></span>
        </a>
        <textarea <?= $this->hyper['helper']['html']->buildAttrs($textareaAttrs) ?>><?= $note->note ?></textarea>
        <div class="uk-margin-small uk-text-right">
            <button class="uk-button uk-button-primary uk-button-small jsSaveNote" disabled>
                <?= Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_NOTE_DONE') ?>
            </button>
        </div>
    </form>
</div>
