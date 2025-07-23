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
use HYPERPC\Money\Type\Money;

/**
 * @var         int     $quantity
 * @var         Money   $unitPrice
 * @var         Money   $promoPrice
 * @var         Money   $totalPrice
 */
?>
<div class="jsItmeTotalPrice">
    <?= $totalPrice->html() ?>
</div>
<div class="uk-text-small uk-text-muted">
    <span class="tm-line-through jsItemPromoPrice" style="margin-inline-end: 5px"<?= $unitPrice->val() === $promoPrice->val() ?  ' hidden' : '' ?>>
        <?= $unitPrice->html() ?>
    </span>
    <span class="jsItemUnitPrice"<?= $quantity === 1 ? ' hidden' : '' ?>>
        <span class="jsPromoCodeItemPrice">
            <?= $promoPrice->html(); ?>
        </span>
        <?= Text::_('COM_HYPERPC_PER_PIECE') ?>
    </span>
</div>

<input type="hidden" class="jsPriceForOne" value="<?= $promoPrice->val() ?>" />
