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

$inputAttr1 = [
    'type'  => 'text',
    'class' => 'uk-input',
    'name'  => $this->getControlName('value')
];
?>
<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin">
    <a class="uk-margin-small" uk-toggle="target: #hp-toggle-manager-input">
        <?= $this->getTitle() ?>
    </a>
    <div id="hp-toggle-manager-input" hidden>
        <div class="uk-form-controls">
            <div class="uk-margin">
                <label>
                    <input <?= $this->hyper['helper']['html']->buildAttrs($inputAttr1) ?>>
                </label>
            </div>
        </div>
    </div>
</div>
