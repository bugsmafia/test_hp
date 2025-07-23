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
use Joomla\Utilities\ArrayHelper;

/**
 * @var \ElementOrderMethods $this
 */

$default = $this->getConfig('default', 0, 'int');
$showInn = $default !== 0; // Not for individual
?>
<div id="field-<?= $this->getIdentifier() ?>">
    <div class="uk-flex uk-flex-right uk-margin">
        <label class="tm-toggle">
            <input type="checkbox" class="tm-toggle__checkbox jsMethodsSwitch">
            <span class="tm-toggle__label">
                <?= Text::_('HYPER_ELEMENT_ORDER_METHOD_LEGAL') ?>
            </span>
            <span class="tm-toggle__switch">
                <span class="tm-toggle__knob"></span>
            </span>
        </label>
    </div>

    <div hidden>
        <?php foreach ($this->getMethods() as $value => $name) :
            $attrs = [
                'value' => $value,
                'type'  => 'radio',
                'name'  => $this->getControlName('value')
            ];

            if ($value === $default) {
                $attrs['checked'] = 'checked';
            }
            ?>
            <label>
                <input <?= ArrayHelper::toString($attrs) ?>>
                <?= Text::_('HYPER_ELEMENT_ORDER_METHOD_' . \strtoupper($name)) ?>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="uk-margin jsOrderCompanyName"<?= !$showInn ? ' hidden' : '' ?>>
        <div class="uk-margin tm-label-infield isEmpty">
            <label class="uk-form-label tm-label-required" for="hp-input-company">
                <?= Text::_('HYPER_ELEMENT_ORDER_METHOD_INN_LABEL') ?>
            </label>
            <div class="uk-form-controls">
                <input type="text" id="hp-input-company" class="uk-input uk-form-large"
                       name="jform[elements][company][name]">
            </div>
        </div>
    </div>
    <input type="hidden" name="jform[elements][company][value]">
</div>
