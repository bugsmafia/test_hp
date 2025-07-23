/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    HYPERPC
 * @license    Proprietary
 * @copyright  Proprietary https://hyperpc.ru/license
 * @link       https://github.com/HYPER-PC/HYPERPC".
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.CreditCalculateRate', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            const $tariffs = $this.$('.jsCreditCalculatorTariff');
            let $activeTariff = $tariffs.filter('.jsCreditCalculatorActiveTariff');
            if ($activeTariff.length === 0) {
                $activeTariff = $tariffs.eq(0);
                $activeTariff.addClass('uk-active jsCreditCalculatorActiveTariff');
            } else if ($activeTariff.length > 1) {
                $activeTariff = $activeTariff.eq(0);
                $activeTariff.siblings().removeClass('uk-active jsCreditCalculatorActiveTariff');
            }

            $this._update($this);

            UIkit.util.on('.jsCreditCalculatorTariffs', 'show', function () {
                $this.$('.jsCreditCalculatorTariff').filter('.uk-active').addClass('jsCreditCalculatorActiveTariff').siblings().removeClass('jsCreditCalculatorActiveTariff');
                $this._update($this);
            });
        },

        /**
         * Update.
         *
         * @param $this
         *
         * @private
         */
        _update: function ($this) {
            const loanAmount         = parseInt($this._getPrice($this)),
                  $activeTariff      = $this.$('.jsCreditCalculatorActiveTariff'),
                  rate               = $activeTariff.find('.jsCreditCalculatorTariffRate').data('value'),
                  loanTermVal        = $activeTariff.find('.jsDataTerm').val(),
                  monthlyPayment     = $this._calculateMonthlyPayment(loanAmount, rate, loanTermVal),
                  discount           = $activeTariff.find('.jsCreditCalculatorDiscount').text(),
                  checkoutTotalPrice = $this._getCheckoutTotalPrice(loanAmount, monthlyPayment, loanTermVal, rate),
                  overPayment        = $this._getOverPayment(checkoutTotalPrice, loanAmount, discount),
                  overPaymentPercent = $this._getOverPaymentPercent(loanAmount, overPayment, discount);

            $this._setMonthlyPayment($this, $activeTariff, monthlyPayment);
            $this._setLoanTerm($this, $activeTariff, loanTermVal);
            $this._setCostOfLoan($this, $activeTariff, checkoutTotalPrice);
            $this._setOverPayment($this, $activeTariff, overPayment, overPaymentPercent);
        },

        /**
         * Set monthly payment.
         *
         * @param $this
         * @param $activeTariff
         * @param {number} value
         *
         * @private
         */
        _setMonthlyPayment: function ($this, $activeTariff, value) {
            const valueFormated = $this._priceFormat(value),
                  $monthlyPayment = $activeTariff.find('.jsCreditCalculatorMonthlyPayment');
            $monthlyPayment.find('.simpleType-value').text(valueFormated);
            $this.$('.jsCreditCalculatorSummaryMonthlyPayment').text($monthlyPayment.text());
        },

        /**
         * Set loan term.
         *
         * @param $this
         * @param $activeTariff
         * @param {number} value
         *
         * @private
         */
        _setLoanTerm: function ($this, $activeTariff, value) {
            $activeTariff.find('.jsCreditCalculatorLoanTermVal').text(value);
            $this.$('.jsCreditCalculatorSummary').find('.jsCreditCalculatorLoanTermVal').text(value);
        },

        /**
         * Set cost of loan.
         *
         * @param $this
         * @param $activeTariff
         * @param {number} value
         */
        _setCostOfLoan: function ($this, $activeTariff, value) {
            const valueFormated = $this._priceFormat(value),
                  $costOfLoan = $activeTariff.find('.jsCreditCalculatorCostOfLoan');
                  $costOfLoan.find('.simpleType-value').text(valueFormated);
            $this.$('.jsCreditCalculatorSummary').find('.jsCreditCalculatorCostOfLoan').text($costOfLoan.text());
        },

        /**
         * Set overpayment.
         *
         * @param $this
         * @param $activeTariff
         * @param {number} absoluteValue
         * @param {number} percentValue
         */
        _setOverPayment: function ($this, $activeTariff, absoluteValue, percentValue) {
            const absoluteValueFormated = $this._priceFormat(absoluteValue),
                  $absoluteValueSimpleType = $activeTariff.find('.jsCreditCalculatorOverPayment').find('.simpleType'),
                  $children = $absoluteValueSimpleType.children();

            $activeTariff.find('.jsCreditCalculatorOverPayment').find('.simpleType').text('')
            $children.each(function() {
                $absoluteValueSimpleType.append($(this)).append(' ');
            });
            $absoluteValueSimpleType.find('.simpleType-value').text(absoluteValueFormated);

            $activeTariff.find('.jsCreditCalculatorOverPaymentPercent').text(percentValue);
        },

        /**
         * Get input price
         *
         * @param   $this
         *
         * @returns {number}
         */
        _getPrice: function ($this) {
            const $customPriceInput = $this.$('.jsCreditCalculatorCustomPrice');
            let price = 0;
            if ($customPriceInput.length > 0) {
                price = $customPriceInput.val();
            } else {
                price = $this.$('.jsCreditCalculatorActiveTariff').data('price');
            }

            return price;
        },

        /**
         * Price format
         *
         * @param   price
         *
         * @returns {*|string}
         */
        _priceFormat: function (price) {
            const moneyConfig = window.Joomla.getOptions('moneyConfig') || {
                'decimal_sep': '.',
                'thousands_sep': ' ',
                'num_decimals': 0
            };

            return window.JBZoo.numFormat(price, moneyConfig.num_decimals, moneyConfig.decimal_sep, moneyConfig.thousands_sep);
        },

        /**
         * Calculate monthly payment.
         *
         * @param   {number} price
         * @param   {number} [rate=0.00]
         * @param   {number} [loanTerm=6]
         * @param   {number} [downPayment=0]
         *
         * @returns {number}
         */
        _calculateMonthlyPayment: function (price, rate, loanTerm, downPayment) {
            rate        = typeof rate === 'undefined' ? 0.00 : rate;
            loanTerm    = typeof loanTerm === 'undefined' ? 6 : loanTerm;
            downPayment = downPayment || 0;

            let monthlyPayment = 0;
            if (rate > 0) {
                monthlyPayment = (((price - downPayment) * rate) / 1200) / (1 - Math.pow((1 / (1 + (rate / 1200))), loanTerm));
            } else {
                monthlyPayment = (price - downPayment) / loanTerm;
            }

            return Math.ceil(monthlyPayment);
        },

        /**
         * Get checkout total price.
         *
         * @param   itemPrice
         * @param   monthlyPayment
         * @param   termVal
         * @param   rateVal
         *
         * @returns {number|*}
         */
        _getCheckoutTotalPrice: function (itemPrice, monthlyPayment, termVal, rateVal) {
            if (parseInt(rateVal) > 0) {
                return monthlyPayment * termVal;
            }

            return itemPrice;
        },

        /**
         * Get over payment.
         *
         * @param   {number} checkoutTotalPrice
         * @param   {number} itemPrice
         * @param   {number} discount
         *
         * @returns {number}
         */
        _getOverPayment: function (checkoutTotalPrice, loanAmount, discount) {
            if (parseFloat(discount) > 0) {
                loanAmount = loanAmount / (100 - parseFloat(discount)) * 100;
            }

            return checkoutTotalPrice - loanAmount;
        },

        /**
         * Get over payment percent.
         *
         * @param   {number} itemPrice
         * @param   {number} overPayment
         * @param   {number} discount
         *
         * @returns {number}
         */
        _getOverPaymentPercent: function (loanAmount, overPayment, discount) {
            if (parseFloat(discount) > 0) {
                loanAmount = loanAmount / (100 - discount) * 100;
            }
            if (loanAmount > 0) {
                return +(Math.round((overPayment / loanAmount) * 100  + "e+2") + "e-2");
            }

            return 0;
        },

        /**
         * Submit and recount credit items.
         *
         * @param e
         * @param $this
         */
        'click .jsCreditCalculatorCustomSubmit': function (e, $this) {
            const priceValue = $this.$('.jsCreditCalculatorCustomPrice').val();
            if (priceValue > 0) {
                $this._update($this);
                $(this).attr('disabled', 'disabled');
            }
        },

        /**
         * On input custom price.
         *
         * @param e
         * @param $this
         */
        'input .jsCreditCalculatorCustomPrice': function (e, $this) {
            const $inputEl = $(this),
                  val = parseInt($inputEl.val());
            if (!val || val < $inputEl.attr('min')) {
                $this.$('.jsCreditCalculatorCustomSubmit').attr('disabled', 'disabled');
            } else {
                $this.$('.jsCreditCalculatorCustomSubmit').removeAttr('disabled');
            }

            if (val > $inputEl.attr('max')) {
                $inputEl.val($inputEl.attr('max'));
            }
        },

        /**
         * Toggle credit item.
         *
         * @param e
         * @param $this
         */
        'mousedown .jsCreditCalculatorTariff': function (e, $this) {
            const $item = $(this);

            if (!$item.hasClass('jsCreditCalculatorActiveTariff')) {
                $item.addClass('uk-active jsCreditCalculatorActiveTariff').siblings().removeClass('uk-active jsCreditCalculatorActiveTariff');
                $this._update($this);
            }
        },

        /**
         * Toddle term value.
         *
         * @param e
         * @param $this
         */
        'input .jsDataTerm': function (e, $this) {
            $this._update($this);
        },

    });
});
