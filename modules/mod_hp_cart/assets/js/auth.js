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

    JBZoo.widget('HyperPC.CartModuleAuth', {
        lang: 'en-GB',
        profileUrl: '/account',
    }, {

        step: 1,
        authMethod: null,

        isManualInitiated: false,
        resendDelay: 0,

        $authWrapper: null,
        $firstStep: null,
        $secondStep: null,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.$authWrapper = $('.jsAuthWrapper');
            $this.$firstStep = $this.$authWrapper.find('.jsAuthFirstStep');
            $this.$secondStep = $this.$authWrapper.find('.jsAuthSecondStep');

            $this.$firstStep.find('form').on('submit', function (e) {
                $this._onFirstStepSubmit(e, $this);
            });

            $this.$secondStep.on('submit', function (e) {
                $this._onSecondStepSubmit(e, $this);
            });

            $this.$secondStep.find('.jsAuthResendButton').on('click', function (e) {
                $this._onResendClick($this);
            });

            $this.$secondStep.find('.jsAuthGoBack').on('click', function (e) {
                e.preventDefault();
                $this._goToStepOne($this);
            });

            UIkit.util.on('#login-form-modal', 'hidden', function (e) {
                if ($(e.target).is('#login-form-modal')) {
                    $this.isManualInitiated = false;
                }
            });

            $this.$secondStep.find('[name^="pwd"]')
                .on('keydown', function (e) {
                    const $input = $(this),
                          key = e.originalEvent.key;
                    if (key === 'Backspace' || key === 'Delete') {
                        if ($input.val().length === 0) {
                            const index = $input.data('code');
                            if (index >= 1) {
                                $input.closest('form').find('[data-code="' + (index - 1) + '"]').trigger('focus').val('');
                            }
                        }
                    }
                })
                .on('input', function (e) {
                    const $input = $(this),
                          $form = $input.closest('form');

                    if ($input.data('code') === 0 && e.originalEvent.data) {
                        pasteCodeResult = $this._fillPwdInputs(e.originalEvent.data, $form);
                        if (pasteCodeResult) {
                            $form.trigger('submit');
                            return false;
                        }
                    }

                    const val = $input.val();
                    if (val.length === 1) {
                        if (/^\d{1}$/.test(val)) {
                            const index = $input.data('code'),
                                  $inputs = $form.find('[name^="pwd"]');

                            if (index < $inputs.length - 1) { // focus next input
                                $inputs.eq(index + 1).trigger('focus');
                            } else if (index === $inputs.length - 1) {
                                $form.trigger('submit');
                            }
                        } else {
                            $input.val('');
                        }
                    }
                })
                .on('beforeinput', function (e) {
                    const $input = $(this);
                    if ($input.data('code') === 0) {
                        const $form = $input.closest('form'),
                              pasteCodeResult = $this._fillPwdInputs(e.originalEvent.data, $form);
                        if (pasteCodeResult) {
                            e.preventDefault();
                            $form.trigger('submit');
                        }
                    }
                });
        },

        /**
         * On resend code button click
         *
         * @param $this
         */
        _onResendClick: function ($this) {
            if ($this.$firstStep.find('.g-recaptcha').length) {
                $this._goToStepOne($this);
                return;
            }

            const $blockedButtons = $this.$secondStep.find('.jsAuthResendButton, .jsAuthGoBack, [type="submit"]')
                .attr('disabled', 'disabled');

            const $form = $this.$firstStep
                .find('[name="jform[type]"]').filter('[value="' + $this.authMethod + '"]')
                .closest('form');

            // clear code inputs and errors
            $this.$secondStep.find('[name^="pwd"]').val('');
            $this.$secondStep.find('.jsAuthFormError').html('').css('display', 'none');

            // submit first step form
            $this._sendFirstStepForm($this, $form)
                .done(function (data) {
                    if (data.result) {
                        UIkit.notification({
                            message: data.message,
                            status: 'primary',
                            pos: 'top-right',
                            timeout: 4000
                        });
                    } else {
                        $this._goToStepOne($this);
                    }
                })
                .fail(function ($xhr, textStatus, errorThrown) {
                    $this._goToStepOne($this);
                })
                .always(function () {
                    $blockedButtons.removeAttr('disabled')
                });
        },

        /**
         * Fill pwd inputs
         *
         * @param code
         *
         * @returns {boolean}
         */
        _fillPwdInputs: function (code, $form) {
            const $pwdInputs = $form.find('[name^="pwd"]'),
                  re = new RegExp('^\\d{' + $pwdInputs.length + '}$');
            if (typeof code === 'string' && re.test(code.trim())) {
                const digits = code.trim().split('');

                for (let i = 0; i < digits.length; i++) {
                    $pwdInputs.eq(i).val(digits[i]);
                }

                $pwdInputs.last().trigger('focus');
                return true;
            }

            return false;
        },

        /**
         * Load google recaptcha script
         *
         * @param $this
         * @param {?HTMLElement} insertBefore
         */
        _loadGoogleRecaptchaScript: function ($this, insertBefore) {
            if (!(insertBefore instanceof HTMLElement)) {
                insertBefore = document.getElementsByTagName('script')[0];
            }

            const googleRecaptchaScript = document.createElement('script');
            googleRecaptchaScript.src = 'https://www.google.com/recaptcha/api.js?onload=JoomlainitReCaptcha2&render=explicit&hl=' + $this.getOption('lang');
            insertBefore.parentNode.insertBefore(googleRecaptchaScript, insertBefore);
        },

        /**
         * Reset captcha
         *
         * @param {jQuery Object} $form 
         */
        _resetCaptcha: function ($form) {
            if (window.grecaptcha) {
                const $captcha = $form.find('.jsAuthCaptcha').find('.g-recaptcha');
                if ($captcha.length) {
                    grecaptcha.reset($captcha.data('recaptchaWidgetId'));
                }
            }
        },

        /**
         * Lock form submit button.
         *
         * @param $form - jQuery object
         */
        _lockFormSubmitButton: function ($form) {
            $form.find('[type="submit"]').attr('disabled', 'disabled');
        },

        /**
         * Unlock form submit button.
         *
         * @param $form - jQuery object
         */
        _unlockFormSubmitButton: function ($form) {
            $form.find('[type="submit"]').removeAttr('disabled');
        },

        /**
         * Handle form ajax fail.
         *
         * @param $form - jQuery object
         */
        _handleFormAjaxFail: function ($form, message) {
            $form
                .find('.jsAuthFormError')
                .html(message || 'Ajax loading error...')
                .removeAttr('style');
        },

        /**
         * Set time remaining to resend an otp
         *
         * @param {object} $el - jQuery object
         * @param {Date} date 
         */
        _setResendTimerValue: function ($el, date) {
            $el.html(date.toISOString().substring(14, 19));
        },

        /**
         * Push form input values to passed object
         *
         * @param {object} $form jQuery object
         * @param {object} dataObj
         */
        _collectInputsToObject: function ($form, dataObj) {
            $form.find('input').each(function () {
                const $input = $(this);
                dataObj[$input.attr('name')] = $input.val();
            });
        },

        /**
         * Get Uid from cookie
         *
         * @returns {string}
         */
        _getUid: function () {
            /** @see https://developer.mozilla.org/ru/docs/Web/API/Document/cookie */
            return document.cookie.replace(/(?:(?:^|.*;\s*)hp_uid\s*\=\s*([^;]*).*$)|^.*$/, "$1"); 
        },

        /**
         * Send code request
         *
         * @param {object} $this
         * @param {JQuery} $form
         *
         * @returns {JQuery.jqXHR}
         */
        _sendFirstStepForm: function ($this, $form) {
            const data = {
                'option'    : 'com_hyperpc',
                'task'      : 'auth.step-one',
                'tmpl'      : 'component',
                'format'    : null,
            };

            $this._collectInputsToObject($form, data);

            const $recaptcha = $form.find('.g-recaptcha');
            if ($recaptcha.length > 0 && window.grecaptcha) {
                data['jform[g-recaptcha-response]'] = grecaptcha.getResponse($recaptcha.data('recaptcha-widget-id'));
            }

            const $firstStepSubmitButtons = $this.$firstStep.find('[type="submit"]');
            $firstStepSubmitButtons.attr('disabled', 'disabled');

            const $submitButton = $form.find('[type="submit"]');
            $submitButton.prepend('<span uk-spinner="ratio: 0.7"></span>');

            const xhr = $.ajax({
                'method'    : 'POST',
                'data'      : data,
                'dataType'  : 'json',
                'timeout'   : 15000,
            })
            .done(function (data) {
                if (data.result) {
                    const user = window.atob(data.user).split('::');

                    $this.userId = user[0];
                    $this.codeId = user[1];

                    $this.newUserRegistered = data.new;

                    // add session token
                    $this.$secondStep.find('.jsAuthToken').remove()
                    $this.$secondStep.append('<input class="jsAuthToken" type="hidden" name="' + data.token + '" value="1">')

                    $this.resendDelay = data.resendDelay || 60; /** @todo send in response */

                    const date = new Date($this.resendDelay * 1000),
                          $resendMessage = $this.$authWrapper.find('.jsAuthResendDelayMessage'),
                          $resendTimer = $resendMessage.find('.jsAuthResendDelayTime'),
                          $resendButtonWrapper = $this.$secondStep.find('.jsAuthResendButton').parent();

                    $firstStepSubmitButtons.parent().addClass('uk-width-auto@s');
                    $resendMessage.removeAttr('hidden');
                    $this._setResendTimerValue($resendTimer, date);

                    $resendButtonWrapper.attr('hidden', 'hidden');

                    const timer = setInterval(function () {
                        if (--$this.resendDelay > 0) {
                            date.setTime($this.resendDelay * 1000);
                            $this._setResendTimerValue($resendTimer, date);
                        } else {
                            clearInterval(timer);
                            $resendMessage.attr('hidden', 'hidden');
                            $resendButtonWrapper.removeAttr('hidden');
                            $firstStepSubmitButtons.removeAttr('disabled').parent().removeClass('uk-width-auto@s');
                        }
                    }, 1000);
                } else {
                    if (data.captcha) {
                        const $captchaWrapper = $form.find('.jsAuthCaptcha');

                        if (!$captchaWrapper.children().is('.g-recaptcha')) {
                            $captchaWrapper.html($(data.captcha));
                        }

                        if (window.grecaptcha) {
                            const $captcha = $captchaWrapper.children();
                            if (typeof $captcha.data('recaptchaWidgetId') === 'undefined') {
                                const captcha = $captcha.get(0);
                                const widgetId = grecaptcha.render(captcha, captcha.dataset);
                                $captcha.data('recaptchaWidgetId', widgetId);
                            }
                        } else {
                            const $plgRecapthaScript = $('script[src*="media/plg_captcha_recaptcha/js/recaptcha.min.js"]');
                            if ($plgRecapthaScript.length) {
                                $this._loadGoogleRecaptchaScript($this, $plgRecapthaScript.get(0));
                            } else {
                                const firstScriptTag = document.getElementsByTagName('script')[0],
                                      plgRecapthaScript = document.createElement('script');

                                plgRecapthaScript.src = '/media/plg_captcha_recaptcha/js/recaptcha.min.js';
                                firstScriptTag.parentNode.insertBefore(plgRecapthaScript, firstScriptTag);

                                plgRecapthaScript.addEventListener('load', function (e) {
                                    $this._loadGoogleRecaptchaScript($this, firstScriptTag);
                                });
                            }
                        }
                    }

                    $this._handleFormAjaxFail($form, data.message);
                    $firstStepSubmitButtons.removeAttr('disabled');
                }
            })
            .fail(function ($xhr, textStatus, errorThrown) {
                $this._handleFormAjaxFail($form, $xhr.status ? $xhr.statusText : null);
                $firstStepSubmitButtons.removeAttr('disabled');
            })
            .always(function () {
                $submitButton.find('[uk-spinner]').remove();
                $this._resetCaptcha($form);
            });

            return xhr;
        },

        /**
         * On first step form submit.
         *
         * @param e
         * @param $this
         */
        _onFirstStepSubmit: function (e, $this) {
            e.preventDefault();

            const $form = $(e.target);

            if ($form.validate) {
                $form.valid();
                if ($form.validate().numberOfInvalids() != 0) {
                    return;
                }
            }

            $this._sendFirstStepForm($this, $form)
                .done(function (data) {
                    if (data.result) {
                        $this.$secondStep.find('.jsAuthBeforeFormText').html(data.message);
                        $this.$secondStep.find('[name^="pwd"]').val('');
                        $this.$secondStep.find('.jsAuthFormError').html('').css('display', 'none');
                        $this._goToStepTwo($this);
                    }
                });
        },

        /**
         * On second step form submit.
         *
         * @param e
         * @param $this
         */
        _onSecondStepSubmit: function (e, $this) {
            e.preventDefault();

            const $form = $(e.target);

            $form.find('.jsAuthFormError').html('').css('display', 'none');

            const data = {
                'option'    : 'com_hyperpc',
                'task'      : 'auth.step-two',
                'tmpl'      : 'component',
                'format'    : null,
                'user_id'   : $this.userId || null,
                'code_id'   : $this.codeId || null,
            };

            $this._collectInputsToObject($form, data);

            const $submitButton = $form.find('[type="submit"]');
            $submitButton
                .attr('disabled', 'disabled')
                .prepend('<span uk-spinner="ratio: 0.7"></span>');

            const uid = $this._getUid();

            $.ajax({
                'method'    : 'POST',
                'data'      : data,
                'dataType'  : 'json',
                'timeout'   : 15000,
            })
            .done(function (response) {
                if (response.result) {
                    window.user = {
                        id: response.user.id,
                        name: response.user.name,
                        email: response.user.email,
                        emailHash: response.user.emailHash
                    };

                    document.dispatchEvent(new CustomEvent('hpuserloggedin', {
                        detail: response.user
                    }));

                    // GTM track registration
                    if ($this.newUserRegistered) {
                        window.dataLayer && window.dataLayer.push({
                            'event'    : 'hpTrackedAction',
                            'hpAction' : 'userRegistered'
                        });
                    }

                    const newUid = $this._getUid();
                    if (newUid !== uid) {
                        window.dataLayer && window.dataLayer.push({'event' : 'hpUidChanged'});
                    }

                    if ($this.isManualInitiated || $form.closest('.uk-modal').length === 0) {
                        location.href = $this.getOption('profileUrl');
                    } else {
                        UIkit.notification({
                            message: response.message,
                            status: 'primary',
                            pos: 'top-right',
                            timeout: 4000
                        });
                        UIkit.modal('#login-form-modal').hide();
                    }
                } else {
                    $this._handleFormAjaxFail($form, response.message);
                }
            })
            .fail(function ($xhr, textStatus, errorThrown) {
                $this._handleFormAjaxFail($form, $xhr.status ? $xhr.statusText : null);
            })
            .always(function () {
                $submitButton
                    .removeAttr('disabled')
                    .find('[uk-spinner]').remove();
            });
        },

        /**
         * Go to step one.
         *
         * @param $this
         */
        _goToStepOne: function ($this) {
            $this.$authWrapper.closest('.uk-modal-dialog').removeClass('tm-modal-dialog-small');
            $this.$firstStep.removeAttr('hidden');
            $this.$secondStep.attr('hidden', 'hidden');

            $this.step = 1;
        },

        /**
         * Go to step two.
         *
         * @param $this
         */
        _goToStepTwo: function ($this) {
            $this.$authWrapper.closest('.uk-modal-dialog').addClass('tm-modal-dialog-small');
            $this.$firstStep.attr('hidden', 'hidden');
            $this.$secondStep
                .removeAttr('hidden')
                .find('[name*="pwd[0]"]').trigger('focus');

            $this.step = 2;
        },

        /**
         * On open login modal by click.
         *
         * @param e
         * @param $this
         */
        'click .jsLoginModalToggle': function (e, $this) {
            $this.isManualInitiated = true;
        }

    });
});
