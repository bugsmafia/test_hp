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

    JBZoo.widget('HyperPC.SiteCompareButtons.CompareProducts', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.constructor.parent.init($this);

            document.addEventListener('hpcompareupdated', (e) => {
                $this._updateCompareButtons($this);
            });
        },

        /**
         * Collect request args
         *
         * @param $this
         * @param $el
         *
         * @returns {object}
         */
        _collectRequestArgs : function ($this, $el) {
            const args = $this.constructor.parent._collectRequestArgs($this, $el);

            args.updateGroup = $this._getGroupKeyFromCompareButton($el);

            return args;
        },

        /**
         * Show success compare message
         *
         * @param $this
         * @param $button
         * @param response
         */
        _showSuccessCompareMessage: function($this, $button, response) {
            const msg = response.msg || '',
                  icon = '<span class="uk-text-success uk-visible@s" uk-icon="icon: check; ratio: 1.5"></span>';

            const $openedOffcanvas = $this.$('[uk-offcanvas]').filter('.uk-open');
            if ($openedOffcanvas.length) {
                UIkit.util.once($openedOffcanvas, 'hidden', function() {
                    $this._showMessage($this, msg, icon);
                });
                UIkit.offcanvas($openedOffcanvas).hide();
            } else {
                $this._showMessage($this, msg, icon);
            }
        },

        /**
         * Show fail compare message
         *
         * @param $this
         * @param $button
         * @param response
         */
        _showFailCompareMessage: function($this, $button, response) {
            const msg = response.msg || '',
                  icon = '<span class="uk-text-warning uk-visible@s" uk-icon="icon: warning; ratio: 1.5"></span>';

            $this._showMessage($this, msg, icon);
        },

        /**
         * Lock compare button.
         *
         * @param $button
         */
         _lockButton : function ($button) {
            $button
                .addClass('uk-disabled')
                .css('opacity', '0.3')
                .append('<span uk-spinner class="uk-position-center"></span>');
        },

        /**
         * Unlock compare button.
         *
         * @param $button
         */
        _unlockButton : function ($button) {
            $button
                .removeClass('uk-disabled')
                .css('opacity', '')
                .find('[uk-spinner]').remove();
        },

    });
});
