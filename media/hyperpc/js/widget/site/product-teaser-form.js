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

    JBZoo.widget('HyperPC.ProductTeaserForm', {}, {

        /**
         * Set form data
         * 
         * @param $this
         * @param $form jQuery Object
         * @param data
         */
        _setFormData : function ($this, $form, data) {
            $this._clearFormData($form);

            if (typeof data === 'object') {
                const $inputs = $form.find('[name^="hp-item_"]');
                for (let prop in data) {
                    $inputs.filter('[name^="hp-item_' + prop + '"]').val(data[prop]);
                }
            }
        },

        /**
         * Clear form data
         * 
         * @param $form jQuery Object
         */
        _clearFormData : function ($form) {
            $form.find('[name^="hp-item_"]').val('');
        },

        /**
         * On button click
         * 
         * @param e
         * @param $this
         */
        'click {element}' : function (e, $this) {
            const data = $this.el.data('itemInfo'),
                  $modal = $(this.hash),
                  $form = $modal.find('form');

            $this._setFormData($this, $form, data);
        }

    });
});
