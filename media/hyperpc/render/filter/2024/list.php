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

use HYPERPC\Filters\Filter;
use HYPERPC\Helper\RenderHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * @var RenderHelper $this
 * @var Filter $filter
 */

$state = $filter->getState();

$filterList = $state['available'];
$current = $state['current'];
?>
<ul class="uk-accordion uk-list-divider uk-margin-remove-bottom" uk-accordion="multiple: true">
    <?php foreach ($filterList as $filterItem) :
        $liAttrs = [
            'class' => 'hp-group-filter',
            'data'  => [
                'filter' => $filterItem['key']
            ]
        ];

        $hasActive = key_exists($filterItem['key'], $current);

        if ($hasActive) {
            $liAttrs['class'] .= ' uk-open';
        }
        ?>
        <li <?= $this->hyper['helper']['html']->buildAttrs($liAttrs) ?>>
            <a class="uk-accordion-title" href="#">
                <?= $filterItem['title'] ?>&nbsp;<span class="jsFilterMark uk-text-primary"><?= $hasActive ? '&bull;' : null ?></span>
            </a>
            <div class="hp-filter-options uk-accordion-content"<?= !$hasActive ? ' hidden' : '' ?>>
                <?php if ($filterItem['type'] === 'checkboxes') : ?>
                    <?php foreach ($filterItem['options'] as $option) :
                        $value = $option['value'];
                        $name = $option['name'];
                        $count = $option['count'];

                        $checked = $hasActive && in_array($value, $current[$filterItem['key']]);
                        ?>
                        <div class="uk-margin-small">
                            <label class="jsFilterButton hp-group-filter__option uk-flex<?= $count === 0 && !$checked ? ' uk-disabled' : '' ?>">
                                <span class="uk-flex-none" style="margin-inline-end: 5px;">
                                    <input type="checkbox" class="uk-checkbox" value="<?= $value ?>"<?= $checked ? ' checked' : '' ?>>
                                </span>
                                <span>
                                    <?= $name ?>
                                    <span class="jsFilterButtonCount uk-text-small uk-text-muted"><?= $count > 0 ? $count : '' ?></span>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($filterItem['type'] === 'range') :
                    $min = $filterItem['options']['min'];
                    $max = $filterItem['options']['max'];

                    $minValue = $filterItem['options']['minValue'];
                    $maxValue = $filterItem['options']['maxValue'];

                    $minSize = strlen((string) $min);
                    $maxSize = strlen((string) $max);

                    $pattern = "^[0-9]{{$minSize},{$maxSize}}";
                    ?>
                    <div class="uk-margin-small">
                        <div class="jsRange uk-grid uk-grid-small uk-flex-middle uk-flex-nowrap">
                            <div>
                                <input
                                    type="text"
                                    size="<?= $maxSize ?>"
                                    maxlength="<?= $maxSize ?>"
                                    pattern="<?= $pattern ?>"
                                    class="uk-input jsRangeFrom"
                                    inputmode="decimal"
                                    placeholder="<?= $min ?>"
                                    value="<?= $minValue ?>">
                            </div>
                            <div>&mdash;</div>
                            <div>
                                <input
                                    type="text"
                                    size="<?= $maxSize ?>"
                                    maxlength="<?= $maxSize ?>"
                                    pattern="<?= $pattern ?>"
                                    class="uk-input jsRangeTo"
                                    inputmode="decimal"
                                    placeholder="<?= $max ?>"
                                    value="<?= $maxValue ?>">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
