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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var     RenderHelper        $this
 * @var     ProductMarker       $product
 * @var     SaveConfiguration   $configuration
 */

$configParts = $product->getConfigParts(true, 'a.product_folder_id ASC', false, true);
?>

<table class="pdf-table-divider" style="width:100%; font-size: 1.1rem">
    <tbody>
        <tr>
            <td>
                <?= $product->name ?>
            </td>
            <td class="pdf-text-right">
                <?= $product->getConfigPrice()->text() ?>
            </td>
        </tr>
        <?php ?>
        <?php foreach ($configParts as $groupId => $parts) : ?>
            <?php
            /** @var PartMarker|MoyskladService $part */
            foreach ($parts as $part) :
                if (!$part->isDetached()) {
                    continue;
                }
                $partPrice = $part->getQuantityPrice(false);
                $partPromoPrice = $part->getQuantityPrice(true);
                $group = $part->getFolder();
                ?>
                <tr>
                    <td>
                        <?= $group->title ?>
                        <?= $part->getConfiguratorName($product->id, true, true); ?>
                    </td>
                    <td class="pdf-text-right">
                        <?php if ($partPrice->val() !== $partPromoPrice->val()) : ?>
                            <small><strike><?= $partPrice->text(); ?></strike></small>
                        <?php endif; ?>
                        <?= $partPromoPrice->text(); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <tr>
            <td class="pdf-text-right" colspan="2">
                <h3 class="pdf-text-normal pdf-margin-remove">
                    <?= Text::_('COM_HYPERPC_TOTAL') ?>
                    <?= $configuration->getDiscountedPrice()->text() ?>
                </h3>
            </td>
        </tr>
    </tbody>
</table>
