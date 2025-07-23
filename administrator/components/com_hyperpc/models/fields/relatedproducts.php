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

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldRelatedProducts
 *
 * @since   2.0
 */
class JFormFieldRelatedProducts extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'RelatedProducts';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.relatedproducts';

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['wa']->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/related-products.js')
            ->widget('.jsRelatedProducts', 'HyperPC.FieldRelatedProducts', [
                'fieldName'    => $this->name,
                'viewItemId'   => $this->hyper['input']->get('id'),
                'removeTitle'  => Text::_('COM_HYPERPC_FIELD_RELATED_REMOVE_ITEM'),
                'deleteImgUrl' => $this->hyper['path']->url('img:icons/delete.png', false)
            ]);

        return parent::getInput();
    }
}
