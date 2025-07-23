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
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * @var int $quantity
 * @var string $type
 * @var Money $promoPrice
 * @var Money $totalPrice
 * @var Entity $item
 */

?>

<?php if ($type === CartHelper::TYPE_POSITION && $item instanceof MoyskladProduct) : ?>
    <div class="uk-text-emphasis uk-heading-bullet">
        <?= $item->getName() ?>
    </div>
<?php elseif ($type === CartHelper::TYPE_POSITION) : ?>
    <div class="uk-text-muted">
        <?= $item->getFolder()->title ?>
    </div>
    <div class="uk-text-emphasis uk-heading-bullet">
        <?= $item->getConfiguratorName() ?>
    </div>
<?php endif; ?>

<div>
    <span class="jsQuantityTotal"><?= $totalPrice->html() ?></span>
    <span class="uk-text-muted uk-text-small">
        (<span class="jsQuantityValue" data-quantity="<?= $quantity ?>"><?= Text::sprintf('COM_HYPERPC_ORDER_ITEM_QUANTITY', $quantity) ?></span>
        x <span class="jsCheckoutItemUnitPrice"><?= $promoPrice->html() ?></span>)
    </span>
</div>
