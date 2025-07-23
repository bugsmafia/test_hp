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

    JBZoo.widget('HyperPC.ToolbarUpdateProductIndex', {
        'getFromInStock' : 'in_stock',
        'getFromCatalog' : 'catalog'
    }, {

        /**
         * Toggle button modal.
         *
         * @param e
         * @param $this
         */
        'click .jsToggleModalBtn' : function (e, $this) {
            e.preventDefault();

            let context = $(this).parents('.jsProductIndex').data('context');

            const modalTarget = $(this).data('bs-target');
            const myModal = new bootstrap.Modal(modalTarget, {
                keyboard: false
            })

            myModal.show();

            $(modalTarget)[0].addEventListener('shown.bs.modal', () => {
                $this._ajaxRecountIndex($this, 0, $this.getOption('getFromInStock'), 1);
            })
        },

        /**
         * Ajax recount index action.
         *
         * @param $this
         * @param page
         * @param getFrom
         * @param dropTable
         * @private
         */
        _ajaxRecountIndex : function ($this, page, getFrom, dropTable) {
            $.ajax({
                'type'      : 'POST',
                'dataType'  : 'json',
                'url'       : '/administrator/index.php',
                'data'      : {
                    'format'            : null,
                    'tmpl'              : 'component',
                    'option'            : 'com_hyperpc',
                    'task'              : 'positions.ajax-recount-index',
                    'page'              : page,
                    'get_from'          : getFrom,
                    'drop_table'        : dropTable
                }
            })
            .done(function(response) {
                if (response.stop === true && response.error) {
                    alert(response.error);
                    $this._cleanProgressData($this);
                    return;
                }

                let $inStockInfoWrapper = $this.$('.jsInStockProductsInfo');
                if (response.getFrom === $this.getOption('getFromCatalog')) {
                    $inStockInfoWrapper = $this.$('.jsCatalogProductsInfo');
                    if ($inStockInfoWrapper.hasClass('hidden')) {
                        $inStockInfoWrapper.removeClass('hidden');
                    }
                }

                $this._setProgressData($this, $inStockInfoWrapper, response);

                let responsePage = response.page;
                if (response.getFrom === $this.getOption('getFromInStock') && response.progress === 100) {
                    responsePage = 0;
                    getFrom      = $this.getOption('getFromCatalog');
                }

                if (response.stop === false) {
                    $this._ajaxRecountIndex($this, responsePage, getFrom, response.dropTable);
                }

                if (response.getFrom === $this.getOption('getFromCatalog') && response.stop === true) {
                    $this._cleanProgressData($this);
                }
            })
            .fail(function() {
                alert('Error index!');
            });
        },

        /**
         * Clean index progress data.
         *
         * @param $this
         * @private
         */
        _cleanProgressData : function ($this) {
            let modalSelector = $this.$('.jsToggleModalBtn').data('bs-target');

            setTimeout(function () {
                $this.$(modalSelector).modal('hide');
                $this.$('.jsProcessItem').text('');
                $this.$('.jsTotalItems').text('');
                $this.$('.progress .bar').css('width', 0);
            }, 2000);
        },

        /**
         * Setup request data for info.
         *
         * @param $this
         * @param $infoSelector
         * @param response
         * @private
         */
        _setProgressData : function ($this, $infoSelector, response) {
            $infoSelector.find('.jsProcessItem').text(response.current);
            $infoSelector.find('.jsTotalItems').text(response.total);
            $this.$('.progress .progress-bar').css('width', response.progress + '%');
            $this.$('.progress .jsProgressVal').text(response.progress);

            if (response.progress > 30) {
                $this.$('.progress .jsProgressTask').text($infoSelector.data('task-desc'));
            } else {
                $this.$('.progress .jsProgressTask').text('');
            }
        }
    });
});
