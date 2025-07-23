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
 * @author      Roman Evsykov
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Joomla\Fields\RelatedComps;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class JFormFieldMoyskladRelatedComps
 *
 * @since   2.0
 */
class JFormFieldMoyskladRelatedComps extends RelatedComps
{
    const CONTROL_TYPE_PARTS            = 'parts';
    const CONTROL_TYPE_MINI             = 'mini';
    const CONTROL_TYPE_DEFAULT          = 'default';
    const CONTROL_TYPE_DEFAULT_PART_OPT = 'default_options';

    const CONFIG_PARAM_PARTS            = 'parts';
    const CONFIG_PARAM_OPTIONS          = 'options';
    const CONFIG_PARAM_DEFAULT          = 'default';
    const CONFIG_PARAM_PARTS_MINI       = 'parts_mini';
    const CONFIG_PARAM_OPTIONS_MINI     = 'options_mini';
    const CONFIG_PARAM_PARTS_OPTIONS    = 'part_options';

    public string $fieldType = 'moysklad_related_comps';

    /**
     * Get group modal url helper
     *
     * @param   int $groupId
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getGroupModalUrl(int $groupId)
    {
        return $this->hyper['helper']['route']->url([
            'view'          => 'positions',
            'tmpl'          => 'component',
            'option'        => 'com_hyperpc',
            'layout'        => 'modal',
            'folder_id'     => $groupId,
            'show_options'  => 'false',
            'hide_elements' => 'false'
        ]);
    }

    /**
     * Get part helper
     *
     * @return  MoyskladPart
     *
     * @since   2.0
     */
    public function getPartHelper()
    {
        return $this->hyper['helper']['moyskladPart'];
    }

    /**
     * Get product list
     *
     * @param   array $notProcessedProducts
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getProductList($notProcessedProducts = [])
    {
        $db         = $this->hyper['db'];
        $conditions = [
            $db->quoteName('a.state') . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
        ];

        if (!empty($notProcessedProducts)) {
            $conditions[] = $db->quoteName('a.id') . ' NOT IN (' . $notProcessedProducts . ')';
        }

        $products = $this->hyper['helper']['moyskladProduct']->findAll(['conditions' => $conditions]);

        return $products;
    }

    /**
     * Get option
     *
     * @param   $formData
     *
     * @return  MoyskladVariant
     *
     * @since   2.0
     */
    public function getOption($formData)
    {
        return $this->hyper['helper']['moyskladVariant']->getById($formData->find('option_id'));
    }

    /**
     * Get part
     *
     * @param   $partId
     *
     * @return  MoyskladPart
     *
     * @since   2.0
     */
    public function getPart($partId)
    {
        $positionHelper = $this->hyper['helper']['position'];

        $position = $positionHelper->findById($partId);

        return $positionHelper->expandToSubtype($position);
    }

    /**
     * Check if variable is option
     *
     * @param   $option
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOption($option)
    {
        return $option instanceof MoyskladVariant;
    }

    /**
     * Update product
     *
     * @param   $product
     *
     * @return  mixed
     *
     * @since   2.0
     */
    public function updateProduct($product)
    {
        $totalPrice = $this->hyper['helper']['moyskladProduct']->countPrice($product);

        $db = $this->hyper['db'];
        $db->setQuery(
            $db->getQuery(true)
                ->update(
                    $db->qn(HP_TABLE_POSITIONS, 'positions')
                )
                ->join('LEFT', $db->qn(HP_TABLE_MOYSKLAD_PRODUCTS, 'products') . ' ON products.id = positions.id')
                ->where([
                    $db->quoteName('positions.id')  . ' = ' . $db->quote($product->id)
                ])
                ->set([
                    $db->quoteName('positions.list_price')           . ' = ' . $db->quote($totalPrice->val()),
                    $db->quoteName('products.configuration')   . ' = ' . $db->quote($product->configuration->write())
                ])
        );

        return $db->execute();
    }
}
