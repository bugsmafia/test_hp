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

use JBZoo\Data\Data;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

/**
 * @var Data         $enterPromoCode
 * @var RenderHelper $this
 * @var Order        $order
 * @var PartMarker   $item
 * @var Field        $field
 */

$isMobile = $this->hyper['detect']->isMobile();
$price         = (new Money($item['price']));
$priceWithRate = (new Money($item['priceWithRate']));
?>
<?php if ($isMobile) : ?>
    <div class="uk-grid uk-grid-small">
        <div class="uk-width-expand">
            <div class="uk-text-emphasis uk-heading-bullet">
                <?= $item['name'] ?>
            </div>
            <div>
                <span>
                    <?= $price->text() ?>
                </span>
                <span class="uk-text-muted uk-text-small">
                    (
                        <span><?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $item['quantity']) ?></span>
                        x
                        <span class="<?= $priceWithRate->val() != $price->val() ? ' tm-line-through' : '' ?>"><?= $price->text() ?></span>
                        <?= $priceWithRate->val() != $price->val() ? $priceWithRate->text() : '' ?>
                    )
                </span>
            </div>
        </div>
    </div>
<?php else : ?>
    <td class="hp-cart-cell-title" colspan="2">
        <div class="uk-text-muted">
            <?= $item['group'] ?>
        </div>
        <div class="uk-text-emphasis">
            <?= $item['name'] ?>
        </div>
    </td>
    <td class="hp-cart-cell-price4one uk-text-nowrap">
        <div class="hp-price4one<?= $priceWithRate->val() != $price->val() ? ' tm-line-through' : '' ?>">
            <?= $price->text() ?>
        </div>
        <span class="jsPromoCodeItemPrice">
            <?= $priceWithRate->val() != $price->val() ? $priceWithRate->text() : '' ?>
        </span>
    </td>
    <td class="hp-cart-cell-quantity">
        <?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $item['quantity']) ?>
    </td>
    <td class="hp-cart-item-total">
        <?= $priceWithRate->multiply($item['quantity']) ?>
    </td>
<?php endif;
