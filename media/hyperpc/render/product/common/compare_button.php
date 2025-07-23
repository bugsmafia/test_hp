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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Object\Product\StockData;
use HYPERPC\ORM\Entity\ProductInStock;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var         RenderHelper    $this
 * @var         ProductMarker   $product
 */

$itemKey = $product->getItemKey();
preg_match('/(\w+)-(.+)/', $itemKey, $matches);
list(,$type, $compareKey) = $matches;

$compareItems = $this->hyper['helper']['compare']->getItems($type);
$isInCompare  = array_key_exists($compareKey, $compareItems);
$linkTitle    = $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';
$linkText     = $isInCompare ? 'COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT' : 'COM_HYPERPC_COMPARE_ADD_BTN_TEXT';

$buttonAttrs = [
    'class' => 'jsCompareAdd hp-compare-btn uk-transition-fade' . ($isInCompare ? ' inCompare' : ''),
    'title' => Text::_($linkTitle),
    'data' => [
        'id'      => $product->id,
        'type'    => $type,
        'itemKey' => $itemKey
    ]
];

if ($product->params->get('stock') instanceof ProductInStock) {
    $buttonAttrs['data']['stock-id'] = $product->params->get('stock')->id;
} elseif ($product->params->get('stock') instanceof StockData) {
    $buttonAttrs['data']['option-id'] = $product->getStockConfigurationId();
}
?>

<span class="hp-compare-btn-wrapper uk-link-muted" data-group="products">
    <a <?= $this->hyper['helper']['html']->buildAttrs($buttonAttrs) ?>>
        <span class="uk-icon">
            <?= $this->hyper['helper']['html']->svgIcon('hpCompareAdd') ?>
        </span>
        <span class="hp-compare-btn-text">
            <?= Text::_($linkText) ?>
        </span>
    </a>
</span>
