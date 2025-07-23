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
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var HyperPcViewProcessingplan $this
 */

$parts = $this->processingplan->getParts();
$product = $this->hyper['helper']['configuration']->findById($this->processingplan->id)->getProduct();
$assemblyKitId = $product->getAssemblyKitId();
$totalPrice = $this->hyper['helper']['money']->get(0);

$stockHelper = $this->hyper['helper']['moyskladStock'];
?>
<div class="uk-flex uk-flex-middle" uk-height-viewport>
    <div class="uk-section uk-width-1-1">
        <div class="uk-container uk-container-small">
            <h1 class="uk-h2 uk-text-center"><?= $this->processingplan->name ?></h1>
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small uk-width-1-1">
                <thead class="uk-text-left">
                    <th><?= Text::_('COM_HYPERPC_CART_TYPE_SEPARATOR_PART') ?></th>
                    <th><?= Text::_('COM_HYPERPC_AVAILABILITY') ?></th>
                </thead>
                <tbody>
                    <?php foreach ($parts as $itemKey => $part) :
                        $availability = $part->option instanceof MoyskladVariant ? $part->option->getAvailability() : $part->getAvailability();

                        if ($part->id !== $assemblyKitId) {
                            $price = ($part->option instanceof MoyskladVariant ? $part->option : $part)->getListPrice()->val();

                            $totalPrice->add($price * $part->quantity);
                        }

                        $balance = 0;
                        if ($availability === Stockable::AVAILABILITY_INSTOCK) {
                            $stockItem = $stockHelper->getItems([
                                'itemIds'   => [$part->id],
                                'optionIds' => $part->option instanceof MoyskladVariant ? [$part->option->id] : []
                            ]);

                            $balance = array_reduce($stockItem, function ($itemBalance, $item) {
                                return $itemBalance + $item->balance;
                            });
                        }
                        ?>
                        <tr>
                            <td>
                                <?= $part->quantity > 1 ? "{$part->quantity} x " : '' ?>
                                <?= $part->getName() ?>
                            </td>
                            <td class="<?= $this->_getAvailabilityLabelColorClass($availability) ?>">
                                <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . strtoupper($availability)) . ($balance ? ': ' . $balance : '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2">
                            <span class="uk-text-emphasis"><?= Text::_('COM_HYPERPC_TOTAL_COST') . ': ' . $totalPrice->text(); ?></span >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
