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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\Joomla\Form\FormField;
use Joomla\CMS\Form\Field\MediaField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHPDimensions
 *
 * @since   2.0
 */
class JFormFieldHPDimensions extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPDimensions';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.dimensions';

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $media    = new MediaField($this->form);
        $mediaVal = (array_key_exists('image', (array) $this->value)) ? $this->value['image'] : '';
        $element  = new SimpleXMLElement(
            '<field name="image" type="hpdimensions" label="COM_HYPERPC_FIELD_DIMENSIONS_WEIGHT_LABEL"
                     description="COM_HYPERPC_FIELD_DIMENSIONS_WEIGHT_DESC" />'
        );

        $media->setup($element, $mediaVal, $this->group . '.dimensions');

        return array_merge(
            parent::getLayoutData(),
            ['image_output' => $media->getInput()]
        );
    }
}
