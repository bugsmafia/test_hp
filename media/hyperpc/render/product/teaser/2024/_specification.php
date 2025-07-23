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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Html\Data\Product\Specification;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var RenderHelper        $this
 * @var ProductMarker       $product
 * @var CategoryMarker[]    $groups
 */

$specification = new Specification($product);
$rootGroups = $specification->getSpecification()['rootGroups'];
$itemKey = $product->getItemKey();
$productName = $product->getName();

?>
<ul class="tm-product-teaser__specification uk-list tm-color-white-smoke tm-margin-8-bottom">
    <?php foreach ($rootGroups as $rootGroup) :
        $children = $rootGroup['groups'];
        ?>
        <?php foreach ($children as $childId => $child) :
            $group = $groups[$childId] ?? null;
            if (!$group || !$group->getParams()->get('show_in_teaser', false, 'bool')) {
                continue;
            }
            ?>
            <li>
                <div class="uk-flex-none">
                    <span class="uk-icon tm-color-gray-200 tm-margin-12-right uk-visible@s" data-uk-icon="hp-<?= $child['alias'] ?>"></span>
                </div>
                <div class="uk-overflow-hidden">
                    <div class="tm-color-gray-200"><?= $child['title'] ?></div>
                    <div class="uk-text-truncate">
                        <?php
                        $i = 0;
                        foreach ($child['parts'] as $partId => $part) :
                            $partName = $this->hyper['helper']['string']->stripSquareBracketContent($part['partName']);

                            if (isset($part['optionName'])) {
                                $partName .= ' ' . $part['optionName'];
                            }

                            if ($part['quantity'] > 1) {
                                $partName = sprintf('%s x %s', $part['quantity'], $partName);
                            }

                            if (++$i < count($child['parts'])) {
                                $partName .= ',';
                            }
                            ?>
                            <?php if (!empty($part['viewUrl'])) : ?>
                                <a href="<?= $part['viewUrl'] ?>"
                                    class="jsLoadIframe uk-link-reset">
                                    <?= $partName ?>
                                </a>
                            <?php else : ?>
                                <?= $partName ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    <?php endforeach; ?>
</ul>
<div class="tm-padding-56-bottom jsShowFullSpecification" data-props='{"itemKey": "<?= $itemKey ?>", "title": "<?= $productName ?>"}'>
</div>
