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
 * @var         ElementCoreEmail $this
 */

defined('_JEXEC') or die('Restricted access');

$inputAttr = [
    'type'        => 'email',
    'class'       => ['uk-input', 'uk-form-large'],
    'id'          => 'hp-input-' . $this->getIdentifier(),
 // 'placeholder' => $this->getData('placeholder'),
    'name'        => $this->getControlName('value')
];

if ($this->isRequired()) {
    $inputAttr['required'] = 'required';
}

$value       = $this->getValue();
$description = $this->getConfig('description');

if ($value) {
    $inputAttr['value'] = $value;
}

if (!empty($this->hyper['user']->email) && !empty($value)) {
    $inputAttr['class'][] = 'uk-disabled';
    $inputAttr['class'][] = 'tm-form-success';
}

if ($this->hasError()) {
    $inputAttr['class'][] = 'uk-form-danger';
}
?>
<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin tm-label-infield">
    <label class="uk-form-label<?= $this->isRequired() ? ' tm-label-required' : '' ?>" for="hp-input-<?= $this->getIdentifier() ?>">
        <?= $this->getTitle() ?>
    </label>
    <div class="uk-form-controls">
        <input <?= $this->hyper['helper']['html']->buildAttrs($inputAttr) ?>>
        <?php if ($description !== '') : ?>
            <div class="hp-basket-field-info">
                <?= $description ?>
            </div>
        <?php endif; ?>
    </div>
</div>
