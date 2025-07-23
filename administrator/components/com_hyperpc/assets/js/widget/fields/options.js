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

    JBZoo.widget('HyperPC.FieldOptions', {
        'formToken'          : '',
        'showArchiveMessage' : 'Show',
        'hideArchiveMessage' : 'Hide',
        'confirmMessage'     : 'Are you sure?'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$('.jsOptionRow').hide();
        },

        /**
         * Toggle part archive options.
         *
         * @param e
         * @param $this
         */
        'click .jsToggleArchiveOptions' : function (e, $this) {
            var button = $(this);

            $this.$('.jsOptionRow').each(function() {
                if (button.hasClass('jsNoActive')) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            if (button.hasClass('jsNoActive')) {
                button
                    .removeClass('jsNoActive')
                    .text($this.getOption('hideArchiveMessage'));
            } else {
                button
                    .addClass('jsNoActive')
                    .text($this.getOption('showArchiveMessage'));
            }
        },

        /**
         * Ajax remove option from part form.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveOption' : function (e, $this) {
            var el = $(this),
                id = el.data('id');

            $this.confirm($this.getOption('confirmMessage'), function () {
                $this._openLoader();
                $.ajax({
                    'url'       : 'index.php?tmpl=component',
                    'dataType'  : 'json',
                    'type'      : 'POST',
                    'headers'   : {
                        'X-CSRF-TOKEN' : $this.getOption('formToken')
                    },
                    'data'      : {
                        'option' : 'com_hyperpc',
                        'task'   : 'options.delete',
                        'cid'    : [id]
                    },
                    'success'   : function (data) {
                        $this._hideLoader();
                        if (data.result === true) {
                            el.closest('tr').slideUp(500, function () {
                                $(this).remove();
                            });
                        }
                    },
                    'error'     : function (error) {
                        alert('Error');
                    }
                });
            });

            e.preventDefault();
        }
    });
});
