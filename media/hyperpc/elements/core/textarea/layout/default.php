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
 * @var         \ElementCoreTextarea $this
 */

defined('_JEXEC') or die('Restricted access');

$inputAttr = [
    'class'       => 'uk-textarea uk-form-large uk-resize-vertical hp-form-input',
//  'placeholder' => $this->getData('placeholder'),
    'name'        => $this->getControlName('value'),
    'rows'        => '5'
];

if ($this->isRequired()) {
    $inputAttr['required'] = 'required';
}

$description = $this->getConfig('description');
?>
<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin tm-label-infield">
    <label class="uk-form-label<?= $this->isRequired() ? ' tm-label-required' : '' ?>" for="">
        <?= $this->getTitle() ?>
    </label>
    <div class="uk-form-controls">
        <textarea <?= $this->hyper['helper']['html']->buildAttrs($inputAttr) ?>><?= $this->getValue() ?></textarea>

        <?php if ($description !== '') : ?>
            <div class="hp-basket-field-info">
                <?= $description ?>
            </div>
        <?php endif; ?>
    </div>
</div>
