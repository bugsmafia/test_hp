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
 *
 * @var         RenderHelper $this
 * @var         Position $item
 * @var         Money $price
 */

use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

defined('_JEXEC') or die('Restricted access');

$imageWidth = 260;
$imageSrc = $this->hyper['helper']['cart']->getItemImage($item, $imageWidth, 0);

$itemTitle = $item->name;

if ($item instanceof PartMarker) {
    /** @var PartMarker $item */

    $optionTakenFromPart = false;
    if ($item->option instanceof OptionMarker && $item->option->id) {
        $optionTakenFromPart = true;
    }

    /** @var OptionMarker $option */
    $option = $optionTakenFromPart ? $item->option : $item->getDefaultOption(false);

    if ($option->id) {
        if (!$optionTakenFromPart) {
            $item->set('option', $option);
        }

        $itemTitle .= ' ' . $option->name;
    }

    if (!$item->can_by) {
        $price = null;
    }
}
?>

<div>
    <div>
        <img src="<?= $imageSrc ?>" alt="<?= $item->name ?>" width="<?= $imageWidth ?>" />
    </div>

    <div class="uk-h4 uk-margin-remove-bottom uk-margin-small-top">
        <?= $itemTitle ?>
    </div>

    <?php if ($price) : ?>
        <div class="tm-text-medium">
            <?= $price->text() ?>
        </div>
    <?php endif; ?>
</div>
