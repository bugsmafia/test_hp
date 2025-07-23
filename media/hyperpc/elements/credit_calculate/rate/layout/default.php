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

use JBZoo\Utils\Filter;
use Joomla\CMS\Language\Text;

/**
 * @var ElementCreditCalculateRate $this
 */

$discount       = $this->getDiscount();
$item           = $this->getConfig('item');
$term           = $this->getConfig('term');
$description    = $this->getConfig('description');
$hasTermRange   = (bool) preg_match('/:/', $term);
$isDefault      = Filter::bool($this->getConfig('is_default'));
?>
<li class="hp-credit-calculator__tariff jsCreditCalculatorTariff<?= $isDefault ? ' uk-active jsCreditCalculatorActiveTariff' : '' ?>"
    data-tariff="<?= $this->getIdentifier() ?>" data-price="<?= $this->getPrice($discount)->val() ?>">
    <div class="uk-card uk-card-body uk-card-default uk-card-hover uk-border-rounded">
        <h3 class="uk-card-title"><?= $this->getTitle() ?></h3>
        <?php if ($description) : ?>
            <div class="uk-card-description jsHeightFixRow"><?= $description ?></div>
        <?php endif; ?>
        <hr />

        <ul class="uk-list uk-list-divider jsListData">
            <li>
                <?php if ($hasTermRange) :
                    list($from, $to) = explode(':', $term);
                    $to   = trim($to);
                    $from = trim($from);
                ?>
                    <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                        <span><?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_TERM') ?></span>
                        <span>
                            <span class="jsCreditCalculatorLoanTermVal"><?= $from ?></span>
                            <?= Text::_('COM_HYPERPC_MONTH_SHORT') ?>
                        </span>
                    </div>
                    <div>
                        <input class="uk-range jsDataTerm" type="range"
                               value="<?= $from ?>" min="<?= $from ?>" max="<?= $to ?>" step="1">
                    </div>
                <?php else : ?>
                    <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                        <span><?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_TERM') ?></span>
                        <span>
                            <?= $term . ' ' . Text::_('COM_HYPERPC_MONTH_SHORT') ?>
                            <input class="jsDataTerm" type="hidden" value="<?= $term ?>">
                        </span>
                    </div>
                    <div>
                        <input class="jsDataTerm" type="hidden" value="<?= $term ?>">
                    </div>
                <?php endif; ?>
            </li>
            <li class="uk-hidden">
                <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                    <span><?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_CUSTOM_PRICE_TITLE') ?></span>
                    <span>
                        <?php if ($discount > 0) : ?>
                            <span class="tm-line-through"><?= $this->getPrice()->html() ?></span>
                        <?php endif; ?>
                        <span><?= $this->getPrice($discount)->html() ?></span>
                    </span>
                </div>
            </li>
            <li class="jsCreditCalculatorTariffRate" data-value="<?= $this->getRateVal()?>">
                <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                    <span><?= Text::_('HYPER_ELEMENT_CREDIT_CALCULATE_RATE_TITLE') ?></span>
                    <span><?= $this->getConfig('rate') ?></span>
                </div>
            </li>
            <li>
                <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                    <span><?= Text::_('HYPER_ELEMENT_CREDIT_CALCULATE_MONTHLY_PAYMENT_TITLE') ?></span>
                    <span class="jsCreditCalculatorMonthlyPayment"><?= $this->getMonthlyPayment()->html() ?></span>
                </div>
            </li>
            <li>
                <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                    <span><?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_TOTAL_PRICE_FOR_PAYMENT') ?></span>
                    <span class="jsCreditCalculatorCostOfLoan"><?= $this->getCheckoutTotalPrice()->html() ?></span>
                </div>
            </li>
            <li>
                <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                    <span><?= Text::_('HYPER_ELEMENT_CREDIT_CALCULATE_OVER_PAYMENT_TITLE') ?></span>
                    <span>
                        <span class="jsCreditCalculatorOverPayment"><?= $this->getOverPayment()->html() ?></span> /
                        <span class="jsCreditCalculatorOverPaymentPercent"><?= $this->getOverPaymentByPercent() ?></span> %
                    </span>
                </div>
            </li>
            <?php if ($this->getDiscount() > 0) : ?>
                <li>
                    <div class="uk-grid uk-grid-small uk-child-width-auto uk-flex-between">
                        <span><?= Text::_('HYPER_ELEMENT_CREDIT_CALCULATE_DISCOUNT_TITLE') ?></span>
                        <span><span class="jsCreditCalculatorDiscount"><?= $this->getDiscount() ?></span> %</span>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
        <?php if ($item) : ?>
            <input type="hidden" name="item" value="<?= $item->id ?>" />
        <?php endif; ?>
        <input type="hidden" name="identifier" value="<?= $this->getIdentifier() ?>" />
    </div>
</li>
