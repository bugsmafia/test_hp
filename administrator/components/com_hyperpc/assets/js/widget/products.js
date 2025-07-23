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

    JBZoo.widget('HyperPC.AdminProducts', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            var sidebar = $this.$('.j-sidebar-container #submenu');
            sidebar.prepend(
                '<li>' +
                    '<a href="index.php?option=com_hyperpc">Панель управления</a>' +
                '</li>' +
                '<li class="divider"></li>'
            );
        }
    });
});
