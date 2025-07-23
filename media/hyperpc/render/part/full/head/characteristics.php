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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Object\Variant\Characteristics\CharacteristicDataCollection;

/**
 * @var RenderHelper                    $this
 * @var CharacteristicDataCollection    $characteristics
 * @var OptionMarker[]                  $options
 */

if (empty($characteristics) || !($characteristics instanceof CharacteristicDataCollection)) {
    return;
}
?>

<div class="uk-margin">
    <?php foreach ($characteristics as $characteristic) : ?>
        <div class="uk-margin-small">
            <div class="tm-text-size-14 uk-text-emphasis">
                <?= \htmlspecialchars($characteristic->name) ?>:
            </div>
            <div class="tm-item-characteristics tm-margin-8-top">
                <?php foreach ($characteristic->options as $opt):
                    $isActive = $opt->is_active;
                    $isDisabled = empty($opt->variant_id);
                    $value = \htmlspecialchars($opt->value, \ENT_QUOTES);
                    ?>
                    <?php if ($isActive) : ?>
                        <div class="tm-item-characteristics__option tm-item-characteristics__option--active">
                            <span>
                                <?= $value ?>
                            </span>
                        </div>
                    <?php elseif ($isDisabled) : ?>
                        <div class="tm-item-characteristics__option tm-item-characteristics__option--disabled">
                            <span>
                                <?= $value ?>
                            </span>
                        </div>
                    <?php else : ?>
                        <div class="tm-item-characteristics__option">
                            <a href="<?= $options[$opt->variant_id]->getViewUrl() ?>">
                                <?= $value ?>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
