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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Html\Data\Product\Specification;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var     ProductMarker   $product
 * @var     string          $optionsMode
 * @var     bool            $excludeExternalParts
 */

$optionsMode = $optionsMode ?? 'default';
$excludeExternalParts = $excludeExternalParts ?? false;

$specification = new Specification($product, false, $excludeExternalParts, $optionsMode);
$rootGroups = $specification->getSpecification()['rootGroups'];
?>

<div class="uk-modal-container">
    <table class="uk-table uk-table-divider tm-table-specs tm-table-specs--icons">
        <tbody>
            <?php foreach ($rootGroups as $groupId => $group) :
                $children = $group['groups'];
                ?>

                <tr class="tm-table-specs__group-head">
                    <th colspan="2"><span class="uk-h4"><?= $group['title'] ?></span></th>
                </tr>

                <?php foreach ($children as $childId => $child) : ?>
                    <tr>
                        <th class="tm-table-specs__property-name">
                            <span class="uk-margin-small-right" uk-icon="icon: hp-<?= $child['alias'] ?>"></span>
                            <?= $child['title'] ?>
                        </th>
                        <td>
                            <ul class="uk-list tm-list-small hp-group-<?= $childId ?>">
                                <?php
                                foreach ($child['parts'] as $partId => $part) :
                                    $partName = $part['partName'];

                                    if (isset($part['optionName'])) {
                                        $partName .= ' ' . $part['optionName'];
                                    }

                                    if ($part['quantity'] > 1) {
                                        $partName = sprintf('%s x %s', $part['quantity'], $partName);
                                    }

                                    $advantages = $part['advantages'] ?? [];
                                    ?>
                                    <li class="hp-spec-item">
                                        <?= $partName ?>
                                        <?php if (count($advantages)) : ?>
                                            <ul class="uk-list uk-list-collapse uk-text-muted uk-text-small uk-margin-remove-top">
                                                <?php foreach ($advantages as $advantage) : ?>
                                                    <li>
                                                        <?= $advantage ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
