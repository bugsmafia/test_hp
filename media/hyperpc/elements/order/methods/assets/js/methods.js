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

    JBZoo.widget('HyperPC.ElementOrderMethods', {}, {

        /**
         * Enum for buyer type.
         *
         * @readonly
         * @enum {number}
         */
        buyerType: {
            INDIVIDUAL: 0,
            LEGAL: 1,
            ENTREPRENEUR: 2
        },

        companyType: 1,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            const $companyNameInput = $this.$('[name="jform[elements][company][name]"]'),
                  $companyValueInput = $this.$('[name="jform[elements][company][value]"]');

            window.addEventListener('pageshow', (event) => {
                $this._update($this);

                if ($companyNameInput.val() !== '' && $companyValueInput.val() === '') {
                    const innMatch = $companyNameInput.val().match(/^(\d{10,12})\,/);
                    innMatch && $companyValueInput.val((innMatch[1]));
                }
            });

            $.validator && $.validator.addMethod('company', function (value, element) {
                return this.optional(element) ||
                    /^(?:\d{10}|\d{12})/.test(
                        $companyValueInput.val()
                    );
            }, 'Выберите организацию из выпадающего списка');

            $companyNameInput
                .suggestions({
                    token: Joomla.getOptions('dadataToken', ''),
                    type: "PARTY",
                    params: {
                        status: ["ACTIVE"]
                    },
                    noCache: true,
                    onSelect: function (suggestion) {
                        const $input = $(this),
                              data = suggestion.data;

                        const value = data.inn + ', ' + data.name.short_with_opf;
                        $input.val(value);
                        $this._setInnValue($this, data.inn);
                        $input.valid();

                        switch (data.type) {
                            case 'INDIVIDUAL':
                                $this.companyType = $this.buyerType.ENTREPRENEUR;
                                break;
                            default:
                                $this.companyType = $this.buyerType.LEGAL;
                                break;
                        }

                        $this._setMethod($this, $this.companyType);
                    },
                    onSuggestionsFetch: function (suggestions) {
                        let inn = '';
                        if (suggestions.length === 1) {
                            inn = suggestions[0].data.inn;
                        }
                        $this._setInnValue($this, inn);

                        const $input = $(this);
                        if ($input.is('[aria-invalid]')) {
                            $input.valid();
                        }

                        return suggestions;
                    }
                })
                .one('input', function (e) {
                    $(this).rules('add', 'company');
                });
        },

        /**
         * Set inn value.
         *
         * @param $this
         */
        _setInnValue: function ($this, value) {
            $this.$('[name="jform[elements][company][value]"]').val(value);
        },

        /**
         * Update state.
         *
         * @param $this
         */
        _update: function ($this) {
            const val = parseInt($this.$('[name="jform[elements][methods][value]"]').filter(':checked').val()),
                  $switch = $this.$('.jsMethodsSwitch'),
                  $payments = $('.jsPaymentElement');
            if (val === $this.buyerType.LEGAL || val === $this.buyerType.ENTREPRENEUR) {
                $switch.prop('checked', true);
                $this._enableCompanyFields($this);
                $payments.find('[name="jform[elements][payments][value]"]')
                    .filter('[value="beznal"]')
                    .prop('checked', true)
                    .closest('.jsPaymentElement')
                    .css('display', '')
                    .siblings()
                    .css('display', 'none');
            } else {
                $switch.prop('checked', false);
                $this._disableCompanyFields($this);
                $payments.find('[name="jform[elements][payments][value]"]')
                    .filter('[value="beznal"]')
                    .closest('.jsPaymentElement')
                    .css('display', 'none')
                    .siblings()
                    .css('display', '')
                    .not('[hidden]')
                    .eq(0)
                    .find('[name="jform[elements][payments][value]"]')
                    .prop('checked', true);
            }
        },

        /**
         * Set physical person method.
         * 
         * @param $this
         */
        _setPhysicalPersonMethod: function ($this) {
            $this._setMethod($this, $this.buyerType.INDIVIDUAL);
        },

        /**
         * Set juridical person method.
         * 
         * @param $this 
         */
        _setJuridicalPersonMethod: function ($this) {
            $this._setMethod($this, $this.companyType);
        },

        /**
         * Disable company fields.
         *
         * @param $this 
         */
        _disableCompanyFields: function ($this) {
            $this.$('.jsOrderCompanyName')
                .attr('hidden', 'hidden')
                .find('input')
                .removeAttr('required');
            $this.$('[name^="jform[elements][company]"]').attr('disabled', 'disabled');
        },

        /**
         * Enable company fields.
         *
         * @param $this 
         */
        _enableCompanyFields: function ($this) {
            $this.$('.jsOrderCompanyName')
                .removeAttr('hidden')
                .find('input')
                .attr('required', 'required');
            $this.$('[name^="jform[elements][company]"]').removeAttr('disabled');
        },

        /**
         * Set method.
         *
         * @param $this
         * @param {number} id method id
         */
        _setMethod: function ($this, id) {
            $this.$('[name="jform[elements][methods][value]"]')
                .filter('[value="' + id + '"]')
                .prop('checked', true)
                .trigger('change');
        },

        /**
         * On change order method.
         *
         * @param e
         * @param $this
         */
        'change [name="jform[elements][methods][value]"]': function (e, $this) {
            $this._update($this);
        },

        /**
         * On change toggle switch.
         *
         * @param e
         * @param $this
         */
        'change .jsMethodsSwitch': function (e, $this) {
            if (this.checked) {
                $this._setJuridicalPersonMethod($this);
            } else {
                $this._setPhysicalPersonMethod($this);
            }
        }
    });
});
