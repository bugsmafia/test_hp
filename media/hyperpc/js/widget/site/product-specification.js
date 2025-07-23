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
 * @author     Roman Evsyukov <roman_e@hyperpc.ru>
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.ProductSpecification', {},
    {

        /**
         * Show specification html.
         *
         * @param e
         * @param $this
         */
        'click .jsSpecificationButton' : function (e, $this) {
            e.preventDefault();

            $('.jsSpecificationModal').remove();

            const $el = $(this);

            let content = $el.data('specsHtml');
            if (!content) {
                content = '<div class="uk-text-center"><span uk-spinner></span></div>';

                const data = {
                    tmpl     : 'component',
                    option   : 'com_hyperpc',
                    task     : 'moysklad_product.get-specification-html',
                    item_key : $el.data('itemkey')
                }

                $.ajax({
                    'url'      : '/index.php',
                    'dataType' : 'json',
                    'method'   : 'POST',
                    'data'     : data
                })
                .done(function(response) {
                    let html = '';
                    if (response.result) {
                        html = response.html;
                        $el.data('specsHtml', html);
                    } else {
                        html = '<div class="uk-alert uk-alert-danger">' + response.message + '</div>';
                    }

                    $('.jsFullSpecs').html(html);
                })
                .fail(function(xjr, error) {
                    const msg = error.msg || 'Connection error';
                    UIkit.notification(msg, 'danger');
                    UIkit.modal('.jsSpecificationModal').hide();
                });
            }

            let modalTitle = '';
            if ($el.data('title')) {
                modalTitle = '<div class="uk-h2">' + $el.data('title') + '</div>';
            }

            UIkit.modal(
                $('<div class="jsSpecificationModal uk-modal-container uk-modal" uk-modal="stack: true">' +
                    '<div class="uk-modal-dialog uk-modal-body">' +
                        '<button class="uk-modal-close-default" type="button" uk-close></button>' +
                        '<div class="uk-margin-auto uk-container-small">' +
                            modalTitle +
                            '<div class="jsFullSpecs">' + content + '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>')
            ).show();
        }
    });
});
