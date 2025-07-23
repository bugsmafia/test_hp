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

    JBZoo.widget('HyperPC.FieldFps', {
        inputName: 'jform[params][fps]',
        typeCpuFactor: 'cpu-factor',
    }, {

        addedProcessors: new Set(),

        /**
         * Initialize widget.
         *
         * @param   $this
         */
        init: function ($this) {
            const $cpuFactorList = $this.$('#hp-fps-' + $this.getOption('typeCpuFactor')).find('[data-itemkey]');

            $cpuFactorList.each(function () {
                $this.addedProcessors.add(this.dataset.itemkey)
            });
        },

        /**
         * Add cpu row
         *
         * @param   $this
         * @param   {string} itemKey
         * @param   {string} name
         * @param   {string} tableSelector
         */
        _addCpu: function ($this, itemKey, name, tableSelector) {
            const html = `
                <tr data-itemkey="${itemKey}">
                    <td>
                        ${name}
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control" value="0"
                            name="${$this.getOption('inputName')}[${$this.getOption('typeCpuFactor')}][${itemKey}]">
                    </td>
                    <td>
                        <button type="button" class="jsRemoveCpuItem btn btn-sm btn-danger">
                            <span class="icon-minus" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>
            `;

            $this.$(tableSelector + ' tbody').append(html);
            $this.addedProcessors.add(itemKey);
        },

        /**
         * On click add cpu button
         *
         * @param   e
         * @param   $this
         */
        'click .jsAddCpuItem': function (e, $this) {
            e.preventDefault();

            const $button = $(this);

            $.fancybox.open({
                src: $button.data('src'),
                type: 'iframe',
                opts: {
                    iframe: {
                        css: {
                            width: '1200px'
                        }
                    },
                    afterLoad: (instance, current) => {
                        const $items = current.$iframe.contents().find('.jsChooseItem');

                        $items.filter(function () {
                            return $this.addedProcessors.has(this.dataset.id);
                        }).closest('tr').addClass(['pe-none', 'disabled']);

                        $items.on('click', function (e) {
                            e.preventDefault();

                            const $target = $(this),
                                  id = $target.data('id'),
                                  name = $target.data('name');

                            $target.closest('tr').addClass(['pe-none', 'disabled']);

                            $this._addCpu($this, id, name, $button.data('href'))
                        });
                    }
                }
            })
        },

        /**
         * On click remove cpu button
         *
         * @param   e
         * @param   $this
         */
        'click .jsRemoveCpuItem': function (e, $this) {
            if (confirm(Joomla.Text._('JGLOBAL_CONFIRM_DELETE'))) {
                const $row = $(this).closest('tr');

                $this.addedProcessors.delete($row.data('itemkey'));
                $row.remove();
            }
        }
    });
});
