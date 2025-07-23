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
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SubscriptionModule', {
        'formToken' : ''
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$('.jsSubscriptionStepFirst').validate({
                errorElement : 'div',
                validClass   : 'tm-form-success',
                errorClass   : 'uk-form-danger hp-error-msg',
                rules: {
                    '.jsSubscriptionEmail' : {
                        required        : true,
                        client_email   : true
                    }
                },
                submitHandler : function (form) {
                    $(form).submit(function () {
                        return false;
                    });

                    UIkit.modal($(form).data('modal-href')).show();
                }
            });

            $('.jsSubscriptionStepSecond').validate({
                errorElement : 'div',
                validClass   : 'tm-form-success',
                errorClass   : 'uk-form-danger hp-error-msg',
                rules: {
                    'jform[username]' : {
                        required  : true,
                        minlength : 4
                    },
                    'jform[phone]' : {
                        required : true,
                        mobile   : true
                    }
                }
            });

            $this.$('.jsSubscriptionStepSecond .jsSubmitForm').on('click', function () {
                var formSubmit = $('#' + $(this).data('form'));
                if (formSubmit.length && formSubmit.valid()) {
                    formSubmit.submit(function () {
                        var form = $(this);

                        if ($(this).valid()) {
                            $(this).find('button[type=submit]').prepend(
                                '<div class="uk-spinner uk-icon uk-margin-right" uk-spinner="ratio: 0.667"></div>'
                            );

                            $.ajax({
                                'dataType' : 'json',
                                'data'     : {
                                    'rand'   : JBZoo.rand(100, 999),
                                    'option' : 'com_hyperpc'
                                },
                                'headers'  : {
                                    'X-Csrf-Token' : $this.getOption('formToken')
                                },
                                'url'      : '/index.php?' + $(this).serialize(),
                                'success'  : function (data) {
                                    form.find('button[type=submit] .uk-spinner').remove();
                                    if (data.result === false) {
                                        $('#hp-subscription-form form').prepend($this._UIkitAlert(data.message, 'danger'));

                                        setTimeout(function () {
                                            $('#hp-subscription-form form .uk-alert-danger').slideUp(function () {
                                                $(this).remove();
                                            });
                                        }, 3000);
                                    }

                                    if (data.result === true) {
                                        $('#hp-subscription-form form').prepend($this._UIkitAlert(data.message, 'success'));

                                        setTimeout(function () {
                                            $('#hp-subscription-form form .uk-alert-success').slideUp(function () {
                                                $(this).remove();
                                            });

                                            $('.jsSubscriptionStepFirst').trigger('reset');
                                            $('.jsSubscriptionStepSecond').trigger('reset');

                                            UIkit.modal('#hp-subscription-form').hide();
                                        }, 3000);
                                    }
                                },
                                'error' : function (data) {
                                }
                            });

                            return false;
                        }

                        return false;
                    });
                }
            });
        },

        /**
         * Change user email.
         *
         * @param e
         * @param $this
         */
        'change .jsSubscriptionEmail' : function (e, $this) {
            var val = $(this).val();
            if (val) {
                $('#jform_email').val(val);
            }
        },

        /**
         * Get Uikit alert html.
         *
         * @param   text
         * @param   type
         * @returns {string}
         * @private
         */
        _UIkitAlert : function (text, type) {
            if (!type) {
                type = 'warning';
            }

            return '<div class="uk-alert-' + type + '" uk-alert>' +
                    '<a class="uk-alert-close" uk-close></a>' +
                    text +
                '</div>';
        }
    });

});
