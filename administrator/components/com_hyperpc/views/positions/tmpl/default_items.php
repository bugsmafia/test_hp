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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewPositions $this
 */

?>
<?php if ((bool) $this->items !== false && count($this->items) > 0) : ?>
    <?php foreach ($this->items as $i => $item) :
        $editLink = $this->hyper['route']->build([
            'view'              => 'moysklad_' . $item->getType(),
            'layout'            => 'edit',
            'product_folder_id' => '%folder_id',
            'id'                => $item->id
        ]);

        ?>
        <tr>
            <td class="center">
                <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
            </td>
            <td>
                <a href="<?= $editLink ?>" title="<?= $item->name ?>">
                    <?= $item->name ?>
                </a>
            </td>
            <td>
                <?= $item->getTypeName(); ?>
            </td>
            <td class="center">
                <?php if ($item->isPart()) : ?>
                    <div class="text-center">
                        <?= $this->hyper['helper']['html']->canBuy($item->getPart()->isForRetailSale(), $i, 'positions') ?>
                    </div>
                <?php endif; ?>
                <?php if ($item->isProduct()) : ?>
                    <div class="text-center">
                        <?= $this->hyper['helper']['html']->canBuy($item->getProduct()->isOnSale(), $i, 'positions') ?>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <?= $item->list_price->text() ?>
            </td>
            <td>
                <?= $item->sale_price->text() ?>
            </td>
            <td class="center">
                <input name="sorting" value="<?= $item->ordering ?>" data-id="<?= $item->id ?>"
                       type="number" class="hp-sorting jsSorting"/>
            </td>
            <td class="center">
                <div class="text-center">
                    <?php
                    if (in_array($item->state, [HP_STATUS_PUBLISHED, HP_STATUS_UNPUBLISHED])) {
                        echo HTMLHelper::_('jgrid.published', (int) $item->state, $i, 'positions.');
                    } else {
                        echo $this->hyper['helper']['html']->published($item->state);
                    }
                    ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif;
