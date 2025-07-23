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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * @var          int $promoRate
 * @var          int|null $option
 * @var          int|null $savedConfiguration
 * @var          string $itemHash
 * @var          string $type
 * @var          Money $unitPrice
 * @var          Entity $item
 */

if ($type === CartHelper::TYPE_PRODUCT) {
    $identifier = 'jform[products][' . $itemHash . ']';
} elseif ($type === CartHelper::TYPE_PART) {
    $identifier = 'jform[parts][' . $itemHash . ']';
} else {
    $identifier = 'jform[positions][' . $itemHash . ']';
}
?>

<input type="hidden" value="<?= $promoRate ?>" name="<?= $identifier ?>[rate]" class="jsItemRateValue" />
<input type="hidden" value="<?= $item->id ?>" name="<?= $identifier ?>[id]" />
<input type="hidden" value="<?= $unitPrice->val() ?>" name="<?= $identifier ?>[price]" />

<?php if ($savedConfiguration > 0) : ?>
    <input type="hidden" value="<?= $savedConfiguration ?>" name="<?= $identifier ?>[saved_configuration]" />
<?php endif; ?>

<?php if ($type === CartHelper::TYPE_PART) : ?>
    <input type="hidden" value="<?= $item->group_id ?>" name="<?= $identifier ?>[group_id]" />
<?php endif; ?>

<?php if ($type === CartHelper::TYPE_PART || $type === CartHelper::TYPE_POSITION) : ?>
    <input type="hidden" value="<?= $option ?>" name="<?= $identifier ?>[option_id]" />
<?php endif; ?>
