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

    JBZoo.widget('HyperPC.FieldImage', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {

        },

        /**
         * Clear image input.
         *
         * @param e
         * @param $this
         */
        'click .jsImageClear' : function (e, $this) {
            $this.$('.jsImageInput').val('');
            e.preventDefault();
        },

        /**
         * Select image or folder.
         *
         * @param e
         * @param $this
         */
        'click .jsImageSelect' : function (e, $this) {
            var href = $(this).data('href');

            $.fancybox.open({
                src     : href,
                type    : 'iframe',
                opts    : {
                    afterLoad : function () {
                        const iframe = $('.fancybox-iframe').contents();
                        iframe.find('.jsChooseMedia').on('click', function () {
                            const path = $(this).data('path');
                            $this.$('.jsImageInput').val(path);
                        });
                    }
                }
            });

            e.preventDefault();
        }
    });
});
