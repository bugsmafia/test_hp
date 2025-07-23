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

    JBZoo.widget('HyperPC.FieldRelatedParts', {

    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {

        },

        /**
         * Add item to related list.
         *
         * @param e
         * @param $this
         */
        'click .jsAddItem' : function (e, $this) {
            var link = $(this).data('src');
            $.fancybox.open({
                src     : link,
                type    : 'iframe',
                opts    : {
                    iframe : {
                        css : {
                            width : '1200px'
                        }
                    },
                    afterLoad : function () {
                        var iframe = $('.fancybox-iframe').contents();

                        $this.$('.jsItemWrapper li').each(function () {
                            iframe
                                .find('.jsChooseItem[data-id=' + $(this).data('id') + ']')
                                .closest('tr')
                                .remove();
                        });

                        iframe
                            .find('.jsChooseItem[data-id=' + $this.getOption('viewItemId') + ']')
                            .closest('tr')
                            .remove();

                        iframe.find('.jsChooseItem').on('click', function (e) {
                            var id        = $(this).data('id');
                            var name      = $(this).data('name');
                            var fieldName = $this.getOption('fieldName') + '[' + id + ']';

                            $(this).closest('tr').fadeOut(500, function() {
                                $(this).remove();
                            });

                            if ($this.$('.item-' + id).length === 0) {
                                var output = $this._itemOutput($this, fieldName, id, name);
                                $this.$('.jsItemWrapper').append(output);
                            } else {
                                $this.alert(null, null, {
                                    'title' : 'Внимание!',
                                    'text'  : 'Комплектующая ' + name + ' уже добавлена в список',
                                    'type'  : 'warning'
                                });
                            }

                            e.preventDefault();
                        });
                    }
                }
            });
        },

        /**
         * Remove item from related.
         *
         * @param e
         * @param $this
         */
        'click .jsDeleteItem' : function (e, $this) {
            $(this).closest('li').fadeOut(500, function () {
                $(this).remove();
            });
            e.preventDefault();
        },


        /**
         * Blocked click link.
         *
         * @param e
         * @param $this
         */
        'click .li-link' : function (e, $this) {
            e.preventDefault();
        },

        /**
         * Item html output.
         *
         * @param $this
         * @param fieldName
         * @param id
         * @param name
         * @returns {string}
         * @private
         */
        _itemOutput : function ($this, fieldName, id, name) {
            var imageSrc   = $this.getOption('deleteImgUrl');
            var imageTitle = $this.getOption('removeTitle');
            return '<li class="list-group-item item-' + id + '" data-id="' + id + '">' +
                    '<a href="#" class="li-link">' +
                        name +
                        '<img data-id="' + id + '" title="' + imageTitle + '" ' +
                        'class="jb-image jsDeleteItem" src="' + imageSrc + '" />' +
                    '</a>' +
                    ' ' +
                    '<input type="hidden" name="' + fieldName +'" value="' + name + '">' +
                '</li>';
        }
    });
});
