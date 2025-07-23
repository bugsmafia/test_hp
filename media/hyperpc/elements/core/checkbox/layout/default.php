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
 * @var         ElementCoreText $this
 */

defined('_JEXEC') or die('Restricted access');

$inputAttr = [
    'type'  => 'checkbox',
    'class' => 'uk-checkbox',
    'name'  => $this->getControlName('value')
];

if ($this->isRequired()) {
    $inputAttr['required'] = 'required';
}

$description = $this->getConfig('description');

if ($this->getValue() === 'on') {
    $inputAttr['checked'] = 'checked';
}

if (!array_key_exists('checked', $inputAttr) && $this->getConfig('selected') === '1') {
    $inputAttr['checked'] = 'checked';
}
?>
<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin">
    <label class="uk-form-label" for="form-horizontal-text">
        <?= $this->getTitle() ?>
    </label>
    <div class="uk-form-controls">
        <label>
            <input <?= $this->hyper['helper']['html']->buildAttrs($inputAttr) ?>>
        </label>

        <?php if ($description !== '') : ?>
            <div class="hp-basket-field-info">
                <?= $description ?>
            </div>
        <?php endif; ?>
    </div>
</div>
