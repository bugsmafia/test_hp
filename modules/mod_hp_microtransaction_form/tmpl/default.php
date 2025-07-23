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
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var Registry $params
 * @var stdClass $module
 */

$hp   = App::getInstance();
$user = $hp['user'];

$total = $params->get('total', 0);
$defaultState = $params->get('default_state');

$fields = (array) $params->get('fields', []);
?>

<div class="jsMicrotransactionForm uk-width-1-1">
    <div class="uk-margin-auto uk-padding tm-background-gray-10 tm-card-bordered" style="max-width: 500px;">
        <div class="uk-h3 uk-text-center">
            <?= Text::_('MOD_HP_MICROTRANSACTION_FORM_HEADING') ?>
        </div>
        <form>
            <fieldset class="uk-fieldset">

                <div class="uk-margin-small">
                    <label class="uk-form-label tm-label-required" for="hp-input-player">
                        <?= trim($params->get('player_field_label')) ?: Text::_('MOD_HP_MICROTRANSACTION_FORM_PLAYER_FIELD_LABEL_DEFAULT') ?>
                    </label>
                    <div class="uk-form-control">
                        <input id="hp-input-player" index="1" name="player" class="uk-input uk-form-large" type="text" required
                               placeholder="<?= $params->get('player_field_placeholder') ?: Text::_('MOD_HP_MICROTRANSACTION_FORM_PLAYER_FIELD_PLACEHOLDER_DEFAULT') ?>">
                    </div>
                </div>

                <?php foreach ($fields as $field) : ?>
                    <div class="uk-margin-small">
                        <label class="uk-form-label tm-label-required" for="<?= 'field-' . $field->field_key ?>">
                            <?= $field->field_label ?>
                        </label>
                        <select name="<?= $field->field_key ?>" class="jsMicrotransactionFormSelect uk-select uk-form-large uk-text-muted" required id="<?= 'field-' . $field->field_key ?>">
                            <?php
                            $hasGroups = false;
                            $firstIsSeparator = current($field->values) ? (trim(current($field->values)->value) === '') : false;
                            if ($firstIsSeparator) {
                                $hasGroups = true;
                            } else {
                                foreach ($field->values as $value) {
                                    if (trim($value->value) === '') {
                                        $hasGroups = true;
                                        break;
                                    }
                                }
                            }

                            if ($hasGroups && !$firstIsSeparator) {
                                echo '<optgroup>';
                            }

                            foreach ($field->values as $value) :
                                $val = trim($value->value);
                                $text = $value->text;

                                $key = "{$field->field_key}:$val";
                                $selected = strpos($defaultState, $key) !== false ? ' selected' : '';

                                if ($val === '') {
                                    echo '</optgroup>';
                                    echo "<optgroup label=\"- {$text} -\" class=\"uk-text-emphasis\">";
                                    continue;
                                }
                                ?>
                                <option value="<?= $val ?>"<?= $selected ?>><?= $text ?></option>
                            <?php endforeach; ?>
                            <?php if ($hasGroups) : ?>
                                <?= '</optgroup>' ?>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endforeach; ?>

                <div class="uk-margin-small-top uk-margin-auto uk-width-2-3@s">
                    <div class="uk-text-center tm-text-medium">
                        <?= Text::_('MOD_HP_MICROTRANSACTION_FORM_TOTAL') ?>:
                        <span class="uk-text-bold uk-text-emphasis">
                            <?= Text::sprintf('MOD_HP_MICROTRANSACTION_FORM_PRICE', "<span class=\"jsMicrotransactionFormTotal\">{$total}</span>") ?>
                        </span>
                    </div>
                    <div class="uk-margin-top">
                        <button class="uk-button uk-button-primary uk-button-large uk-width-1-1" type="submit"<?= $user->id ? '' : ' disabled' ?>>
                            <?= Text::_('MOD_HP_MICROTRANSACTION_FORM_SUBMIT') ?>
                        </button>
                    </div>
                </div>

                <?php if (!$user->id) : ?>
                    <div class="jsMicrotransactionFormAuthAlert uk-text-small uk-text-center@s uk-margin-small-top">
                        <?= Text::sprintf('MOD_HP_MICROTRANSACTION_FORM_AUTH_ALERT', '#login-form-modal') ?>
                    </div>
                <?php endif; ?>
            </fieldset>
            <input type="hidden" value="<?= $module->id ?>" name="module-id" />
            <div class="jsMicrotransactionFormToken"><?= HtmlHelper::_('form.token'); ?></div>
        </form>
    </div>
</div>
