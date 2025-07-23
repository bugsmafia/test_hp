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
jQuery(function ($) {

    JBZoo.widget('HyperPC.AdminFieldElementsPosition', {
    }, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {
            //  Initialize sortable position.
            $this.$('.jsElementEditList').sortable({
                handle      : '.jsFieldSort',
                placeholder : 'ui-state-highlight',
                connectWith : '.' + $this.getOption('contentWidth')
            });
        },

        /**
         * Add new element.
         *
         * @param e
         * @param $this
         */
        'click .jsAddNewElement' : function (e, $this) {
            var type    = $(this).data('type'),
                isCore  = $(this).data('core'),
                control = $(this).data('control'),
                group   = $(this).closest('.hp-element-list').data('group');

            $.ajax({
                'url' : '/administrator/index.php',
                'dataType' : 'json',
                'data' : {
                    'type'    : type,
                    'group'   : group,
                    'control' : control,
                    'tmpl'    : 'component',
                    'option'  : 'com_hyperpc',
                    'task'    : 'elements.add'
                },
                'success' : function (data) {
                    var element = $this.$('.jsElementEditList li[data-element=' + type + ']');
                    if (isCore && element.length) {
                        alert('Можно добавить только один элемент ядра');
                        return;
                    }

                    $this.$('.jsEditElements .jsElementEditList').append(data.element);

                    $this._initTemplate();
                }
            })
        },

        /**
         * Remove element.
         *
         * @param e
         * @param $this
         */
        'click .jsRemoveField' : function (e, $this) {
            var element = $(this);
            if (confirm('Вы уверены?')) {
                element.closest('li').slideUp(500, function () {
                    $(this).remove();
                });
            }

            e.preventDefault();
        },

        /**
         * Initialize Joomla admin template.
         *
         * @param event
         * @param container
         * @private
         */
        _initTemplate : function (event, container) {
            var $container = $(container || document);

            //  Create tooltips.
            $container.find('*[rel=tooltip]').tooltip();

            //  Turn radios into btn-group.
            $container.find('.radio.btn-group label').addClass('btn');

            //  Handle disabled, prevent clicks on the container, and add disabled style to each button.
            $container.find('fieldset.btn-group:disabled').each(function() {
                $(this).css('pointer-events', 'none').off('click').find('.btn').addClass('disabled');
            });

            //  Setup coloring for buttons.
            $container.find('.btn-group input:checked').each(function() {
                var $input   = $(this);
                var $label   = $('label[for=' + $input.attr('id') + ']');
                var btnClass = 'primary';

                if ($input.val() != '') {
                    var reversed = $input.parent().hasClass('btn-group-reversed');
                    btnClass = ($input.val() == 0 ? !reversed : reversed) ? 'danger' : 'success';
                }

                $label.addClass('active btn-' + btnClass);
            });
        }
    });
});
