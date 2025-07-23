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
 * @author     Roman Evsyukov
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.AdminPositions', {}, {

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
        },

        /**
         * Ajax change position ordering.
         *
         * @param e
         * @param $this
         */
        'change .jsSorting' : function (e, $this) {
            const value      = $(this).val();
            const positionId = $(this).data('id');

            $this._openLoader();
            $this.ajax({
                'url'      : '/administrator/index.php?tmpl=component',
                'dataType' : 'json',
                'data'     : {
                    'ordering' : value,
                    'id'       : positionId,
                    'task'     : 'position.change-sorting',
                },
                'success' : function (data) {
                    $this._hideLoader();
                    if (data.result === false) {
                        $this.alert(null, null, {
                            'title' : 'Внимание!',
                            'text'  : 'Произошла ошибка сохранения сортировки.',
                            'type'  : 'warning'
                        });
                    }
                },
                'error'  : function (error) {
                    $this._hideLoader();
                    $this.alert(null, null, {
                        'title' : 'Ошибка!',
                        'text'  : error,
                        'type'  : 'error'
                    });
                }
            });
        }
    });
});
