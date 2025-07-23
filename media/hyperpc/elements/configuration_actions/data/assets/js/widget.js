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
 *
 * @author     Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

jQuery(function ($) {

    JBZoo.widget('HyperPC.SiteConfigurationActionsData', {}, {

        /**
         * Click submit button.
         *
         * @param e
         *
         * @param $this
         */
        'click .jsSendDataBtn' : function (e, $this) {
            $(this)
                .addClass('uk-position-relative')
                .attr('disabled', 'disabled')
                .prepend('<span uk-spinner="ratio: 0.7"></span>');

            $(this).closest('form').trigger('submit');
        }

    });
});
