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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.TradeinCalculator', {}, {

        $groups: null,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.$groups = $this.$('.jsTradeinGroup');

            $this.$groups.eq(0).removeAttr('hidden');

            $this._hidePrices($this);
        },

        /**
         * Remove parts prices from DOM.
         *
         * @param $this
         */
        _hidePrices: function ($this) {
            $this.$('.jsTradeinPart').each(function () {
                const price = $(this).data('price');
                $(this).removeAttr('data-price').data('price', price);
            });
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
         * On change group part.
         *
         * @param e
         * @param $this
         */
        'change .jsTradeinGroup input[type="radio"]': function (e, $this) {
            const $group = $(this).closest('.jsTradeinGroup'),
                  $nextGroup = $group.next();

            const $accordionTitle = $group.find('.uk-accordion-title'),
                  groupName = $accordionTitle.data('groupName');

            $accordionTitle.html('<span class="uk-text-small uk-text-muted">' + groupName + '</span>' + '<div>' + $(this).val() + '</div>');

            UIkit.accordion($this.$('[uk-accordion]')).toggle($group, true);

            $nextGroup.removeAttr('hidden');
            const $grid = $nextGroup.find('[uk-margin]');
            if ($grid.length > 0) {
                UIkit.margin($grid).$emit('update');
            }

            if ($this.$groups.find('input[type="radio"]:checked').length === $this.$groups.length) {
                $this.$('.jsTradeinCalculate').removeAttr('hidden');
                $this.$('.jsTradeinOffer').attr('hidden', 'hidden');
            }
        },

        /**
         * Click on calculate button.
         *
         * @param e
         * @param $this
         */
        'click .jsTradeinCalculate': function (e, $this) {
            let sum = 0;
            $this.$groups.find('input[type="radio"]:checked').each(function() {
                const $input = $(this),
                      $part = $input.closest('.jsTradeinPart'),
                      $group = $input.closest('.jsTradeinGroup');

                sum += $part.data('price');

                $this.$('.simpleForm2').find('[name="' + $group.data('key') + '"]').val($input.val());
            });

            sum = $this._priceFormat(sum);

            $(this).attr('hidden', 'hidden');
            $this.$('.jsTradeinOffer').removeAttr('hidden').find('.jsTradeinOfferPrice .simpleType-value').text(sum);
            $this.$('.simpleForm2').find('[name="offer"]').val(sum);
        },

        /**
         * On change filter.
         *
         * @param e
         * @param $this
         */
        'input .jsTradeinCalculatorFilter input': function (e, $this) {
            const $accordionContent = $(this).closest('.uk-accordion-content'),
                  $parts = $accordionContent.find('.jsTradeinPart'),
                  $footer = $accordionContent.find('.jsTradeinCalculatorFilterFooter');

            if ($(this).val() === '') {
                $parts.removeAttr('hidden');
                $footer.attr('hidden', 'hidden');
            } else {
                var value = $(this).val().toLowerCase();
                $parts.filter(':not([data-name*="' + value + '"])').attr('hidden', 'hidden');
                $parts.filter('[data-name*="' + value + '"]').removeAttr('hidden', 'hidden');

                if ($parts.filter('[hidden]').length > 0) {
                    $footer.removeAttr('hidden');
                } else {
                    $footer.attr('hidden', 'hidden');
                }
            }

            const $grid = $accordionContent.find('[uk-margin]');
            if ($grid.length > 0) {
                UIkit.margin($grid).$emit('update');
            }
        },

        /**
         * Clear filter.
         *
         * @param e
         * @param $this
         */
        'click .jsTradeinCalculatorFilterClear': function (e, $this) {
            $(this).closest('.uk-accordion-content').find('.jsTradeinCalculatorFilter input').val('').trigger('input');
        }

    });
});