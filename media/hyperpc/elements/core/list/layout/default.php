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
 *
 * @var         array $options
 * @var         bool $multiple
 * @var         string $default
 * @var        \ElementCoreList $this
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

$attrs = [
    'class' => 'uk-select'
];

if ($multiple) {
    $attrs['multiple'] = 'multiple';
}

$description = $this->getConfig('description');
?>
<div id="field-<?= $this->getIdentifier() ?>" class="uk-margin">
    <label class="uk-form-label<?= $this->isRequired() ? ' tm-label-required' : '' ?>" for="hp-input-<?= $this->getIdentifier() ?>">
        <?= $this->getTitle() ?>
    </label>
    <div class="uk-form-controls">
        <?php
        echo HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->getControlName('value'),
            $this->hyper['helper']['html']->buildAttrs($attrs),
            'value',
            'text',
            $default,
            null
        );
        ?>

        <?php if ($description !== '') : ?>
            <div class="hp-basket-field-info">
                <?= $description ?>
            </div>
        <?php endif; ?>
    </div>
</div>
