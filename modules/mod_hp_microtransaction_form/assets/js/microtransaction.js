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

    JBZoo.widget('HyperPC.Microtransaction', {
        subjects: {},
        checkPaymentError: 'Failed to verify payment. Contact HYPERPC support and provide your order number.',
        purchaseFailMessage: 'The bank reported a problem with the payment. Try again later or contact our support.',
        successPurchaseMessage: 'Thank you for your purchase!',
        defaultState : '',
    }, {

        paths: [],
        currentPath: '',
        $selects: {},

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.paths = Object.keys($this.getOption('subjects'));

            if ($this.paths.length) {
                $this.currentPath = $this.paths.includes($this.getOption('defaultState')) ? $this.getOption('defaultState') : $this.paths[0];
            }

            $this.$selects = $this.$('.jsMicrotransactionFormSelect');

            $(document).one('hpuserloggedin', function(e) {
                const detail = e.originalEvent.detail || {};
                $this.$('[type="submit"]').removeAttr('disabled');
                $this.$('.jsMicrotransactionFormToken').children().attr('name', detail.token);
                $this.$('.jsMicrotransactionFormAuthAlert').attr('hidden', 'hidden');
            });

            $this._rebuildPath($this, $this.$selects.first().attr('name'));
        },

        /**
         * Update total value
         * 
         * @param $this 
         */
        _updateTotal: function ($this) {
            $this.$('.jsMicrotransactionFormTotal').html(
                $this.getOption('subjects')[$this.currentPath]
            );
        },

        /**
         * Rebuild current path
         * 
         * @param $this 
         * @param {string} changedProp 
         */
        _rebuildPath: function ($this, changedProp) {
            $this.currentPath = '';

            let isMeanProp = true;
            $this.$selects.each(function() {
                const $select = $(this),
                      name = $select.attr('name');

                if (isMeanProp) {
                    $this.currentPath += ($this.currentPath === '' ? '' : '/') + name + ':' + $select.val();
                    if (name === changedProp) {
                        isMeanProp = false;
                    }
                } else {
                    const availablePaths = $this.paths.filter(function($path) {
                        return ($path.includes($this.currentPath  + '/') || $path === $this.currentPath);
                    });

                    const $options = $select.find('option');
                    $options.each(function() {
                        const $option = $(this);

                        const search = availablePaths.filter(function(path) {
                            return path.split('/').includes(name + ':' + $option.attr('value'));
                        });

                        if (search.length === 0) {
                            $option.attr('disabled', 'disabled');
                        } else {
                            $option.removeAttr('disabled');
                        }
                    });

                    const $selectedOption = $options.filter(':selected');

                    if ($selectedOption.is(':disabled')) {
                        $options.not(':disabled').first().prop('selected', true);
                    }

                    $this.currentPath += '/' + name + ':' + $select.val();
                }
            });
        },

        /**
         * Get purchase key.
         *
         * @return  {string}
         */
        _getPurchaseKey: function ($this) {
            return $this.currentPath;
        },

        /**
         * Lock button during ajax request in progress.
         *
         * @param $button - jQuery object
         */
         _lockButton: function ($button) {
            $button.attr('disabled', 'disabled')
                   .prepend('<span uk-spinner="ratio: 0.67"></span>')
                   .find('[uk-icon]').attr('hidden', 'hidden')
        },

        /**
         * Unlock button after ajax request is complete.
         *
         * @param $button - jQuery object
         */
        _unlockButton: function ($button) {
            $button.removeAttr('disabled')
                   .find('[uk-icon]').removeAttr('hidden');
            $button.find('[uk-spinner]').remove();
        },

        /**
         * Success purchase handler
         * 
         * @param $this
         * @param {object} order 
         */
        _successPurchaseHandler: function ($this, order) {
            $.ajax({
                'url'       : '/index.php',
                'dataType'  : 'json',
                'type'      : 'POST',
                'data'      : {
                    'option' : 'com_hyperpc',
                    'task'   : 'microtransaction.check-payment',
                    'format' : 'json',
                    'order'  : order,
                    'module' : $this.$('[name="module-id"]').val()
                }
            })
            .done(function(response) {
                if (response.result) {
                    const message = response.message || $this.getOption('successPurchaseMessage');

                    $this.el
                        .html(
                            '<div class="uk-alert uk-alert-success uk-width-large uk-margin-auto uk-margin-large-top">' +
                                message +
                            '</div>'
                        );
                } else {
                    UIkit && UIkit.notification($this.getOption('checkPaymentError'), {status: 'danger', timeout: 0});
                }
            })
            .fail(function($xhr, error) {
                UIkit && UIkit.notification($this.getOption('checkPaymentError'), {status: 'danger', timeout: 0});
            });
        },

        /**
         * Failure purchase handler
         * 
         * @param $this
         * @param {object} order 
         */
        _failurePurchaseHandler: function ($this, order) {
            UIkit && UIkit.notification($this.getOption('purchaseFailMessage'), {status: 'warning', timeout: 0});
        },

        /**
         * On change select value
         * 
         * @param e 
         * @param $this 
         */
        'change .jsMicrotransactionFormSelect' : function (e, $this) {
            const $changedSelect = $(this);

            $this._rebuildPath($this, $changedSelect.attr('name'));
            $this._updateTotal($this);
        },

        /**
         * On form submit
         * 
         * @param e 
         * @param $this 
         */
        'submit form': function (e, $this) {
            e.preventDefault();

            const $form = $(this),
                  $button = $form.find('[type="submit"]');
            $this._lockButton($button);

            $.ajax({
                'url'       : '/index.php',
                'dataType'  : 'json',
                'type'      : 'POST',
                'data'      : {
                    'option' : 'com_hyperpc',
                    'task'   : 'microtransaction.create',
                    'format' : 'json',
                    'module' : $form.find('[name="module-id"]').val(),
                    'player' : $form.find('[name="player"]').val(),
                    'key'    : $this._getPurchaseKey($this)
                },
                'headers' : {
                    'X-CSRF-Token' : $form.find('.jsMicrotransactionFormToken').children().attr('name')
                }
            })
            .done(function(response) {
                if (response.result) {
                    window.ipayCheckout && window.ipayCheckout({
                            amount: response.price,
                            currency: 'RUB',
                            order_number: response.order,
                            description: response.description
                        },
                        function(order) {
                            $this._successPurchaseHandler($this, order)
                        },
                        function(order) {
                            $this._failurePurchaseHandler($this, order)
                        }
                    );
                } else {
                    UIkit && UIkit.notification(response.message, 'danger');
                }
            })
            .fail(function($xhr, error) {
                UIkit && UIkit.notification((error || 'Connection error'), 'danger');
            })
            .always(function () {
                $this._unlockButton($button);
            });
        }

    });
});