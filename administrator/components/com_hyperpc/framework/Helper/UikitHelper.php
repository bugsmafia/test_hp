<?php
/**
 * HYPERPC - The shop of powerful computers.
 *
 * This file is part of the HYPERPC package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     HYPERPC
 * @license     Proprietary
 * @copyright   Proprietary https://hyperpc.ru/license
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use JBZoo\Utils\Filter;

/**
 * Class UikitHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class UikitHelper extends AppHelper
{

    /**
     * Get responsive class by cols.
     *
     * @param   int $cols
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getResponsiveClassByCols($cols = 1)
    {
        $cols = Filter::int($cols);
        $responsiveClass = 'uk-child-width-1-1 ';
        if ($cols === 2) {
            $responsiveClass .= 'uk-child-width-1-2@m';
        } elseif ($cols === 3) {
            $responsiveClass .= 'uk-child-width-1-2@s uk-child-width-1-3@l';
        } elseif ($cols === 4) {
            $responsiveClass .= 'uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@xl';
        } elseif ($cols === 5) {
            $responsiveClass .= 'uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l uk-child-width-1-5@xl';
        } elseif ($cols === 6) {
            $responsiveClass .= 'uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l uk-child-width-1-6@xl';
        }

        return $responsiveClass;
    }

    /**
     * Get product responsive class by cols.
     *
     * @param   int  $cols
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getProductsResponsiveClassByCols($cols = 1)
    {
        $cols = Filter::int($cols);
        $responsiveClass = 'uk-grid uk-child-width-1-1 ';
        if ($cols === 2) {
            $responsiveClass .= 'uk-child-width-1-2@m';
        } elseif ($cols === 3) {
            $responsiveClass .= 'uk-child-width-1-2@s uk-child-width-1-3@l';
        } elseif ($cols === 4) {
            $responsiveClass .= 'uk-child-width-1-2@s uk-child-width-1-4@l';
        } elseif ($cols === 5) {
            $responsiveClass .= 'uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l uk-child-width-1-5@xl';
        } elseif ($cols === 6) {
            $responsiveClass .= 'uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l uk-child-width-1-6@xl';
        }

        return $responsiveClass;
    }

    /**
     * Get products container class by cols.
     *
     * @param   int  $cols
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getProductsContainerClassByCols($cols = 1)
    {
        $cols = Filter::int($cols);
        $class = 'uk-container';
        if ($cols === 2) {
            $class .= ' uk-container-xsmall';
        } elseif ($cols >= 4) {
            $class .= ' uk-container-large';
        }

        return $class;
    }

    /**
     * Render uikit modal content.
     *
     * @param   string  $id
     * @param   null    $content
     *
     * @return  string
     *
     * @since   2.0
     */
    public function modal($id, $content = null)
    {
        return implode(PHP_EOL, [
            '<div id="' . $id . '" class="uk-modal uk-modal-container" uk-modal>',
                '<div class="uk-modal-dialog uk-modal-body">' .
                    '<button class="uk-modal-close-default" type="button" uk-close></button>' .
                    '<div>' . $content . '</div>'.
                '</div>' .
            '</div>'
        ]);
    }
}
