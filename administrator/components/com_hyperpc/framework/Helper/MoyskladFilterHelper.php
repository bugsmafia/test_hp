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

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class MoyskladFilterHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladFilterHelper extends FilterHelper
{
    const PRODUCT_INDEX_FIELD   = 'index_fields_moysklad';
    const PRODUCT_FILTER_FIELD  = 'filter_product_allowed_moysklad';

    /**
     * Hold table name.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $tableName = HP_TABLE_MOYSKLAD_PRODUCTS_INDEX;

    /**
     * Get filter form action.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function formAction()
    {
        return 'index.php?option=' . HP_OPTION . '&task=moysklad_product.ajax-filter';
    }

    /**
     * Get count in stock products.
     *
     * @return  int
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCountInStockProducts()
    {
        return count($this->hyper['helper']['moyskladStock']->getProducts());
    }

    /**
     * Get all recount in stock products.
     *
     * @param   int  $offset
     * @param   int  $limit
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getAllRecountInStockProducts($offset = 0, $limit = 10)
    {
        return $this->hyper['helper']['moyskladStock']->getProducts();
    }

    /**
     * Get count products from all published categories.
     *
     * @return  int
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCountProductsFromCategories()
    {
        $db       = $this->hyper['db'];
        $products = $this->hyper['helper']['moyskladProduct']->findAll([
            'conditions' => [
                $db->qn('a.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED)
            ]
        ]);

        return count($products);
    }

    /**
     * Get all recount products in published categories.
     *
     * @param   int  $offset
     * @param   int  $limit
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getAllRecountProductsFromCatalog($offset = 0, $limit = 10)
    {
        $db       = $this->hyper['db'];
        $products = $this->hyper['helper']['moyskladProduct']->findAll([
            'conditions' => [
                $db->qn('a.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED)
            ],
            'offset' => $offset,
            'limit'  => $limit
        ]);

        return $products;
    }

    /**
     * Get part helper.
     *
     * @return  MoyskladPartHelper
     *
     * @since   2.0
     */
    protected function _getPartHelper()
    {
        return $this->hyper['helper']['moyskladPart'];
    }

    /**
     * Get option helper.
     *
     * @return  MoyskladVariantHelper
     *
     * @since   2.0
     */
    protected function _getOptionHelper()
    {
        return $this->hyper['helper']['moyskladVariant'];
    }

    /**
     * Get part options.
     *
     * @param   int
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getPartOptions($part_id)
    {
        return $this->hyper['helper']['moyskladVariant']->getPartVariants($part_id);
    }

    /**
     * Get index table instance.
     *
     * @return  bool|Table
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getIndexTable()
    {
        return Table::getInstance('Moysklad_Products_Index');
    }

    /**
     * Get parts context.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getContext()
    {
        return HP_OPTION . '.position';
    }

    /**
     * set in stock configuration id.
     *
     * @param ProductMarker $product
     * @param $rootIndexData
     *
     * @since   2.0
     */
    protected function _setInstock(ProductMarker $product, &$rootIndexData)
    {
        if ($product->saved_configuration) {
            $rootIndexData->set('in_stock', $product->saved_configuration);
        }
    }
}
