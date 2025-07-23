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

    JBZoo.widget('HyperPC.GameServers', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            const game = $this.el.data('game') || 'minecraft';

            $this._setValues($this, game);

            // repeat every 60sec
            const timerId = setInterval(() => $this._setValues($this, game), 60000);
            // stop after 30min
            setTimeout(() => { clearInterval(timerId); }, 1800000);
        },

        /**
         * Set values for servers online state
         *
         * @param $this
         * @param {string} game the game title
         */
        _setValues: function($this, game) {
            const $servers = $this.$('.jsServersOnlineServer');
            if ($servers.length) {
                $.ajax({
                    'url'       : '/index.php',
                    'dataType'  : 'json',
                    'type'      : 'POST',
                    'data'      : {
                        'option' : 'com_hyperpc',
                        'task'   : 'microtransaction.servers-online',
                        'format' : 'json',
                        'game'   : game
                    }
                })
                .done(function(response) {
                    if (typeof response === 'object' && Object.keys(response).length) {
                        $servers.each(function() {
                            const $server = $(this),
                                  serverName = $server.data('server'),
                                  value = typeof response[serverName] === 'undefined' ? 'N/A' : response[serverName];

                            $server.html(value);
                        });
                    } else {
                        $servers.html('N/A');
                    }
                })
                .fail(function($xhr, error) {
                    $servers.html('N/A');
                });
            } else {
                $servers.html('N/A');
            }
        }

    });
});
