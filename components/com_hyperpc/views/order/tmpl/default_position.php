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
 * @author      Roman Evsyukov
 *
 * @todo        Render positions from position data collection instead of entities. This will allow the removed items to be shown
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoneyHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Object\Order\PositionData;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Object\Processingplan\PlanItemDataCollection;

/**
 * @var         RenderHelper            $this
 * @var         Order                   $order
 * @var         string                  $itemKey
 * @var         Position|null           $item
 * @var         Position[]              $items
 * @var         ProductFolder[]         $groups
 * @var         PositionData            $positionData
 * @var         PositionDataCollection  $positionsData
 * @var         Data                    $enterPromoCode
 */

if (!$item) {
    return; // Skip unavailable positions
}

/** @var MoneyHelper */
$moneyHelper = $this->hyper['helper']['money'];

$isMobile = $this->hyper['detect']->isMobile();

$listPrice = $moneyHelper->get($positionData->price);
$salePrice = $moneyHelper->get($positionData->price * (1 - $positionData->discount / 100));
$linePrice = $salePrice->multiply($positionData->quantity, true);

$order->discount->add($positionData->price * ($positionData->discount / 100) * $positionData->quantity);

$imageSrc = $this->hyper['helper']['cart']->getItemImage($item);

if ($positionData->type === 'productvariant' && $item && $item->id) {
    /** @var MoyskladProduct $item */
    $configuration = $item->getConfiguration();

    $processingPlan = $this->hyper['helper']['processingPlan']->findById($positionData->option_id);
    $planItems = PlanItemDataCollection::create($processingPlan->parts->getArrayCopy());

    $configurationParts = new JSON();
    foreach ($planItems as $itemKey => $planItem) {
        $partData = $configuration->parts->get($planItem->id);
        if ($partData) {
            $configurationParts->set($planItem->id, $partData);
        } elseif ($planItem->id !== $item->getAssemblyKitId()) {
            $planPart = $this->hyper['helper']['moyskladPart']->findById($planItem->id);
            if ($planPart->id) {
                $configurationParts->set($planItem->id, [
                    "id" => $planItem->id,
                    "price" => 0,
                    "group_id" => $planPart->getFolderId(),
                    "quantity" => $planItem->quantity,
                    "option_id" => $planItem->option_id
                ]);
            }
        }
    }

    // Add services to config parts
    $pattern = '/position-\d+-product-' . $positionData->id .  '-' . $positionData->option_id . '/';
    /** @var PositionData $data  */
    foreach ($positionsData->items() as $key => $data) {
        if (!preg_match($pattern, $key)) {
            continue;
        }

        if (isset($items[$key])) {
            $service = $items[$key];

            $serviceQuantity = $data->quantity / $positionData->quantity;

            $configurationParts->set($service->id, [
                'id' => $service->id,
                'group_id' => $service->product_folder_id,
                'quantity' => $serviceQuantity,
                'price' => (int) $data->price,
                'option_id' => null
            ]);

            $listPrice->add($data->price);

            $serviceSalePrice = $data->price * (1 - $data->discount / 100);
            $salePrice->add($serviceSalePrice);
            $linePrice->add($serviceSalePrice * $data->quantity);

            $order->discount->add($data->price * ($data->discount / 100) * $data->quantity);
        }
    }

    $configuration->parts = $configurationParts;

    $modalHref = 'hp-configuration-' . $positionData->option_id;
    $specsHtml = $this->hyper['helper']['render']->render('product/configuration_parts', [
        'product'     => $item,
        'optionsMode' => 'fromPart',
        'excludeExternalParts' => true
    ]);
}

$showDiscount = $listPrice->val() !== $salePrice->val();
$viewUrlParams = [];
if ($positionData->type === 'variant') {
    $viewUrlParams['opt'] = true;
}
?>
<?php if ($isMobile) : ?>
    <div class="uk-grid uk-grid-small">
        <div style="max-width: 76px; margin-inline-end: -10px">
            <a href="<?= $item->getViewUrl($viewUrlParams) ?>" target="_blank">
                <img src="<?= $imageSrc ?>" alt="<?= $positionData->name ?>" />
            </a>
        </div>
        <div class="uk-width-expand">
            <div class="uk-text-emphasis uk-heading-bullet">
                <?= $positionData->name ?>
            </div>
            <div>
                <span>
                    <?= $linePrice->text() ?>
                </span>
                <span class="uk-text-muted uk-text-small">
                    (
                        <span><?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $positionData->quantity) ?></span>
                        x
                        <span class="<?= $showDiscount ? ' tm-line-through' : '' ?>"><?= $listPrice->text() ?></span>
                        <?= $showDiscount ? $salePrice->text() : '' ?>
                    )
                </span>
            </div>

            <?php if ($positionData->type === 'productvariant') : ?>
                <div>
                    <span class="uk-text-primary jsDetailToggle jsShowMore"
                        toggled-text="<?= Text::_('COM_HYPERPC_CART_HIDE_CONFIGURATION') ?>"
                        toggled-icon="icon:chevron-down" uk-toggle="target: #<?= $modalHref ?>; animation: uk-animation-fade;">
                        <span uk-icon="icon: chevron-right" style="vertical-align: bottom"></span>
                        <?= Text::_('COM_HYPERPC_CART_CHECK_CONFIGURATION'); ?>
                    </span>
                </div>
            <?php endif; ?>

        </div>
    </div>
    <?php if ($positionData->type === 'productvariant') : ?>
        <div id="<?= $modalHref ?>" hidden>
            <?= $specsHtml ?>
        </div>
    <?php endif; ?>
<?php else : ?>
    <td class="hp-cart-cell-img" width="10%">
        <a href="<?= $item->getViewUrl($viewUrlParams) ?>" target="_blank">
            <img src="<?= $imageSrc ?>" alt="<?= $positionData->name ?>" />
        </a>
    </td>
    <td class="hp-cart-cell-title">
        <div class="uk-text-muted">
            <?php
            if ($positionData->type !== 'productvariant' && array_key_exists($item->product_folder_id, $groups)) {
                $group = $groups[$item->product_folder_id];
                echo $group->title;
            }
            ?>
        </div>
        <div class="uk-text-emphasis">
            <?= $positionData->name ?>
        </div>
        <?php if ($positionData->type === 'productvariant') : ?>
            <a href="#<?= $modalHref ?>" class="uk-display-block" uk-toggle>
                <?= Text::_('COM_HYPERPC_CART_CHECK_CONFIGURATION') ?>
            </a>

            <?php
            echo $this->hyper['helper']['uikit']->modal($modalHref, implode(PHP_EOL, [
                '<div class="uk-container-small uk-margin-auto">',
                    '<div class="uk-h2">' . $item->getName() . '</div>',
                    $specsHtml,
                '</div>'
            ]));
            ?>
        <?php endif; ?>
    </td>
    <td class="hp-cart-cell-price4one uk-text-nowrap">
        <div class="hp-price4one<?= $showDiscount ? ' tm-line-through' : '' ?>">
            <?= $listPrice->text() ?>
        </div>
        <span>
            <?= $showDiscount ? $salePrice->text() : '' ?>
        </span>
    </td>
    <td class="hp-cart-cell-quantity">
        <?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $positionData->quantity) ?>
    </td>
    <td class="hp-cart-item-total">
        <?= $linePrice->text() ?>
    </td>
<?php endif;
