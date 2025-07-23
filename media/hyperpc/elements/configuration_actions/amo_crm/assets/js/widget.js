/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    HYPERPC
 * @license    Proprietary
 * @link       https://github.com/HYPER-PC/HYPERPC".
 *{
    "username": "\u0421\u0435\u0440\u0433\u0435\u0439",
    "phone": "+7 (927) 223-69-75",
    "email": "kalistratov.s.m@gmail.com",
    "amo_kakaja_bol": "\u043a\u0430\u043a\u0430\u044f \u0431\u043e\u043b\u044c \u043a\u0430\u043a\u0430\u044f \u0442\u043e 4444",
    "amo_cel_pokupki": "\u0446\u0435\u043b\u044c \u043f\u043e\u043a\u0443\u043f\u043a\u0438 \u0432\u043e\u0442 \u043e\u043d\u0430 5555",
    "amo_sroshnost_pokupki": "151299",
    "amo_lead_id": 32143876
}
 * @author     Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteConfigurationActionsAmoCrm', {
        'goToAmoTitle' : 'Go to amo'
    }, {

        /**
         * Build pdf.
         *
         * @param e
         *
         * @param $this
         */
        'click .jsSendRequestForAmoCrm' : function (e, $this) {
            e.preventDefault();

            let $el       = $(this),
                actionUrl = $el.attr('href');

            $this._openLoader();

            $.ajax({
                'type'     : 'POST',
                'dataType' : 'json',
                'url'      : actionUrl,
                'data'     : {}
            })
            .done(function(response) {
                if (response.result) {
                    UIkit.notification(response.message, 'success');

                    $el
                        .attr('target', '_blank')
                        .attr('href', 'https://hyperpc.amocrm.ru/leads/detail/' + response.lead_id)
                        .removeClass('jsSendRequestForAmoCrm')
                        .text($this.getOption('goToAmoTitle'));

                    $el.prepend('<span class="uk-icon" uk-icon="link"></span>');

                    let configId = $el.closest('.hp-configurations-table-item').data('configuration-id');
                    $(document).trigger('configActionsAmoCrmOnSuccessCreate', configId);
                } else {
                    UIkit.notification(response.message, 'danger');
                    let configDataForm = $this.$('.jsConfigDataForm');
                    if (configDataForm.length > 0) {
                        setTimeout(function() {
                            configDataForm.trigger('click');
                        }, 2500);
                    }
                }
            })
            .fail(function() {
                UIkit.notification('Connection error', 'danger');
            })
            .always(function() {
                $this._hideLoader();
            });
        }

    });
});
