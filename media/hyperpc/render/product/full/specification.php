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

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Specification;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var HyperPcViewMoysklad_Product $view
 * @var RenderHelper                $this
 * @var ProductMarker               $product
 */

$specification = new Specification($product);
$rootGroups = $specification->getSpecification()['rootGroups'];

$dimensions = new JSON($product->params->get('dimensions', [], 'arr'));
$imageSrc = $dimensions->get('image', '', 'hpimagepath');
?>

<h2 class="uk-h1 uk-text-center">
    <?= Text::_('COM_HYPERPC_TAB_SPECIFICATION') . ' ' . $product->name ?>
</h2>

<?php if (!empty($imageSrc)) : ?>
    <div class="uk-text-center uk-overflow-hidden">
        <div class="hp-product-dimensions" style="background-image: url(<?= $imageSrc ?>)">
            <img src="<?= $imageSrc ?>" alt="<?= $dimensions->get('image_alt', '') ?>" class="uk-invisible">
        </div>
    </div>
<?php endif; ?>

<div class="uk-container uk-container-small">
    <table class="jsProductSpecification uk-table uk-table-divider tm-table-specs tm-table-specs--icons">
        <tbody>

        <?php foreach ($rootGroups as $groupId => $group) :
            $children = $group['groups'];
            ?>

            <tr class="tm-table-specs__group-head">
                <th colspan="2"><span class="uk-h3"><?= $group['title'] ?></span></th>
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
                        foreach ($child['parts'] as $part) :
                            $partName = $part['partName'];
                            if ($part['quantity'] > 1) {
                                $partName = $part['quantity'] . ' x ' . $partName;
                            }

                            if (isset($part['optionName'])) {
                                $partName .= ' ' . Text::sprintf('COM_HYPERPC_PRODUCT_OPTION', $part['optionName']);
                            }
                            ?>
                            <li class="hp-spec-item">
                                <?= $partName ?>
                                <?php if (isset($part['advantages'])) : ?>
                                    <ul class="uk-list uk-list-collapse uk-text-muted uk-text-small uk-margin-remove-top">
                                        <?php foreach ($part['advantages'] as $advantage) : ?>
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
