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

    JBZoo.widget('HyperPC.CartModuleLoadConfiguration', {
        'ajaxErrorMessage' : 'Ajax loading error...'
    }, {

        input : null,
        submitButton: null,

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            $this.input = $this.$('[name="configuration_id"]');
            $this.submitButton = $this.$('[type="submit"]');
        },

        /**
         * Lock form during ajax request in progress.
         *
         * @param $this
         */
        _lock: function ($this) {
            $this.submitButton
                .attr('disabled', 'disabled')
                .prepend('<span uk-spinner="ratio: 0.67"></span>');
        },

        /**
         * Unlock form after ajax request is complete.
         *
         * @param $this
         */
        _unlock: function ($this) {
            $this.submitButton
                .removeAttr('disabled')
                .find('[uk-spinner]').remove();
        },

        /**
         * Handle AJAX error
         * 
         * @param $this
         * @param {string} message
         */
        _handleError: function ($this, message) {
            $('<div class="uk-form-danger">' +
                message +
              '</div>'
            ).insertAfter($this.input.addClass('uk-form-danger'));
        },

        /**
         * On form submit
         * 
         * @param e
         * @param $this
         */
        'submit {element}': function (e, $this) {
            e.preventDefault();

            $this.input
                .removeClass('uk-form-danger')
                .next('.uk-form-danger').remove();

            const configId = $this.input.val();

            $this._lock($this);

            $.ajax({
                'url'       : '/index.php',
                'dataType'  : 'json',
                'method'    : 'POST',
                'data'      : {
                    'option' : 'com_hyperpc',
                    'format' : 'raw',
                    'tmpl'   : 'component',
                    'task'   : 'configurator.check_configuration',
                    'configuration_id' : configId
                }
            })
            .done(function (data) {
                if (data.result === 'success') {
                    $this.input.addClass('uk-form-success');
                    window.location.href = $this.el.attr('action') + '&configuration_id=' + configId;
                    $(document).trigger('goToConfigurator');
                } else {
                    $this._handleError($this, data.msg);
                }
            })
            .fail(function (error) {
                $this._handleError($this, $this.getOption('ajaxErrorMessage'));
            })
            .always(function () {
                $this._unlock($this);
            });

        }
    });
});
