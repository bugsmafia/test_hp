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
 *
 * @author     Sergey Kalistratov   <kalistratov.s.m@gmail.ru>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteAccountEdit', {
        'token' : null,
        'lang'  : 'en-GB',
    }, {

        mergeAvailable : true,

        type        : null,
        userId      : null,
        codeId      : null,
        userValue   : null,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $(document)
                .on('click', '.jsMergeConfirmBack', function (e) {
                    const $button = $(this),
                          type = $button.data('type');

                    UIkit.modal('#hp-modal-edit-' + type).show();
                })
                .on('submit', '.jsMergeConfirmForm', function (e) {
                    e.preventDefault();
                    const $form = $(this);
                    $this._handleMergeConfirmSubmit($this, $form);
                });

            $('.jsEditSecondStep').find('[name^="pwd"]')
                .on('keydown', function (e) {
                    const $input = $(this),
                          key = e.originalEvent.key;

                    if (key === 'Backspace' || key === 'Delete') {
                        if ($input.val().length === 0) {
                            const index = $input.data('code');
                            if (index >= 1) {
                                $input.closest('.jsEditSecondStep').find('[data-code="' + (index - 1) + '"]').trigger('focus').val('');
                            }
                        }
                    }
                })
                .on('input', function (e) {
                    const $input = $(this),
                          val = $input.val();

                    if ($input.data('code') === 0 && e.originalEvent.data) {
                        const $form = $input.closest('.jsEditSecondStep'),
                            pasteCodeResult = $this._fillPwdInputs(e.originalEvent.data, $form);

                        if (pasteCodeResult) {
                            $form.trigger('submit');
                            return false;
                        }
                    }

                    if (val.length === 1) {
                        if (/^\d{1}$/.test(val)) {

                            const index = $input.data('code'),
                                  $form = $input.closest('.jsEditSecondStep'),
                                  inputsCount = $form.find('[name^="pwd"]').length;

                            if (index < inputsCount - 1) { // focus next input
                                $form.find('[data-code="' + (index + 1) + '"]').trigger('focus');
                            } else if (index === inputsCount - 1) { // submit if last
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
                        const $form = $input.closest('.jsEditSecondStep'),
                            pasteCodeResult = $this._fillPwdInputs(e.originalEvent.data, $form);
                        if (pasteCodeResult) {
                            e.preventDefault();
                            $form.trigger('submit');
                        }
                    }
                });
        },

        /**
         * Fill pwd inputs
         *
         * @param code
         * @param form
         *
         * @returns {boolean}
         */
        _fillPwdInputs: function (code, form) {
            if (typeof code === 'string' && /^\d{4}$/.test(code.trim())) {
                const digits = code.trim().split(''),
                      $pwdInputs = form.find('[name^="pwd"]');

                for (let i = 0; i < digits.length; i++) {
                    $pwdInputs.eq(i).val(digits[i]);
                }

                $pwdInputs.last().trigger('focus');
                return true;
            }

            return false;
        },

        /**
         * Lock form submit button.
         *
         * @param $form - jQuery object
         */
        _lockFormSubmitButton: function ($form) {
            $form.find('[type="submit"]')
                .prepend('<span uk-spinner="ratio: 0.7"></span>')
                .attr('disabled', 'disabled');
        },

        /**
         * Unlock form submit button.
         *
         * @param $form - jQuery object
         */
        _unlockFormSubmitButton: function ($form) {
            $form.find('[type="submit"]')
                .removeAttr('disabled')
                .find('[uk-spinner]').remove();
        },

        /**
         * Show second step modal.
         *
         * @param $this
         * @param {string} text
         */
        _showSecondStepModal: function ($this, text) {
            const $secondStepModal = $('.jsEditSecondStepModal');

            $secondStepModal
                .find('.jsBeforeFormText')
                .html(text);

            UIkit.modal($secondStepModal).show();

            setTimeout(function () {
                $secondStepModal.find('[name*="pwd[0]"]').trigger('focus');
            }, 500);
        },

        /**
         * Handle merge confirm form submit.
         *
         * @param $this
         * @param $form
         */
        _handleMergeConfirmSubmit: function ($this, $form) {
            const $value = $form.find('.jsMergeConfirmValue'),
                  type   = $value.attr('name'),
                  value  = $value.val();

            const data = {
                'option' : 'com_hyperpc',
                'task'   : 'user.ajax-edit-merge-confirm',
                'tmpl'   : 'component',
                'type'   : type,
                'value'  : value
            };

            const $recaptcha = $form.find('.g-recaptcha');
            if ($recaptcha.length > 0 && window.grecaptcha) {
                data['g-recaptcha-response'] = grecaptcha.getResponse($recaptcha.data('recaptcha-widget-id'));
            }

            $this._lockFormSubmitButton($form);
            $.ajax({
                'url'      : '/index.php',
                'dataType' : 'json',
                'method'   : 'POST',
                'data'     : data,
                'headers'  : {
                    'X-CSRF-Token' : $this.getOption('token')
                }
            })
            .done(function (response) {
                let $captchaWrapper = $('.jsMergeConfirmModal').find('.jsAuthCaptcha');

                if (!$captchaWrapper.children().is('.g-recaptcha')) {
                    $captchaWrapper.html($(response.captcha));
                }

                $this._checkCaptcha($this, $captchaWrapper);

                if (response.result) {
                    const user = atob(response.user).split('::');

                    $this.userId = user[0];
                    $this.codeId = user[1];

                    $this._showSecondStepModal($this, response.message);
                } else {
                    UIkit.notification(response.message, 'danger');
                }
            })
            .fail(function ($xhr) {
                const msg = $xhr.status ? $xhr.statusText : 'Connection error';
                UIkit.notification(msg, 'danger');
            })
            .always(function () {
                $this._unlockFormSubmitButton($form);
                $this._resetCaptcha($form);
            });
        },

        /**
         * Second step for change auth value.
         *
         * @param e
         * @param $this
         */
        'submit {document} .jsEditSecondStep': function (e, $this) {
            e.preventDefault();

            const data = {
                'option'    : 'com_hyperpc',
                'task'      : 'user.ajax-edit-user-check-value',
                'tmpl'      : 'component',
                'format'    : null,
                'user_id'   : $this.userId,
                'code_id'   : $this.codeId,
                'type'      : $this.type,
                'value'     : $this.userValue
            }

            const $form = $(this);

            $form.find('input').each(function () {
                const $input = $(this);
                if ($input.is('[type="checkbox"]')) {
                    data[$input.attr('name')] = $input.prop('checked');
                } else {
                    data[$input.attr('name')] = $input.val();
                }
            });

            $this._lockFormSubmitButton($form);

            $.ajax({
                'url'       : '/index.php',
                'method'    : 'POST',
                'data'      : data,
                'dataType'  : 'json',
                'timeout'   : 15000,
                'headers'   : {
                    'X-CSRF-Token' : $this.getOption('token')
                }
            })
            .done(function (response) {
                if (response.result) {
                    UIkit.notification(response.message, 'primary');

                    const $modal = $form.closest('.uk-modal');
                    UIkit.modal($modal).hide();
                    $modal.find('.jsBeforeFormText').html('');

                    const elementType = $this.type[0].toUpperCase() + $this.type.slice(1);
                    $this.$('.js' + elementType + 'Value').val($this.userValue)

                    if (response.phone) {
                        const $phoneInput  = $('#member-profile').find('input[type="tel"]');

                        $phoneInput.val(response.phone);
                        $phoneInput.parent().attr('data-mask', response.phone);
                    }
                } else {
                    UIkit.notification(response.message, 'danger');
                }
            })
            .fail(function ($xhr) {
                const msg = $xhr.status ? $xhr.statusText : 'Connection error';
                UIkit.notification(msg, 'danger');
            })
            .always(function () {
                $this._unlockFormSubmitButton($form)
            });
        },

        /**
         * First step for change auth value.
         *
         * @param e
         * @param $this
         */
        'submit {document} .jsEditFirstStep': function (e, $this) {
            e.preventDefault();

            const $form = $(this);

            if ($form.validate) {
                $form.valid();
                if ($form.validate().numberOfInvalids() != 0) {
                    return false;
                }
            }

            $this.type      = $form.data('element-type');
            $this.userValue = $form.find('.hpJsEditValue').val();

            const data = {
                'option'    : 'com_hyperpc',
                'task'      : 'user.ajax-edit-user-value',
                'tmpl'      : 'component',
                'type'      : $this.type,
                'value'     : $this.userValue
            };

            const $recaptcha = $form.find('.g-recaptcha');
            if ($recaptcha.length > 0 && window.grecaptcha) {
                data['g-recaptcha-response'] = grecaptcha.getResponse($recaptcha.data('recaptcha-widget-id'));
            }

            $this._lockFormSubmitButton($form);

            $.ajax({
                'url'      : '/index.php',
                'dataType' : 'json',
                'method'   : 'post',
                'data'     : data
            })
            .done(function (response) {
                let $captchaWrapper = $form.find('.jsAuthCaptcha');

                if (response.result) {
                    const user = atob(response.user).split('::');

                    $this.userId = user[0];
                    $this.codeId = user[1];

                    $this._showSecondStepModal($this, response.message);
                } else {
                    if ($this.mergeAvailable && response.notUnique) {
                        const modalHeading = $form.closest('.uk-modal').find('.uk-modal-title').text();
                        const $modalHtml =
                            $('<div class="jsMergeConfirmModal uk-modal">' +
                                '<div class="uk-modal-dialog uk-modal-body">' +
                                    '<button class="uk-modal-close-default uk-icon uk-close" type="button" uk-close></button>' +
                                    '<h2 class="uk-modal-title">' + modalHeading + '</h2>' +
                                    response.form +
                                '</div>' +
                            '</div>');
                        UIkit.modal($modalHtml).show();

                        $captchaWrapper = $('.jsMergeConfirmModal').find('.jsAuthCaptcha');

                        UIkit.util.once('.jsMergeConfirmModal', 'hidden', function (e) {
                            UIkit.modal(e.target).$destroy(true);
                        });
                    } else {
                        UIkit.notification(response.message, 'danger');
                    }
                }

                if (!$captchaWrapper.children().is('.g-recaptcha')) {
                    $captchaWrapper.html($(response.captcha));
                }

                $this._checkCaptcha($this, $captchaWrapper);
            })
            .fail(function ($xhr) {
                const msg = $xhr.status ? $xhr.statusText : 'Connection error';
                UIkit.notification(msg, 'danger');
            })
            .always(function () {
                $this._unlockFormSubmitButton($form);
                $this._resetCaptcha($form);
            });
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
         * Check captcha script
         *
         * @param $this
         * @param {jQuery Object} $form
         */
        _checkCaptcha: function ($this, $captchaWrapper) {
            if (window.grecaptcha) {
                const $captcha = $captchaWrapper.children();
                if (typeof $captcha.data('recaptchaWidgetId') === 'undefined' && $captcha.length > 0) {
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
    });
});
