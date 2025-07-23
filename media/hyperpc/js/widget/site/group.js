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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.Group', {}, {

        /**
         * load archive items
         *
         * @param e
         * @param $this
         */
        'click .jsLoadArchive' : function (e, $this) {
            const $button = $(this);
            if ($button.data('loaded')) {
                return false;
            }

            $button.attr('disabled', 'disabled');

            const groupId = $button.data('groupId');
            $.ajax({
                'url'      : document.location.pathname,
                'dataType' : 'json',
                'method'   : 'POST',
                'data'     : {
                    'group'   : groupId,
                    'task'    : 'groups.load-archive'
                },
            })
            .done(function(response) {
                $button.data('loaded', true);
                if (response.result) {
                    const html = response.html;
                    $('.jsArchiveItems').html(html);
                } else {
                    $('.jsArchiveItems').html('').after(
                        '<div class="jsArchiveItems uk-container uk-container-small">' +
                            '<div class="uk-alert uk-alert-danger">' + response.message + '</div>' +
                        '</div>'
                    ).remove();
                }
            })
            .fail(function(xjr, error) {
                UIkit && UIkit.notification('Connection error', 'danger');
            })
            .always(function() {
                $button.removeAttr('disabled');
            });
        }

    });
});
