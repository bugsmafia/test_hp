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

use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CreditHelper;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * @var RenderHelper             $this
 * @var CreditHelper             $cHelper
 * @var ProductMarker|PartMarker $item
 * @var Money                    $price
 */

$this->hyper['helper']['assets']
    ->js('js:widget/site/credit-calculate.js')
    ->widget('.jsCreditCalculatorWrapper', 'HyperPC.CreditCalculateRate');

$cHelper     = $this->hyper['helper']['credit'];
$tariffs     = $cHelper->getTariffs();
$countTariff = count((array) $tariffs);
$ruleData    = new JSON((array) $price->getRuleData($price->getRule()));
$priceVal    = $price->val();
?>

<div class="jsCreditCalculatorWrapper hp-credit-calculator uk-flex uk-flex-middle" uk-height-viewport="expand: true">
    <div class="uk-width-1-1">
        <div class="uk-container uk-container-large uk-margin-medium-bottom">
            <div class="uk-grid">
                <div class="uk-width-1-1 uk-width-medium@l">
                    <div class="uk-margin-large-top uk-margin-bottom uk-flex uk-flex-center@s">
                        <?php if (isset($item)) : ?>
                            <?= $this->hyper['helper']['render']->render('credit/item_teaser', [
                                'item'  => $item,
                                'price' => $price,
                            ]);
                            ?>
                        <?php else :
                            $maxPrice = new Money($this->hyper['helper']['credit']->getMaxPrice());

                            $inputPriceAttrs = [
                                'class' => [
                                    'jsCreditCalculatorCustomPrice',
                                    'uk-input uk-form-large uk-width-1-1 uk-text-center uk-text-large'
                                ],
                                'min'           => 1000,
                                'max'           => $maxPrice->value() > 0 ? (int) $maxPrice->value() : 9999999,
                                'step'          => 100,
                                'type'          => 'number',
                                'value'         => $priceVal,
                                'onkeypress'    => 'if (this.value.length > 6) return false;',
                                'placeholder'   => Text::_('COM_HYPERPC_CREDIT_CALCULATOR_CUSTOM_PRICE_HINT')
                            ];
                            ?>
                            <div class="uk-width-1-1 uk-width-medium@s">
                                <div class="uk-h4 uk-margin-small uk-text-center">
                                    <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_CUSTOM_PRICE_TITLE') ?>
                                </div>
                                <div class="uk-margin-small">
                                    <input <?= $this->hyper['helper']['html']->buildAttrs($inputPriceAttrs) ?>/>
                                </div>
                                <button class="uk-button uk-button-primary uk-width-1-1 jsCreditCalculatorCustomSubmit"
                                        type="button">
                                    <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_CALCULATE') ?>
                                </button>
                                <hr />
                                <?php if ($maxPrice) : ?>
                                    <div class="uk-text-muted uk-text-small">
                                        * максимальная сумма кредита <span class="uk-text-nowrap"><?= $maxPrice->text() ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="uk-width-expand">
                    <h2 class="uk-text-center@s">
                        <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_CHOOSE_TARIFF') ?>
                    </h2>

                    <div class="uk-grid uk-hidden@s">
                        <div>
                            <div class="jsScrollableList uk-button-group tm-button-group-nav uk-text-nowrap"
                                 uk-switcher="toggle: > *; connect: .hp-credit-calculator__tariffs; animation: uk-animation-fade; swiping: false; duration: 100">
                                <?php foreach ($tariffs as $tariff) :
                                    $isDefault = Filter::bool($tariff->getConfig('is_default'));
                                    ?>
                                    <button class="uk-button uk-button-secondary uk-margin-remove<?= $isDefault ? ' uk-active' : '' ?>" role="button">
                                        <?= $tariff->getConfig('name') ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <ul class="hp-credit-calculator__tariffs jsCreditCalculatorTariffs uk-switcher uk-grid uk-grid-small uk-child-width-1-1 uk-child-width-expand@s uk-grid-match" uk-grid>
                        <?= $this->hyper['helper']['credit']->renderTariff($item, $priceVal) ?>
                    </ul>

                    <div class="jsCreditCalculatorSummary uk-grid uk-grid-small uk-flex-bottom uk-visible@s" uk-grid>
                        <div>
                            <div class="uk-card uk-card-body uk-card-small uk-card-default uk-border-rounded">
                                <div class="uk-text-muted">
                                    <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_TERM') ?>
                                </div>
                                <div class="uk-text-large uk-margin-small-top">
                                    <span class="jsCreditCalculatorLoanTermVal"></span>
                                    <?= Text::_('COM_HYPERPC_MONTH_SHORT') ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="uk-card uk-card-body uk-card-small uk-card-default uk-border-rounded">
                                <div class="uk-text-muted">
                                    <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_MONTHLY_PAYMENT') ?>
                                </div>
                                <div class="jsCreditCalculatorSummaryMonthlyPayment uk-text-large uk-margin-small-top uk-text-warning"></div>
                            </div>
                        </div>
                        <div>
                            <div class="uk-card uk-card-body uk-card-small uk-card-default uk-border-rounded">
                                <div class="uk-text-muted">
                                    <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_TOTAL_PRICE_FOR_PAYMENT') ?>
                                </div>
                                <div class="jsCreditCalculatorCostOfLoan uk-text-large uk-margin-small-top"></div>
                            </div>
                        </div>
                    </div>

                    <div class="uk-text-muted uk-text-small uk-text-italic uk-margin-medium-top">
                        <?= Text::_('COM_HYPERPC_CREDIT_CALCULATOR_NOTIFY_MSG') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
