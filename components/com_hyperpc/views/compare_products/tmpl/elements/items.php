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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;

/**
 * @var         JSON    $items
 * @var         JSON    $itemData
 * @var         JSON    $arrayItem
 */

$countGroupItem = count($items->get('items'));
?>

<ul class="uk-list">
    <li>
        <?php if ($items->get('hasEqualRow')) : ?>
            <div class="uk-container uk-container-large uk-margin-top">
                <div uk-margin>
                    <span class="jsCompareShowAll tm-button-filter uk-button uk-button-small uk-button-default">
                        <?= Text::_('COM_HYPERPC_COMPARE_SHOW_ALL') ?>
                    </span>
                    <span class="jsCompareHideEqual tm-button-filter uk-active uk-disabled uk-button uk-button-small uk-button-default">
                        <?= Text::_('COM_HYPERPC_COMPARE_HIDE_EQUAL') ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
        <div class="uk-margin-auto uk-container-large uk-margin">
            <table class="uk-table uk-table-divider hp-table-compare hp-table-compare--slide jsScrollable" data-count="<?= $countGroupItem ?>">
                <tr class="hp-table-compare__head">
                    <?php
                    $showButtonsLine = false;
                    foreach ($items->get('items') as $itemKey => $itemData) {
                        $itemPrice = $itemData->find('buy.price');
                        if ($itemPrice) {
                            $showButtonsLine = true;
                        }

                        echo $this->hyper['helper']['render']->render('compare_products/tmpl/elements/item', [
                            'itemPrice' => $itemPrice,
                            'itemData'  => $itemData,
                            'itemKey'   => $itemKey
                        ], 'views');
                    }

                    $isMaxCount = $countGroupItem === $this->hyper['params']->get('compare_max', 4, 'int');
                    ?>
                    <td class="jsShowCompareSidebar"<?= $isMaxCount ? ' hidden' : '' ?>>
                        <div class="uk-position-relative">
                            <canvas width="450" height="450"></canvas>
                            <button class="uk-width-1-1 uk-button uk-button-secondary uk-position-cover uk-position-medium"
                                uk-toggle=".jsCompareSidebar"
                                style="line-height: 1.25"
                                >
                                <span class="uk-icon" uk-icon="icon: plus-circle; ratio: 1.5"></span>
                                <span class="uk-display-block uk-margin-small-top">
                                    <?= Text::_('COM_HYPERPC_ADD_COMPUTER') ?>
                                </span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php if ($showButtonsLine) : ?>
                    <tr class="hp-table-compare__buttons-row">
                        <?php foreach ($items->get('items') as $itemKey => $itemData) : ?>
                            <?= $this->hyper['helper']['render']->render('compare_products/tmpl/elements/item_buy_btn', [
                                'itemData'  => $itemData,
                                'itemKey'   => $itemKey
                            ], 'views');
                            ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
                <?php
                foreach ($items->get('properties') as $property) :
                    if ($property->show === false) {
                        continue;
                    }

                    if ($property->type === 'hpseparator') : ?>
                        <tr class="hp-table-compare__group-head<?= $property->isEqual ? ' jsEqualValues uk-hidden' : '' ?>">
                            <td colspan="<?= $countGroupItem ?>" class="uk-text-emphasis">
                                <?= $property->get('label'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <tr class="hp-table-compare__property-name<?= $property->isEqual ? ' jsEqualValues uk-hidden' : '' ?>">
                            <td colspan="<?= $countGroupItem ?>" class="uk-text-uppercase uk-text-small uk-text-muted">
                                <?= $property->label; ?>
                            </td>
                        </tr>
                        <tr data-group-id="<?= $property->groupId ?>" class="hp-table-compare__values<?= $property->isEqual ? ' jsEqualValues uk-hidden' : '' ?>">
                            <?php foreach ($property->values as $partId => $value) : ?>
                                <td data-part-id="<?= $partId ?>">
                                    <?php
                                    if ($value !== null && trim($value) !== '') {
                                        echo $value;
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endif ?>

                <?php endforeach; ?>
            </table>
        </div>
    </li>
</ul>
