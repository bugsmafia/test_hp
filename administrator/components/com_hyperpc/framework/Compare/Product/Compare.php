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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Compare\Product;

use Exception;
use HYPERPC\App;
use HYPERPC\Data\JSON;
use HYPERPC\Object\Compare\CategoryTree\CategoryTree;
use HYPERPC\Object\Compare\CategoryTree\CategoryProductsCollection;

abstract class Compare
{
    /**
     * Instance of HYPERPC application.
     *
     * @var App
     */
    public $hyper;

    protected $_categoryTreeData;

    protected array $_descriptionGroupIds = [];

    public function __construct()
    {
        $this->hyper = App::getInstance();
    }

    public function setCategoryTreeData($categoryTreeData)
    {
        $this->_categoryTreeData = $categoryTreeData;
    }

    public function setDescriptionGroupIds(array $descriptionGroupIds)
    {
        $this->_descriptionGroupIds = $descriptionGroupIds;
    }

    abstract public function getCategoriesTree(): CategoryTree;

    abstract public function getComparedProducts(): JSON;

    /**
     * Get array of category ids from tree data
     *
     * @return  int[]
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getCategoryIds()
    {
        if (!isset($this->_categoryTreeData)) {
            throw new Exception('CategoryTreeData not set');
        }

        $categoryIds = [];
        foreach ($this->_categoryTreeData as $rootCategories) {
            foreach ($rootCategories['child'] as $childCategoryId) {
                if (!in_array($childCategoryId, $categoryIds)) {
                    $categoryIds[] = $childCategoryId;
                }
            }
        }

        return $categoryIds;
    }

    /**
     * Sort products in collection
     *
     * @param   CategoryProductsCollection $productsCollection
     *
     * @return  CategoryProductsCollection
     *
     * @since   2.0
     */
    protected function _sortProducts(CategoryProductsCollection $productsCollection): CategoryProductsCollection
    {
        $products = $productsCollection->items();
        usort($products, function ($item1, $item2) {
            return (intval($item2->price) <=> intval($item1->price));
        });

        return new CategoryProductsCollection($products);
    }
}
