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

    JBZoo.widget('HyperPC.FieldProduct', {}, {

        /**
         * Choose product.
         *
         * @param e
         * @param $this
         */
        'click .jsAddProduct' : function (e, $this) {
            $.fancybox.open({
                src     : $(this).attr('href'),
                type    : 'iframe',
                opts    : {
                    iframe : {
                        css : {
                            width : '1200px'
                        }
                    },
                    afterLoad : function () {
                        var iframe = $('.fancybox-iframe').contents();

                        iframe.find('.jsChooseItem').on('click', function (e) {
                            var id   = $(this).data('id'),
                                name = $(this).data('name');

                            $this.$('#jform_product_id').val(id);
                            $this.$('.jsProductName').val(name);

                            $.fancybox.close();

                            e.preventDefault();
                        });
                    }
                }
            });

            e.preventDefault();
        }
    });
});
