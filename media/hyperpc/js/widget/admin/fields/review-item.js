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
 *
 * @author     Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.FieldReviewItem', {
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {

        },

        /**
         * Remove basket field.
         *
         * @param e
         * @param $this
         */
        'click .jsChoseProduct' : function (e, $this) {
            e.preventDefault();

            let context = $this.$('#jform_context').val(),
                url     = null,
                view    = null;

            switch (context) {
                case "com_hyperpc.position":
                    view = 'positions';
                    break;
                default:
                    view = 'products';
                    break;
            }

            url = $this.$('.field-review-item').data('url') + '&view=' + view;

            if (url) {
                $.fancybox.open({
                    src     : url,
                    type    : 'iframe',
                    opts    : {
                        afterLoad : function () {
                            let iframe = $('.fancybox-iframe').contents();

                            iframe.find('.jsChooseItem').on('click', function (e) {
                                let id   = $(this).data('id'),
                                    name = $(this).data('name');

                                id = parseInt(id.toString().match(/\d+/));

                                $this.$('.field-review-item-input-name').val(name);
                                $this.$('#jform_item_id_id').val(id);

                                parent.jQuery.fancybox.getInstance().close();

                                e.preventDefault();
                            });
                        }
                    }
                });
            }
        }

    });

});
