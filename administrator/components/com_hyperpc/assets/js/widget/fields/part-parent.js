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

    JBZoo.widget('HyperPC.FieldPartParent', {

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

                        iframe.find('.jsIsRelated').remove();
                        iframe.find('.jsHasRelated').remove();

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

                                $this.ajax({
                                    'dataType' : 'json',
                                    'data'     : {
                                        'id'       : id,
                                        'parentId' : $this.getOption('viewItemId'),
                                        'task'     : 'part.add-related-part'
                                    },
                                    'success' : function (data) {
                                        if (data.result === false) {
                                            $this.alert(null, null, {
                                                'title' : 'Внимание!',
                                                'text'  : data.msg,
                                                'type'  : 'warning'
                                            });
                                        }

                                        $this.$('.jsPartSelect').remove();
                                    }
                                });

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
         * Toggle related list.
         *
         * @param e
         * @param $this
         */
        'change #jform_parent_id' : function (e, $this) {
            var value = $(this).val();
            if (value > 0) {
                $this.$('.jsRelatedList').slideUp();
            } else {
                $this.$('.jsRelatedList').slideDown();
            }
        },

        /**
         * Remove item from related.
         *
         * @param e
         * @param $this
         */
        'click .jsDeleteItem' : function (e, $this) {
            var element = $(this);
            var partId  = element.data('id');

            $this._openLoader();
            $this.ajax({
                'dataType' : 'json',
                'data'     : {
                    'id'       : partId,
                    'parentId' : 0,
                    'task'     : 'part.remove-related-part'
                },
                'success' : function (data) {
                    $this._hideLoader();
                    if (data.result === false) {
                        $this.alert(null, null, {
                            'title' : 'Внимание!',
                            'text'  : data.msg,
                            'type'  : 'warning'
                        });
                    } else {
                        element.closest('li').fadeOut(500, function () {
                            $(this).remove();
                        });
                    }
                }
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
                '</li>';
        }
    });
});
