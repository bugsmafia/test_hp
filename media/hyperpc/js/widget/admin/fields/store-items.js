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

    JBZoo.widget('HyperPC.FieldStoreItems', {}, {

        'change .jsStoreItemBalance' : function (e, $this) {
            e.preventDefault();

            let balance = $(this),
                row     = balance.closest('td'),
                storeId = row.data('store-id'),
                context = $this.$('.jsStoreItemContext'),
                itemId  = row.find('.jsStoreItemId').val(),
                optionId = row.find('.jsStoreItemOptionId').val();

            $.ajax({
                'type'      : 'POST',
                'dataType'  : 'json',
                'url'       : '/administrator/index.php',
                'data'      : {
                    'format'    : null,
                    'tmpl'      : 'component',
                    'option'    : 'com_hyperpc',
                    'task'      : 'store_item.ajax-set-item',
                    'store_id'  : storeId,
                    'balance'   : balance.val(),
                    'context'   : context.val(),
                    'item_id'   : itemId,
                    'option_id' : optionId
                },
                'headers' : {
                    'X-CSRF-Token' : $('.jsSessionToken input').attr('name')
                }
            })
                .done(function(response) {

                })
                .fail(function() {
                    UIkit.notification('Connection error', 'danger');
                });


        }

    });

});
