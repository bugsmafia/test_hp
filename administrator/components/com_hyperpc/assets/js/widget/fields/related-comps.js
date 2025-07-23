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

    JBZoo.widget('HyperPC.FieldRelatedComps', {}, {

        /**
         * Initialize widget.
         *
         * @param $this
         */
        init : function ($this) {

        },

        /**
         * Clear image input.
         *
         * @param e
         * @param $this
         */
        'click .jsSendRelatedData' : function (e, $this) {
            let $fieldType = $(this).data('field-type');

            $this._openLoader();
            $this._sendRelatedData($this, $fieldType)
        },

        /**
         * Clear image input.
         *
         * @param e
         * @param $this
         */
        'click .jsSetByExample' : function (e, $this) {
            var link = $(this).data('src');
            $.fancybox.open({
                src     : link,
                type    : 'iframe',
                opts    : {
                    iframe : {
                        css : {
                            width : '1200px'
                        }
                    },
                    afterLoad : function () {
                        const iframe = $('.fancybox-iframe').contents();

                        iframe.find('.jsChooseItem').on('click', function (e) {
                            const id = $(this).data('id');
                            $('#related_comps input[type="checkbox"]:not(:disabled)').prop('checked', false);

                            if ($this.$('.item-' + id).length === 0) {
                                $this._getRelatedData($this, id);
                                $.fancybox.close();
                            }

                            e.preventDefault();
                        });
                    }
                }
            });
        },

        /**
         * Get relation data from example parts
         *
         * @param $this
         * @param id
         *
         * @private
         */
        _getRelatedData : function ($this, id) {
            $.ajax({
                'url'      : '/administrator/index.php?tmpl=component',
                'dataType' : 'json',
                'type'     : 'POST',
                'data'     : {
                    'tmpl'    : 'component',
                    'option'  : 'com_hyperpc',
                    'task'    : 'position.get-example-data',
                    'format'  : 'raw',
                    'id' : id,
                },
                'success' : function (response) {
                    $this._hideLoader();

                    if (!response.result) {
                        alert('Error!');
                    } else {
                        let valueIn   = response.data.parts,
                            valueMini = response.data.mini,
                            productId;

                        $('.jsProductRow').each(function () {
                            productId = $(this).data('product');

                            if (valueIn.includes(productId)) {
                                $(this).find('#product-' + productId + ':enabled').prop('checked', true);
                            }

                            if (valueMini.includes(productId)) {
                                $(this).find('#product-' + productId + '-mini:enabled').prop('checked', true);
                            }
                        });

                        let $fieldType = $(this).data('field-type');

                        $this._sendRelatedData($this, $fieldType)
                    }
                },
                'error'  : function (error) {
                    $this._hideLoader();
                    alert('Error!');
                }
            });
        },

        /**
         * Send relation data from part
         *
         * @param $this
         * @param $fieldType
         *
         * @private
         */
        _sendRelatedData : function ($this, $fieldType = null) {
            if ($fieldType === null) {
                $fieldType = 'moysklad_related_comps';
            }

            $.ajax({
                'url'      : '/administrator/index.php?tmpl=component',
                'dataType' : 'json',
                'type'     : 'POST',
                'data'     : {
                    'option' : 'com_hyperpc',
                    'task'   : 'field.callback',
                    'field'  : $fieldType,
                    'method' : 'save_configs',
                    'form'   : $('#item-form').serialize()
                },
                'success' : function (data) {
                    $this._hideLoader();
                    if (!data.result) {
                        alert('Error!');
                    }
                },
                'error'  : function (error) {
                    $this._hideLoader();
                    alert('Error!');
                }
            });
        }
    });
});
