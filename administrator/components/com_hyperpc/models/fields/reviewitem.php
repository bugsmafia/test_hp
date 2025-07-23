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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Joomla\Form\FormField;


/**
 * Class JFormFieldReviewItem
 *
 * @since       2.0
 */
class JFormFieldReviewItem extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'ReviewItem';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.review_item';

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
            ->js('js:widget/admin/fields/review-item.js')
            ->widget('#adminForm', 'HyperPC.FieldReviewItem', [
            ]);

        return parent::getInput();
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $formData   = $this->getForm()->getData();
        $layoutData = parent::getLayoutData();

        $layoutData['item_name'] = null;
        $layoutData['context']   = $formData->get('context');

        if ($this->value) {
            $productHelper = $this->hyper['helper']['moyskladProduct'];

            $product = $productHelper->findById($this->value);
            $layoutData['item_name'] = $product->getName();
        }

        return $layoutData;
    }
}
