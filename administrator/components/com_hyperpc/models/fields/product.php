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
 */

use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldProduct
 *
 * @since   2.0
 */
class JFormFieldProduct extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.product';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Product';

    /**
     * Method to get a control group with label and input.
     *
     * @param   array  $options  Options to be passed into the rendering of the field
     *
     * @return  string  A string containing the html for the control group
     *
     * @since   3.2
     */
    public function renderField($options = array())
    {
        $this->hyper['wa']->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/product.js')
            ->widget('.hp-field-product', 'HyperPC.FieldProduct', [
                'fieldId' => $this->id
            ]);

        return parent::renderField($options);
    }
}
