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

use JBZoo\Utils\Filter;
use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldBindPartComp
 *
 * @since   2.0
 */
class JFormFieldBindPartComp extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.bind_part_comp';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'BindPartComp';

    /**
     * Get current value.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getCurrentValue()
    {
        $return = [];
        $value  = (array) $this->value;

        if ($this->isSaveAndSetValue()) {
            foreach ($value as $val) {
                $val = (array) $val;
                if (isset($val[0])) {
                    $return[] = Filter::int($val[0]);
                }
            }

            return $return;
        }

        return $value;
    }

    /**
     * Check for set value in checkbox.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isSaveAndSetValue()
    {
        return ((string)$this->element['use-value'] !== '') ? Filter::bool($this->element['use-value']) : false;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $db = $this->hyper['db'];

        $categories = [];
        $products   = [];

        $list = $this->hyper['helper']['moyskladProduct']->findList(['a.*'], [
            $db->quoteName('a.state') . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
        ]);

        if (count($list)) {
            /** @var ProductMarker $product */
            foreach ($list as $product) {
                if (!array_key_exists($product->product_folder_id, $categories)) {
                    $category = $product->getFolder();
                    if ($category->id) {
                        $categories[$product->product_folder_id] = $category;
                    }
                }

                if (!isset($products[$product->product_folder_id][$product->id])) {
                    $products[$product->product_folder_id][$product->id] = $product;
                }
            }
        }

        return array_merge(parent::getLayoutData(), [
            'products'   => $products,
            'categories' => $categories
        ]);
    }
}
