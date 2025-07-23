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

    JBZoo.widget('HyperPC.CartModule', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            document.addEventListener('hpuserloggedin', (e) => {
                $this._onUserLoggedIn($this, e.detail || {});
            });
        },

        /**
         * On user logged in
         *
         * @param $this
         * @param data
         */
        _onUserLoggedIn : function ($this, data) {
            $this.$('.jsNavbarUserDropItemAuthed').removeAttr('hidden');
            $this.$('.jsNavbarUserDropItemGuest').attr('hidden', 'hidden');
            $this.$('.jsNavbarUserDropUsername').text(data.username);

            $this._setLogoutToken($this, data.token);
        },

        /**
         * Set token to the logout link
         *
         * @param $this
         * @param {string} token
         */
        _setLogoutToken : function ($this, token) {
            const $logoutLink = $this.$('.jsUserLogoutLink'),
                  logoutHref = $logoutLink.attr('href'),
                  newLogoutHref = logoutHref.replace(/^(.+&)([a-z0-9]{32})(=1.*)$/, '$1' + token + '$3');
            $this.$('.jsUserLogoutLink').attr('href', newLogoutHref);
        }

    });
});
