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
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HyperPcModelProduct_Folder;
use HYPERPC\Helper\CompareHelper;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Helper\ConfiguratorHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Object\Compare\CategoryTree\CategoryData;
use HYPERPC\Object\Compare\CategoryTree\CategoryTree;
use HYPERPC\Object\Compare\CategoryTree\RootCategoryData;
use HYPERPC\Object\Compare\CategoryTree\CategoryProductData;
use HYPERPC\Object\Compare\CategoryTree\CategoriesCollection;
use HYPERPC\Object\Compare\CategoryTree\CategoryProductsCollection;

class MoyskladCompare extends Compare
{
    /**
     * Get categories tree for offcanvas products list
     *
     * @return  CategoryTree
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getCategoriesTree(): CategoryTree
    {
        if (!isset($this->_categoryTreeData)) {
            throw new Exception('CategoryTreeData not set');
        }

        $list = new CategoryTree();
        $_categories = new CategoriesCollection();

        $categoryIds = $this->_getCategoryIds();
        if (empty($categoryIds)) {
            return $list;
        }

        $categories = $this->hyper['helper']['productFolder']->findById($categoryIds);

        $db = $this->hyper['db'];
        $conditions = [
            $db->qn('a.product_folder_id') . ' IN (' . implode(', ', $categoryIds) . ')',
            $db->qn('a.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED)
        ];

        $products = $this->hyper['helper']['moyskladProduct']->findAll([
            'conditions' => [$conditions]
        ]);

        $stockProducts = $this->hyper['helper']['moyskladStock']->getProducts([], $categoryIds);

        $descriptionGroups = $this->hyper['helper']['productFolder']->findById($this->_descriptionGroupIds, []);

        /** @var MoyskladProduct $product */
        foreach (array_merge($products, $stockProducts) as $product) {
            $_product = new CategoryProductData([
                'id'          => $product->id,
                'type'        => CompareHelper::TYPE_POSITION,
                'name'        => $product->name,
                'price'       => $product->getSalePrice()->text(),
                'image'       => $this->hyper['helper']['cart']->getItemImage($product, 0, 85),
                'itemKey'     => $product->getItemKey(),
                'isInCompare' => $this->hyper['helper']['compare']->isInCompare($product->id, CompareHelper::TYPE_POSITION),
                'description' => $this->hyper['helper']['moyskladProduct']->getMiniDescription($product, $descriptionGroups, false, ' / '),
            ]);

            if ($product->saved_configuration) {
                $_product->optionId = $product->saved_configuration;
            }

            if (!$_categories->offsetExists($product->getFolderId())) {
                $_categories->offsetSet($product->getFolderId(), new CategoryProductsCollection());
            }

            $_categories->offsetGet($product->getFolderId())->offsetSet(null, $_product);
        }

        foreach ($this->_categoryTreeData as $rootData) {
            if (!count($rootData['child'])) {
                continue;
            }

            $categoriesCollection = new CategoriesCollection();

            $data = new RootCategoryData([
                'name' => $rootData['root'],
                'categories' => $categoriesCollection
            ]);

            foreach ($rootData['child'] as $childCategoryId) {
                $minCategoryPrice = $this->hyper['helper']['productFolder']->getMinCategoryPrice($childCategoryId);

                if (!$_categories->offsetExists($childCategoryId) || !$minCategoryPrice) {
                    continue;
                }

                $categoryDate = new CategoryData([
                    'name'  => $categories[$childCategoryId]->title,
                    'image' => '',
                    'price' => Text::sprintf('COM_HYPERPC_STARTS_FROM', $minCategoryPrice->text()),
                    'products' => $this->_sortProducts($_categories->offsetGet($childCategoryId))
                ]);

                $categoriesCollection->offsetSet($childCategoryId, $categoryDate);
            }

            if (empty($categoriesCollection)) {
                continue;
            }

            $list->offsetSet(null, $data);
        }

        return $list;
    }

    /**
     * Get compare products.
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getComparedProducts(): JSON
    {
        /** @var CompareHelper */
        $compareHelper = $this->hyper['helper']['compare'];

        $items = $compareHelper->getItemList();
        $positions = $items[CompareHelper::TYPE_POSITION];

        $products = [];
        foreach ($positions as $folderId => $folderPositions) {
            foreach ($folderPositions as $sessionId => $position) {
                if ($position instanceof MoyskladProduct) {
                    $products[$sessionId] = $position;
                }
            }
        }

        $return = new JSON([
            'items' => [],
            'properties' => [],
            'hasEqualRow' => false
        ]);

        if (empty($products)) {
            return $return;
        }

        $propValues = [];
        $properties = [];

        $imageHeight = $this->hyper['params']->get('product_img_teaser_height', HP_PART_IMAGE_THUMB_WIDTH);

        $db = $this->hyper['db'];

        $conditions = [
            /** @todo exclude certain part groups if necessary */
            'NOT ' . $db->quoteName('a.alias') . ' = ' . $db->quote('root'),
        ];
        $folders = $this->hyper['helper']['productFolder']->findList(['a.*'], $conditions, 'a.lft ASC', 'id');

        /** @var HyperPcModelProduct_Folder $model */
        $model = ModelList::getInstance('Product_folder');
        $rootCategory = (int) $this->hyper['params']->get('configurator_root_category', 1);
        $folderTree = $model->buildTree($folders, $rootCategory); // will it work if root category id is 1?

        foreach ($products as $sessionId => $product) {
            $partsFromConfig = (bool) $product->saved_configuration;
            $loadUnavailable = $product->isFromStock();
            $productParts = $product->getConfigParts(
                true,
                $this->hyper['params']->get('moysklad_product_teaser_parts_order', 'a.product_folder_id ASC'),
                true,
                $partsFromConfig,
                $loadUnavailable
            );

            $render = $product->getRender();
            $render->setEntity($product);

            $renderItemData = [
                'type'  => CompareHelper::TYPE_POSITION,
                'image' => $this->hyper['helper']['cart']->getItemImage($product, 0, $imageHeight),
                'name'  => $product->getName(),
                'url'   => $product->getViewUrl(),
                'buy'   => [
                    'price'   => $product->getListPrice(),
                    'buttons' => $render->getCartBtn()
                ],
                'availability' => $product->getAvailability()
            ];

            $items = (array) $return->get('items');
            $items[$sessionId] = new JSON($renderItemData);

            /** @var ConfiguratorHelper $configuratorHelper */
            $configuratorHelper = $this->hyper['helper']['configurator'];

            foreach ($folderTree as $folder) {
                if (!$configuratorHelper->hasPartsInTree($folder, $productParts)) {
                    continue;
                }

                $treeIds = $configuratorHelper->getGroupTreeIds($folder);

                $properties[$folder->id] = new JSON([
                    'values'  => [],
                    'show'    => true,
                    'name'    => $folder->title,
                    'type'    => 'hpseparator',
                    'label'   => $folder->title,
                    'isEqual' => true
                ]);

                foreach ($productParts as $folderId => $folderParts) {
                    if (in_array($folderId, $treeIds) &&
                        array_key_exists($folders[$folderId]->parent_id, $folders)
                    ) {
                        if ($folders[$folderId]->level == 3) {
                            $parentGroup = $folders[$folderId];
                        } elseif ($folders[$folderId]->level == 4) {
                            $parentGroup = $folders[$folders[$folderId]->parent_id];
                        }

                        $properties[$parentGroup->id] = new JSON([
                            'show'    => true,
                            'name'    => $parentGroup->title,
                            'type'    => false,
                            'label'   => $parentGroup->title,
                            'isEqual' => true
                        ]);

                        /** @var MoyskladService $part */
                        foreach ((array) $folderParts as $part) {
                            $partName = $part->getConfiguratorName($product->id);

                            if ((int) $part->get('quantity', 1) > 1) {
                                $partName = $part->quantity . ' x ' . $partName;
                            }

                            if ($part instanceof MoyskladPart && !$part->isReloadContentForProduct($product->id)) {
                                if ($part->option !== null) {
                                    $partName .= ' ' . Text::sprintf('COM_HYPERPC_PRODUCT_OPTION', $part->option->name);
                                }
                            }

                            $partAdvantages = $part->getAdvantages();
                            if (!empty($partAdvantages)) {
                                /** @todo move to render template */
                                $advantagesHtml ='<ul class="uk-list uk-list-collapse uk-text-muted uk-text-small uk-margin-remove-top">';
                                foreach ($partAdvantages as $advantage) {
                                    $advantagesHtml .= "<li>{$advantage}</li>";
                                }
                                $advantagesHtml .= '</ul>';
                                $partName .= $advantagesHtml;
                            }

                            if (count($folderParts) > 1) {
                                $propValues[$parentGroup->id][$sessionId][] = $partName;
                            } else {
                                $propValues[$parentGroup->id][$sessionId] = $partName;
                            }
                        }
                    }
                }
            }

            $return
                ->set('items', $items)
                ->set('properties', $properties);
        }

        foreach ($propValues as $folderId => $propValue) {
            if (array_key_exists($folderId, $properties)) {
                $result = array_diff_key($products, $propValue);
                foreach ($result as $itemKey => $product) {
                    $propValue[$itemKey] = '-';
                }

                $parentGroupId = $folders[$folderId]->parent_id;

                $valueBuffer = '';
                foreach ($propValue as $productId => $item) {
                    if (is_array($item)) {
                        $propValue[$productId] = implode('<br />', $item);
                    }

                    if (count($products) === 1) {
                        $properties[$parentGroupId]->set('isEqual', false);
                        $properties[$folderId]->set('isEqual', false);
                    } elseif ($valueBuffer === '') {
                        $valueBuffer = $propValue[$productId];
                    } elseif ($valueBuffer !== $propValue[$productId]) {
                        $properties[$parentGroupId]->set('isEqual', false);
                        $properties[$folderId]->set('isEqual', false);
                    }
                }

                ksort($propValue, SORT_STRING);
                $properties[$folderId]->set('values', $propValue);

                if ($properties[$folderId]->get('isEqual') && !$return->get('hasEqualRow')) {
                    $return->set('hasEqualRow', true);
                }
            }
        }

        $sortedProperties = [];
        foreach ($folders as $folder) {
            if (isset($properties[$folder->id])) {
                $_values = [];
                $values = $properties[$folder->id]->get('values');
                foreach ($items as $id => $value) {
                    if (!count($values)) {
                        continue;
                    }

                    $_values[$id] = $values[$id] ?? '-';
                }

                $properties[$folder->id]->set('values', $_values);
                $sortedProperties[] = $properties[$folder->id];
            }
        }

        $return->set('properties', $sortedProperties);

        return $return;
    }
}
