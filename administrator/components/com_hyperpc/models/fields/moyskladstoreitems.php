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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class JFormFieldStoreItems
 *
 * @since 2.0
 */
class JFormFieldMoyskladStoreItems extends FormField
{
    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'MoyskladStoreItems';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.moysklad.store_items';

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.5
     */
    protected function getLayoutData()
    {
        $stores  = $this->hyper['helper']['MoyskladStore']->getList();
        $context = ((string)$this->element['context'] !== '')   ? (string) $this->element['context'] : 'part';

        if ($context === 'product') {
            $renderItems = $this->getProductRenderItems();
        } else {
            $renderItems = $this->getPartsRenderItems();
        }

        return array_merge(parent::getLayoutData(), [
            'stores' => $stores,
            'items'  => $renderItems
        ]);
    }

    /**
     * Get moysklad store part items
     *
     * @return array
     *
     * @since  2.0
     */
    protected function getPartsRenderItems()
    {
        $db = $this->hyper['db'];

        $partId = $this->getForm()->getData()->get('part_id');
        if (!isset($partId)) {
            $partId = $this->getForm()->getData()->get('id');
        } else {
            $optionId = $this->getForm()->getData()->get('id');
        }

        $item = $this->hyper['helper']['moyskladPart']->findById($partId);

        $renderItems = [];

        if (isset($optionId)) {
            $options = $this->hyper['helper']['moyskladVariant']->findAll([
                'conditions' => [$db->qn('a.id') . ' = ' . $db->q($optionId)]
            ]);
        } else {
            $options = $item->getOptions();
        }

        if (count($options)) {
            /** @var MoyskladVariant $option */
            foreach ($options as $option) {
                $newItem = clone $item;
                $newItem->set('option', $option);
                $newItem->set('name', $option->name);

                $renderItems[] = $newItem;
            }

            return $renderItems;
        }

        return [$item];
    }

    /**
     * Get moysklad store product items
     *
     * @return array
     *
     * @since  2.0
     */
    protected function getProductRenderItems()
    {
        $productId = $this->getForm()->getData()->get('id');

        return [$this->hyper['helper']['MoyskladProduct']->findById($productId)];
    }
}
