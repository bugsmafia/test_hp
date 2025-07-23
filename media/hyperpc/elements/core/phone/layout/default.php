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

/**
 * @var ElementCorePhone $this
 */

$inputAttr = [
    'type'        => 'tel',
    'class'       => 'uk-input uk-form-large',
    'id'          => 'hp-input-' . $this->getIdentifier(),
    'name'        => $this->getControlName('value'),
    'pattern'     => HP_PHONE_REGEX,
    'value'       => ''
];

$wrapperAttr = [
    'class' => 'uk-margin tm-label-infield',
    'id'    => 'field-' . $this->getIdentifier()
];

if ($this->isRequired()) {
    $inputAttr['required'] = 'required';
}

$value       = $this->getValue();
$description = $this->getConfig('description');

if ($value) {
    $inputAttr['value'] = $value;
}

if (strlen($this->getUserPhone()) > ElementCorePhone::USER_PHONE_MIN_SIZE) {
    $inputAttr = [
        'type'  => 'hidden',
        'value' => $this->getUserPhone(),
        'id'    => 'hp-input-' . $this->getIdentifier(),
        'name'  => $this->getControlName('value')
    ];
    $wrapperAttr['class'] .= ' uk-hidden';
}
?>
<div <?= $this->hyper['helper']['html']->buildAttrs($wrapperAttr) ?>>
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
