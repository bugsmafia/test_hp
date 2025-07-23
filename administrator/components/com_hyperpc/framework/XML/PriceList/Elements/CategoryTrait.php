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
 * @author      Artem vyshnevskiy
 */

namespace HYPERPC\XML\PriceList\Elements;

use Joomla\CMS\Language\Text;
use HYPERPC\Object\PriceList\CategoryData;
use HYPERPC\XML\PriceList\Elements\TypeId;
use HYPERPC\Object\PriceList\CategoryCollection;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * Trait CategoryTrait
 *
 * @package HYPERPC\XML\PriceList
 *
 * @since   2.0
 */
trait CategoryTrait
{
    /**
     * Get categories collection
     *
     * @param   array $folderList [$typeId => CategoryMarker[]]
     *
     * @return  CategoryCollection
     *
     * @since   2.0
     */
    protected function _getCategoryCollection(array $folderList): CategoryCollection
    {
        $collectionData = [];

        /** @var CategoryMarker[] $folders */
        foreach ($folderList as $typeId => $folders) {
            ksort($folders);
            switch ($typeId) {
                case TypeId::PRODUCTS_TYPE_ID:
                    $collectionData = array_merge(
                        $collectionData,
                        $this->_getProductsCategoryCollection($folders)->items()
                    );
                    break;
                case TypeId::PARTS_TYPE_ID:
                case TypeId::SERVICES_TYPE_ID:
                    $collectionData = array_merge(
                        $collectionData,
                        $this->_getNonProductCategoryCollection($folders, $typeId)->items()
                    );
                    break;
            }
        }

        return new CategoryCollection($collectionData);
    }

    /**
     * Get products category collection
     *
     * @param   CategoryMarker[] $folders
     *
     * @return  CategoryCollection
     *
     * @since   2.0
     */
    private function _getProductsCategoryCollection(array $folders): CategoryCollection
    {
        $collectionData = [];

        $parents = [];
        foreach ($folders as $id => $folder) {
            $itemsType = strtoupper($folder->getItemsType());
            $itemsTypeId = constant(TypeId::class . "::PRODUCTS_TYPE_{$itemsType}_ID");
            if (!in_array($itemsTypeId, $parents)) {
                $parents[] = $itemsTypeId;
                $collectionData[] = new CategoryData([
                    'id'    => (int) (TypeId::PRODUCTS_TYPE_ID . $itemsTypeId),
                    'title' => Text::_('COM_HYPERPC_PRODUCT_TYPE_' . $itemsType)
                ]);
            }

            $collectionData[] = new CategoryData([
                'id'       => $id,
                'parentId' => (int) (TypeId::PRODUCTS_TYPE_ID . $itemsTypeId),
                'title'    => $folder->title
            ]);
        }

        return new CategoryCollection($collectionData);
    }

    /**
     * Get non-products category collection
     *
     * @param   CategoryMarker[] $folders
     * @param   int $typeId
     *
     * @return  CategoryCollection
     *
     * @since   2.0
     */
    private function _getNonProductCategoryCollection(array $folders, int $typeId): CategoryCollection
    {
        $collectionData = [];

        $parents = [];
        foreach ($folders as $id => $folder) {
            $parentId = $folder->getParentId();
            if ($parentId !== 1 && !in_array($parentId, $parents)) {
                $parents[] = $parentId;
                $parentFolder = $this->_groupHelper->findById($parentId);
                $collectionData[] = new CategoryData([
                    'id'    => (int) ($typeId . $parentId),
                    'title' => $parentFolder->getYandexMarketXmlName()
                ]);
            }

            $collectionData[] = new CategoryData([
                'id'       => $id,
                'parentId' => (int) ($typeId . $parentId),
                'title'    => $folder->getYandexMarketXmlName()
            ]);
        }

        return new CategoryCollection($collectionData);
    }
}
