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

    JBZoo.widget('HyperPC.SiteLoadWorkers', {
        'id'      : [],
        'limit'   : 3,
        'tpl'     : 'default',
        'wrapper' : 'list',
    }, {

        /**
         * @typedef {Object} WidgetParams
         * @property {array} id workers ids
         * @property {string} tpl card template
         * @property {string|number} limit limit of workers
         * @property {string} wrapper wrapper template
         */

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            /** @type {WidgetParams} */
            var widgetParams = {
                id      : $this.getOption('id'),
                tpl     : $this.getOption('tpl'),
                limit   : $this.getOption('limit'),
                wrapper : $this.getOption('wrapper')
            };

            if (widgetParams.id.length === 0) {
                var elDataParams = $this.el.data('params');
                if (typeof elDataParams === 'object') {
                    widgetParams.id      = elDataParams.id;
                    widgetParams.tpl     = elDataParams.tpl;
                    widgetParams.wrapper = elDataParams.wrapper;
                    if (elDataParams.hasOwnProperty('limit')) {
                        widgetParams.limit = elDataParams.limit;
                    }
                }
            }

            $this.el.html('<span class="uk-text-center" uk-spinner></span>');

            $this.ajax({
                'url' : '/index.php',
                'dataType' : 'json',
                'data' : {
                    'format'  : null,
                    'tmpl'    : 'component',
                    'id'      : widgetParams.id,
                    'tpl'     : widgetParams.tpl,
                    'limit'   : widgetParams.limit,
                    'wrapper' : widgetParams.wrapper,
                    'task'    : 'workers.ajax-get-snippet-list'
                },
                'success' : function (data) {
                    if (data.result) {
                        $this.el.after(data.html);
                        $this.el.remove();
                    } else {
                        $this.el.html('');
                        UIkit.notification('<span uk-icon="icon:warning"></span> Ajax loading error...', 'warning');
                    }
                },
                'error'   : function (data) {
                    $this.el.html('');
                    UIkit.notification('<span uk-icon="icon:warning"></span> Ajax loading error...', 'warning');
                }
            });

        }
    });
});
