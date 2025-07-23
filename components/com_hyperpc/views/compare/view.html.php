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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Arr;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CompareHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Compare\Product\CompareFactory;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * Class HyperPcViewCompare
 *
 * @property    JSON    $items
 * @property    array   $positions
 * @property    string  $singleGroup
 * @property    array   $moyskladParts
 * @property    array   $productFolders
 * @property    array   $moyskladProducts
 *
 * @since       2.0
 */
class HyperPcViewCompare extends ViewLegacy
{

    const FIELD_CONTEXT_POSITION = 'position';

    /**
     * Display view action.
     *
     * @param   null $tpl
     * @return  mixed|void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $items = $this->hyper['helper']['compare']->getItemList();

        $this->positions = $items[CompareHelper::TYPE_POSITION];

        $singleGroup = $this->hyper['input']->get('group');
        $type = $this->hyper['input']->get('type');

        $this->productFolders = [];

        if ($type === CompareHelper::TYPE_POSITION && $singleGroup !== 'products') {
            $foldersIds = array_keys($this->positions);
            if (in_array((int) $singleGroup, $foldersIds)) {
                $this->productFolders = $this->hyper['helper']['productFolder']->getByIds([(int) $singleGroup], ['a.*'], 'a.id ASC', 'id');
            } else {
                $singleGroup = null;
            }
        }

        if (empty($singleGroup)) {
            $this->productFolders = $this->hyper['helper']['productFolder']->getByIds(array_keys($this->positions), ['a.*'], 'a.id ASC', 'id');
        }

        $this->singleGroup = $singleGroup;

        $this->items = new JSON();

        $this->_setComparePositions();

        $this->hyper['doc']->setTitle(Text::_('COM_HYPERPC_COMPARE_PAGE_TITLE'));

        parent::display($tpl);
    }

    /**
     * Set compare positions.
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setComparePositions()
    {
        foreach ($this->positions as $folderId => $positionList) {
            if (!array_key_exists($folderId, $this->productFolders) && $this->singleGroup !== 'products') {
                continue;
            }

            /** @var CompareHelper */
            $compareHelper = $this->hyper['helper']['compare'];

            foreach ($positionList as $position) {
                $sessionArgs = ['itemId' => $position->id];
                if ($position instanceof MoyskladProduct) {
                    if ($position->saved_configuration) {
                        $sessionArgs['optionId'] = $position->saved_configuration;
                    }
                    $sessionId = $compareHelper->getItemKey($sessionArgs);
                    $this->moyskladProducts[$sessionId] = $position;
                } else {
                    if (property_exists($position, 'option') && $position->option instanceof MoyskladVariant && $position->option->id) {
                        $sessionArgs['optionId'] = $position->option->id;
                    }
                    $sessionId = $compareHelper->getItemKey($sessionArgs);
                    $this->moyskladParts[$folderId][$sessionId] = $position;
                }
            }
        }

        if (isset($this->moyskladProducts) && count($this->moyskladProducts)) {
            ksort($this->moyskladProducts, SORT_STRING);
            $this->_setCompareMoyskladProducts();
        }

        if (isset($this->moyskladParts) && count($this->moyskladParts)) {
            $this->_setCompareMoyskladParts();
        }

        return $this;
    }

    /**
     * Set compare moysklad products.
     *
     * @return  $this
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setCompareMoyskladProducts()
    {
        $type = $this->hyper['input']->get('type');
        if (in_array($type, [CompareHelper::TYPE_PART, CompareHelper::TYPE_PRODUCT])) {
            return $this;
        }

        $compare = (new CompareFactory)::createCompare('Moysklad');
        $comparedProducts = $compare->getComparedProducts();
        $comparedProducts->set('groupName', Text::_('COM_HYPERPC_PRODUCTS'));
        $comparedProducts->set('groupAlias', 'moyskladProducts');

        $this->items->set('moyskladProducts', $comparedProducts);

        return $this;
    }

    /**
     * Set compare moysklad parts.
     *
     * @return  $this
     *
     * @throws \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    protected function _setCompareMoyskladParts()
    {
        $imageWidth  = $this->hyper['params']->get('catalog_part_img_width', HP_PART_IMAGE_THUMB_WIDTH);
        $imageHeight = $this->hyper['params']->get('catalog_part_img_height', HP_PART_IMAGE_THUMB_HEIGHT);

        $fieldContext = self::FIELD_CONTEXT_POSITION;
        /** @var Position[] $positionList */
        foreach ($this->moyskladParts as $folderId => $positionList) {
            if (!array_key_exists($folderId, $this->productFolders)) {
                continue;
            }

            /** @var ProductFolder */
            $folder = $this->productFolders[$folderId];

            $closestHead = -1;
            $positionsFields = [];
            $folderFields = $folder->getCustomFields(['context' => $fieldContext]);
            $properties = $this->_getProductFolderProperties($folder, $positionList, $folderFields);

            $returnItem = new JSON([
                'items'       => [],
                'properties'  => [],
                'hasEqualRow' => false,
                'groupName'   => $folder->title,
                'groupAlias'  => $folder->alias
            ]);

            $items = [];

            foreach ($properties as $fieldId => $property) {
                if ($property->type === 'url') {
                    continue;
                }

                if ($property->type === 'hpseparator') {
                    $closestHead = $fieldId;
                    continue;
                }

                $values      = [];
                $valueBuffer = '';

                foreach ($positionList as $position) {
                    $sessionArgs = new JSON([
                        'optionId' => null,
                        'itemId'   => $position->id
                    ]);

                    $positionName  = $position->name;
                    $positionUrl   = $position->getViewUrl();
                    $positionImage = $position->getRender()->image($imageWidth, $imageHeight);
                    $availability  = null;
                    if ($position instanceof Stockable && method_exists($position, 'isForRetailSale') && $position->isForRetailSale()) {
                        $availability = $position->getAvailability();
                    }

                    $compareEntityId = $position->id;
                    if (isset($position->option) && $position->option instanceof MoyskladVariant) {
                        $positionName .= ' ' . $position->option->name;
                        $positionUrl = $position->option->getViewUrl();
                        $sessionArgs->set('optionId', $position->option->id);

                        if (!empty($position->option->images->get('image', '', 'hpimagepath'))) {
                            $positionImage = $position->option->getRender()->image($imageWidth, $imageHeight);
                        }

                        if ($availability) {
                            $availability = $position->option->getAvailability();
                        }
                    }

                    $sessionId = $this->hyper['helper']['compare']->getItemKey($sessionArgs);

                    $renderItemData = [
                        'type'  => CompareHelper::TYPE_POSITION,
                        'image' => null,
                        'name'  => $positionName,
                        'url'   => $positionUrl,
                        'buy'   => [
                            'price'   => null,
                            'buttons' => null
                        ],
                        'availability' => $availability
                    ];

                    if ($position instanceof Stockable &&
                        method_exists($position, 'isForRetailSale') &&
                        $position->isForRetailSale() &&
                        !in_array($availability, [Stockable::AVAILABILITY_DISCONTINUED, Stockable::AVAILABILITY_OUTOFSTOCK])
                    ) {
                        $renderItemData['buy']['price'] = $position->getListPrice()->text();
                        $renderItemData['buy']['buttons'] = $position->getRender()->getCartBtn();
                        if (isset($position->option) && $position->option instanceof MoyskladVariant) {
                            $renderItemData['buy']['buttons'] = $position->getRender()->getCartBtn('button', [
                                'option'           => $position->option,
                                'part'             => $position,
                                'useDefaultOption' => false
                            ]);
                        }
                    }

                    $imgAttrs = new JSON();
                    if (array_key_exists('thumb', $positionImage)) {
                        $cacheImg = $positionImage['thumb'];
                        $imgAttrs->set('src', $cacheImg->getUrl());
                    }

                    $renderItemData['image'] = $imgAttrs->get('src');

                    $items[$sessionId] = new JSON($renderItemData);

                    if (!isset($positionsFields[$position->id])) {
                        $positionsFields[$position->id] = $folder->getPartFields($compareEntityId, ['context' => $fieldContext]);
                    }

                    if (isset($position->option) && $position->option instanceof MoyskladVariant) {
                        $optionsFieldIds = $position->params->get('option_fields');

                        if ($optionsFieldIds !== null) {
                            if (Arr::search($optionsFieldIds, $fieldId) !== false) {
                                $fieldValue = $position->option->params->find('options.' . $property->get('name'));
                                $positionsFields[$position->id][$fieldId]->value = $fieldValue;
                            }
                        }
                    }

                    $values[$sessionId] = isset($positionsFields[$position->id][$fieldId]) ? $positionsFields[$position->id][$fieldId]->getValue() : '';
                    if ($positionsFields[$position->id][$fieldId]->value !== null && $positionsFields[$position->id][$fieldId]->value !== 'none') {
                        $property->set('show', true);
                        if ($closestHead !== -1) {
                            $properties[$closestHead]->set('show', true);
                        }
                    }

                    if ($valueBuffer === '') {
                        $valueBuffer = $values[$sessionId];
                    } elseif ($valueBuffer !== $values[$sessionId]) {
                        $property->set('isEqual', false);
                        if ($closestHead !== -1) {
                            $properties[$closestHead]->set('isEqual', false);
                        }
                    }
                }
                $property->set('values', $values);
                if ($property->show && $property->isEqual && !$returnItem->get('hasEqualRow')) {
                    $returnItem->set('hasEqualRow', true);
                }
            }

            $returnItem
                ->set('items', $items)
                ->set('properties', $properties)
                ->set('partsFields', $positionsFields);

            $this->items->set($folder->id, $returnItem);
        }

        return $this;
    }

    /**
     * Get position compare group properties.
     *
     * @param   ProductFolder $folder
     * @param   array  $positionList
     * @param   array  $folderFields
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getProductFolderProperties(ProductFolder $folder, $positionList, array $folderFields)
    {
        $properties = [];
        foreach ((array) $folder->params->get('part_fields') as $fieldId) {
            if (!array_key_exists($fieldId, $folderFields)) {
                continue;
            }

            $properties[$fieldId] = new JSON([
                'values'  => [],
                'show'    => false,
                'name'    => $folderFields[$fieldId]->name,
                'type'    => $folderFields[$fieldId]->type,
                'label'   => $folderFields[$fieldId]->label,
                'isEqual' => (count($positionList) > 1)
            ]);
        }

        return $properties;
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();
        $this->hyper['helper']['assets']->widget('.hp-compare', 'HyperPC.SiteCompare', [
            'emptyMsg' => Text::_('COM_HYPERPC_COMPARE_EMPTY')
        ]);
    }
}
