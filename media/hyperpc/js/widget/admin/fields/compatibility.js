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

    JBZoo.widget('HyperPC.FieldCompatibility', {
        fieldsContext: 'part'
    }, {

        /**
         * Add new part field.
         *
         * @param e
         * @param $this
         */
        'change .jsCompatibilityChangeGroup' : function (e, $this) {
            const $el   = $(this),
                  value = $(this).val();

            $this._openLoader();

            if (value) {
                $.ajax({
                    'url' : '/administrator/index.php',
                    'dataType' : 'json',
                    'data' : {
                        'format'        : 'html',
                        'group_id'      : value,
                        'fields_context': $this.getOption('fieldsContext'),
                        'tmpl'          : 'component',
                        'option'        : 'com_hyperpc',
                        'task'          : 'compatibility.ajax-load-group-fields'
                    },
                    'success' : function (data) {
                        $this._hideLoader();
                        if (data.result === 'success') {
                            const $wrapper = $el.closest('.hp-form-control');
                            if ($wrapper.length) {
                                $wrapper.find('.jsCompatibilityChooseField').removeClass('hide');
                                const $select = $wrapper.find('.jsCompatibilityChooseField').find('select');
                                $select.find('option').remove();
                                $select.append(data.output);
                                $select.trigger('liszt:updated');
                            }
                        }
                    },
                    'error' : function () {
                        $this._hideLoader();
                        alert('error!');
                    }
                })
            }
        }

    });

});
