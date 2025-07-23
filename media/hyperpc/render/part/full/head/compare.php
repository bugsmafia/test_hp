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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var array           $compareItems
 * @var RenderHelper    $this
 * @var PartMarker      $part
 * @var ?OptionMarker[] $options
 * @var ?OptionMarker   $optionDefault
 */

list($type, $id) = explode('-', $part->getItemKey());

if (isset($options) && count($options)) :
    $optionCompareBtn = [];

    foreach ($options as $option) {
        if ($option->isDiscontinued()) {
            continue;
        }

        $compareClass = 'jsCompareAdd hp-compare-btn';

        $isSelected = ($optionDefault->id === $option->id);

        $itemCompareKey = $this->hyper['helper']['compare']->getItemKey(['itemId' => $id, 'optionId' => $option->id]);
        $isInCompare    = (array_key_exists($itemCompareKey, $compareItems));
        $linkTitle      = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';
        $linkText       = $isInCompare ? 'COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT' : 'COM_HYPERPC_COMPARE_ADD_BTN_TEXT';

        if (!$isSelected) {
            $compareClass .= ' uk-hidden';
        }

        $itemKey = implode('-', [
            $type,
            $id,
            $option->id
        ]);

        $compareAttrs = [
            'class'   => $compareClass,
            'title'   => Text::_($linkTitle),
            'data'    => [
                'id'        => $id,
                'option-id' => $option->id,
                'type'      => $type,
                'itemKey'   => $itemKey
            ]
        ];

        $compareAttrs['class'] = $isInCompare ? $compareClass . ' inCompare' : $compareClass;

        $optionCompareBtn[] =   '<a ' . $this->hyper['helper']['html']->buildAttrs($compareAttrs) . '>' .
                                  '<span class="uk-icon">' . $this->hyper['helper']['html']->svgIcon('hpCompareAdd') . '</span> ' .
                                  '<span class="hp-compare-btn-text">' . Text::_($linkText) . '</span>' .
                                '</a>';
    }
    ?>
    <span class="hp-compare-btn-wrapper uk-link-muted" data-group="<?= $part->getGroupId() ?>">
        <?= implode(PHP_EOL, $optionCompareBtn) ?>
    </span>
<?php else :
    $compareClass = 'jsCompareAdd hp-compare-btn';

    $itemCompareKey = $this->hyper['helper']['compare']->getItemKey(['itemId' => $id, 'optionId' => $optionDefault->id]);
    $isInCompare    = (array_key_exists($itemCompareKey, $compareItems));
    $linkTitle      = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';
    $linkText       = $isInCompare ? 'COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT' : 'COM_HYPERPC_COMPARE_ADD_BTN_TEXT';

    if ($isInCompare) {
        $compareClass .= ' inCompare';
    }

    $itemKey = implode('-', [
        $type,
        $id
    ]);

    $compareAttrs = [
        'class'   => $compareClass,
        'title'   => Text::_($linkTitle),
        'data'    => [
            'id'        => $id,
            'option-id' => $optionDefault->id,
            'type'      => $type,
            'itemKey'   => $itemKey
        ]
    ];
    ?>
    <span class="hp-compare-btn-wrapper uk-link-muted" data-group="<?= $part->getGroupId() ?>">
        <a <?= $this->hyper['helper']['html']->buildAttrs($compareAttrs) ?>>
            <span class="uk-icon">
                <?= $this->hyper['helper']['html']->svgIcon('hpCompareAdd') ?>
            </span>
            <span class="hp-compare-btn-text">
                <?= Text::_($linkText) ?>
            </span>
        </a>
    </span>
<?php endif;
