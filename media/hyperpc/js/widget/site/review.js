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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteReview', {
        rating      : 0,
        preview_val : 5,
        item_id     : null,
        modal_id    : null,
        context     : 'com_hyperpc.product'
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            $this.$('#hp-review-total-vote').raty({
                starType    : 'i',
                readOnly    : true,
                score       : $this.getOption('rating')
            });
        },

        /**
         * Handle form ajax fail.
         *
         * @param $form - jQuery object
         */
        _handleFormAjaxFail : function($form) {
            $form.prepend(
                '<div class="uk-alert uk-alert-danger" uk-alert>' +
                    '<a class="uk-alert-close" uk-close></a>' +
                    'Ajax loading error...' +
                '</div>');
        },

        /**
         * Lock form submit button.
         *
         * @param $form - jQuery object
         */
        _lockFormSubmitButton : function ($form) {
            $form.find('[type="submit"]')
                 .prepend('<span uk-spinner="ratio: 0.7"></span>')
                 .attr('disabled', 'disabled');
        },

        /**
         * Unlock form submit button.
         *
         * @param $form - jQuery object
         */
        _unlockFormSubmitButton : function ($form) {
            $form.find('[type="submit"]')
                 .removeAttr('disabled')
                 .find('[uk-spinner]').remove();
        },

        /**
         * On click the leave review button
         *
         * @param e
         * @param $this
         */
        'click .jsLeaveReview' : function (e, $this) {
            const $button = $(this);
            if ($button.is('[uk-toggle], [data-uk-toggle]')) {
                return;
            }

            UIkit.modal('#login-form-modal').show();
            $(document).one('hpuserloggedin', function(e) {
                const detail = e.originalEvent.detail || {};
                const reviewModalId = $button.data('modalId');
                UIkit.modal(reviewModalId).show();
                $button.attr('uk-toggle', reviewModalId);
                $('.jsReviewForm').find('.jsFormToken').children().attr('name', detail.token);
            });
        },

        /**
         * Show more reviews.
         *
         * @param e
         * @param $this
         */
        'click .jsShowMoreReview' : function (e, $this) {
            const $button = $(this),
                  $buttonWrapper = $button.closest('tr'),
                  context = $button.data('context'),
                  itemId  = $button.data('itemid'),
                  limit   = $button.data('limit'),
                  start   = $button.data('start');

            $.ajax({
                'url'      : '/index.php',
                'type'     : 'POST',
                'dataType' : 'json',
                'data'     : {
                    'format'    : null,
                    'tmpl'      : 'component',
                    'option'    : 'com_hyperpc',
                    'task'      : 'review.load-reviews',
                    'id'        : itemId,
                    'context'   : context,
                    'limit'     : limit,
                    'start'     : start,
                }
            })
            .done(function (data) {
                data.html ? $buttonWrapper.before(data.html) : '';
                data.button ? $buttonWrapper.replaceWith(data.button) : $buttonWrapper.remove();

                $this.$('.jsRatingStars').raty({
                    starType : 'i',
                    readOnly : true
                });
            });
        },

        /**
         * On review form submit
         *
         * @param e
         * @param $this
         */
        'submit {document} .jsReviewForm' : function (e, $this) {
            e.preventDefault();

            const $form = $(this),
                  $alerts = $form.find('[uk-alert]');

            if ($alerts.length) {
                UIkit.alert($alerts).close();
            }

            $this._lockFormSubmitButton($form);

            $.ajax({
                'type'     : 'post',
                'dataType' : 'json',
                'url'      : '/index.php',
                'data'     : {
                    'format'    : null,
                    'tmpl'      : 'component',
                    'option'    : 'com_hyperpc',
                    'task'      : $form.data('task'),
                    'jform'     : {
                        'item_id'     : $form.find('#jform_item_id').val() || $this.getOption('item_id'),
                        'context'     : $form.find('#jform_context').val() || $this.getOption('context'),
                        'virtues'     : $form.find('#jform_virtues').val(),
                        'comment'     : $form.find('#jform_comment').val(),
                        'order_id'    : $form.find('#jform_order_id').val(),
                        'limitations' : $form.find('#jform_limitations').val(),
                        'rating'      : $form.find('#jform_rating_input').val(),
                        'token'       : $form.find('.jsFormToken').children().attr('name')
                    }
                }
            })
            .done(function(response) {
                const alertStyle = response.result ? 'uk-alert-success' : 'uk-alert-danger';

                $form.prepend(
                    '<div class="uk-alert ' + alertStyle + '" uk-alert>' +
                        '<a class="uk-alert-close" uk-close></a>' +
                        response.message +
                    '</div>');

                if (response.result) {
                    $form.find('[type="submit"]').remove();
                    setTimeout(function() {
                        UIkit.modal($form.closest('.uk-modal')).hide();
                        UIkit.alert($form.find('[uk-alert]')).close();
                    }, 5000);
                }
            })
            .fail(function() {
                $this._handleFormAjaxFail($form);
            })
            .always(function() {
                $this._unlockFormSubmitButton($form);
            });
        }
    });
});
