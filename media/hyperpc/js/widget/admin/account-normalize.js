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
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.AccountNormalize', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init: function ($this) {
            const $button = $this.$('.jsNormalizeAccount').first(),
                  target = $button.data('target'),
                  modal = new bootstrap.Modal(target, {
                      keyboard: false
                  });

            $button.on('click', (e) => {
                e.preventDefault();
                modal.show();
            })

            $(target).one('shown.bs.modal', (e) => {
                $this._ajaxNormalizeAccountRequest($this, 0);
            });
        },

        /**
         * Ajax normalize account request.
         *
         * @param $this
         * @param page
         */
        _ajaxNormalizeAccountRequest: function ($this, last) {
            $.ajax({
                'type'      : 'POST',
                'dataType'  : 'json',
                'url'       : '/administrator/index.php',
                'data'      : {
                    'format'     : null,
                    'tmpl'       : 'component',
                    'option'     : 'com_hyperpc',
                    'task'       : 'users.ajax-normalize-account',
                    'last'       : last,
                }
            })
            .done(function (response) {
                let prevLast = response.last,
                    $infoWrapper = $this.$('.jsNormalizeAccountInfo');

                $this._setProgressData($this, $infoWrapper, response);

                if (response.stop === false) {
                    $this._ajaxNormalizeAccountRequest($this, prevLast);
                } else {
                    const modalSelector = $this.$('.jsNormalizeAccount').data('target');
                    setTimeout(function () {
                        $this.$(modalSelector).modal('hide');
                    }, 2000);
                }
            })
            .fail(function () {
                alert('Error index!');
            });
        },

        /**
         * Setup request data for info.
         *
         * @param $this
         * @param $infoSelector
         * @param response
         */
        _setProgressData: function ($this, $infoSelector, response) {
            $infoSelector.find('.jsProcessItem').text(response.current);

            $this.$('.progress .progress-bar').css('width', response.progress + '%');
            $this.$('.progress .jsProgressVal').text(response.progress + '%');

            const $deleted = $infoSelector.find('.jsDeletedItem');
            $infoSelector.find('.jsTotalItems').text(response.total + parseInt($deleted.text()));

            const newDeleted = parseInt($deleted.text()) + response.deleted;
            $deleted.text(newDeleted);
        }
    });
});
