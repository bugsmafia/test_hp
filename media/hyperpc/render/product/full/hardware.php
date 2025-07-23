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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

/**
 * @var         HyperPcViewMoysklad_Product $view
 * @var         RenderHelper                $this
 * @var         MoyskladPart                $part
 * @var         array                       $groups
 * @var         MoyskladProduct             $product
 * @var         array                       $groupTree
 * @var         bool                        $allowChange
 * @var         array                       $productParts
 */

$configurator = $this->hyper['helper']['configurator'];
?>

<h2 class="uk-h1 uk-text-center">
    <?= Text::_('COM_HYPERPC_PRODUCT_EQUIPMENT') ?> <?= $product->name ?>
</h2>
<?php
$i = 0;
$maxPartsByDefault = 500; // unattainable value

$this->defaultPartsData = [];
foreach ($groupTree as $group) {
    if ($configurator->hasPartsInTree($group, $productParts)) {
        if ($group->alias === 'service') {
            $siteContext = $this->hyper['params']->get('site_context', 'hyperpc');
            echo '
                <div id="product-service" class="uk-padding-large uk-padding-remove-bottom">
                    <h2 class="uk-h1 uk-text-center uk-margin-remove-bottom">' . Text::_('COM_HYPERPC_PRODUCT_SERVICE_' . strtoupper($siteContext)) . '</h2>
                    <div class="uk-text-lead uk-text-center uk-margin-bottom">' . Text::_('COM_HYPERPC_PRODUCT_SERVICE_LEAD_' . strtoupper($siteContext)) . '</div>
                </div>';
        }

        foreach ($productParts as $groupId => $groupParts) {
            $treeIds = $configurator->getGroupTreeIds($group);
            if (in_array($groupId, $treeIds) &&
                array_key_exists($groups[$groupId]->parent_id, $groups)
            ) {
                if ($groups[$groupId]->level == 2 || ($product instanceof MoyskladProduct && $groups[$groupId]->level == 3)) {
                    $parentGroup = $groups[$groupId];
                } elseif ($groups[$groupId]->level == 3 || ($product instanceof MoyskladProduct && $groups[$groupId]->level == 4)) {
                    $parentGroup = $groups[$groups[$groupId]->parent_id];
                }

                if (!$parentGroup->showInConfig()) {
                    continue;
                }

                /** @var MoyskladPart|MoyskladService $item */
                foreach ((array) $groupParts as $item) {
                    $i++;

                    if ($item instanceof PartMarker && $item->isReloadContentForProduct($product->id)) {
                        $tpl = 'hardware_part_reload';
                    } else {
                        $tpl = 'hardware_part';
                    }

                    $partHtml = $this->hyper['helper']['render']->render('product/full/' . $tpl, [
                        'part'           => $item,
                        'product'        => $product,
                        'group'          => $parentGroup,
                        'hiddenAttr'     => $i > $maxPartsByDefault ? ' hidden' : '',
                        'hiddenCls'      => $i > $maxPartsByDefault ? ' hp-part-hidden' : '',
                        // TODO check availability by changes in the mini configurator
                        'hasChangeParts' => $allowChange ? $product->isMiniConfiguratorAvailableInGroup($parentGroup, $item) : false
                    ]);

                    echo $partHtml;
                }
            }
        }
    }
}
?>
<script>
    window.HpProductDefaultParts = <?= json_encode($this->defaultPartsData) ?>
</script>

<?php if ($i > $maxPartsByDefault) : ?>
    <div class="uk-text-center uk-padding">
        <button class="uk-button uk-button-default uk-button-large jsDetailToggle"
                uk-toggle="target: .hp-part-hidden; animation: uk-animation-fade"
                toggled-text="<?= Text::_('COM_HYPERPC_PRODUCT_SHOW_LESS_PARTS') ?>">
            <?= Text::_('COM_HYPERPC_PRODUCT_SHOW_MORE_PARTS') ?>
        </button>
    </div>
<?php endif;
