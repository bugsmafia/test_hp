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

use HYPERPC\Elements\ElementPayment;

/**
 * @var \ElementOrderPayments $this
 * @var ElementPayment $element
 */

$default = $this->getConfig('default', 'spot');

$userIsManager = (bool) $this->hyper['input']->cookie->get(HP_COOKIE_HMP);
?>
<div id="field-<?= $this->getIdentifier() ?>">
    <div class="uk-margin">
        <div class="uk-h4 uk-margin-small">
            <?= $this->getConfig('name') ?>
        </div>
        <div class="uk-form-controls">
            <?php foreach ($this->getMethods() as $element) :
                if (!$userIsManager && $element->isForManager()) {
                    continue;
                }

                $attrs = [
                    'type'  => 'radio',
                    'class' => 'uk-radio uk-flex-none',
                    'value' => $element->getType(),
                    'name'  => $this->getControlName('value')
                ];

                if ($element->getType() === $default) {
                    $attrs['checked'] = 'checked';
                }

                $description = $element->getConfig('description');

                $wrapperAttr = [
                    'id'    => 'hp-payment-' . $element->getType(),
                    'class' => [
                        'hp-cart-payment-element',
                        'jsPaymentElement',
                        'uk-margin-small'
                    ]
                ];
                ?>
                <div <?= $this->hyper['helper']['html']->buildAttrs($wrapperAttr) ?>>
                    <label class="uk-card uk-card-small uk-card-body uk-border-rounded tm-background-gray-15 uk-flex uk-text-top">
                        <input <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                        <span>
                            <span class="uk-display-block uk-text-emphasis">
                                <?= $element->getTitle() ?>
                            </span>
                            <?php if ($description !== '') : ?>
                                <span class="uk-display-block uk-text-small uk-text-muted">
                                    <?= $description ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
