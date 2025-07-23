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

    JBZoo.widget('HyperPC.Admin', {
        'maxHeight' : 700
    }, {

        /**
         * Widget initialize.
         *
         * @param $this
         */
        init : function ($this) {
            $this._onEditLayout($this);
            $this._normalizeSideBar($this);

            $this.$('#submenu li a').each(function () {
                var href = $(this).attr('href');
                if (href === '#divider') {
                    $(this).closest('li').addClass('divider').html('<span></span>');
                }
            });
        },

        /**
         * Normalize Joomla right sidebar.
         *
         * @param $this
         * @private
         */
        _normalizeSideBar: function ($this) {
            var sidebar   = $this.$('#sidebar');
            var maxHeight = $this.getOption('maxHeight');

            if (sidebar.height() > maxHeight) {
                sidebar.css({
                    'height'     : maxHeight + 'px',
                    'overflow'   : 'hidden',
                    'overflow-y' : 'scroll'
                });
            }
        },

        /**
         * On Joomla! edit layout page.
         *
         * @param $this
         * @private
         */
        _onEditLayout: function ($this) {
            $this.$('.control-group #jform_title').focus();
            $this.$('.control-group #jform_name').focus();
        }
    });
});
