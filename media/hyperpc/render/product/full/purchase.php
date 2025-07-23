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

use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 * @var Money           $price
 * @var array           $properties
 * @var string          $layout can be 'head' or 'nav'
 */

$availability = $product->getAvailability();

$buttons = [];

$buyButtonKey = 'buy';

switch ($layout) {
    case 'head':
        $buyButtonKey = 'add_to_cart';
        break;
    case 'nav':
        if ($product->isPublished()) {
            $buttons[] = 'configurator';
        }
        break;
}

$canChanged = (count($properties) || $product->hasPartsMini());
if ($canChanged) {
    $buyButtonKey .= '_and_save_config';
}

if ($product->isFromStock()) {
    $buyButtonKey = 'buy_in_stock';
}
$buttons[] = $buyButtonKey;

if (!in_array($availability, [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
    $buttons = ['buy'];
}
?>

<?= $this->hyper['helper']['render']->render('common/price/item-price', [
    'price'      => $price,
    'entity'     => $product,
    'htmlPrices' => $layout === 'head' ? true : $canChanged
]); ?>

<?= $product->getRender()->getCartBtn('button', $buttons);
