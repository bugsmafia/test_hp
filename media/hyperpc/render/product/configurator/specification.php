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

use Joomla\CMS\Language\Text;
use HYPERPC\Html\Data\Product\Specification;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var ProductMarker $product
 */

$specification = new Specification($product, true);
$rootGroups = $specification->getSpecification()['rootGroups'];
?>
<ul class="uk-list hp-configurator-box-groups">
    <?php foreach ($rootGroups as $groupId => $group) :
        $children = $group['groups'];
        ?>
        <li class="hp-root-group hp-group-<?= $group['alias'] ?>">
            <div class="uk-h5 hp-root-group-title"><?= $group['title'] ?></div>
            <ul class="uk-list hp-sub-group hp-group-<?= $groupId ?>">
                <?php foreach ($children as $childId => $child) : ?>
                    <li class="hp-content-group-<?= $childId ?><?= !isset($child['parts']) ? ' uk-hidden' : '' ?>" data-alias="<?= $child['alias'] ?>">
                        <?php if (isset($child['parts'])) : ?>
                        <span class="hp-content-group-title"><?= $child['title'] ?></span>
                        <ul id="hp-box-group-<?= $childId ?>" class="uk-list uk-margin-remove-top hp-sub-group-items">
                            <?php
                            foreach ($child['parts'] as $partId => $part) :
                                $partName = $part['partName'];
                                if (isset($part['optionName'])) {
                                    $partName .= implode(PHP_EOL, [
                                        '<span class="part-option uk-text-nowrap">',
                                            Text::sprintf('COM_HYPERPC_PRODUCT_OPTION', $part['optionName']),
                                        '</span>'
                                    ]);
                                }

                                $advantages = isset($part['advantages']) ? $part['advantages'] : [];
                                ?>
                                <li class="hp-box-part-<?= $partId ?>"<?= count($advantages) ? ' data-advantages=\'' . json_encode($advantages) . '\'' : '' ?>>
                                    <span class="jsBoxPartQuantity">
                                        <?= $part['quantity'] > 1 ? $part['quantity'] . ' x ' : '' ?>
                                    </span>

                                    <?php if (!empty($part['viewUrl'])) : ?>
                                        <a href="<?= $part['viewUrl'] ?>" class="uk-link-muted jsLoadIframe">
                                            <?= $partName ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="uk-text-muted">
                                            <?= $partName ?>
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
    <?php endforeach; ?>
</ul>
