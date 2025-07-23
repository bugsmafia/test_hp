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

!function (t, n) {
    angular.module('widgetkit').run(), t(function () {
        t('.btn-toolbar').on('click', '[rel="widgetkit"]', function (e) {
            e.preventDefault(), e.stopPropagation();
            for (var o = t(this); o.length && !o.has('textarea').length;)o = o.parent();
            n.widgetkit.env.editor(o.find('textarea:first'))
        });
        var e = t('body.com_hyperpc .widgetkit-widget'), o = e.nextAll('input'), a = {
            value: function () {
                try {
                    return JSON.parse(o.val())
                } catch (t) {
                    return {}
                }
            }, update: function () {
                var t = this.value().name;
                e.text(t ? Translator.trans('Виджет: %widget%', {widget: t}) : Translator.trans('Выберите widget'))
            }
        };
        e.on('click', function (t) {
            t.preventDefault(), n.widgetkit.env.init('widget', a.value(), function (t) {
                o.val(JSON.stringify(t)), a.update()
            })
        }), a.update()
    })
}(jQuery, window);