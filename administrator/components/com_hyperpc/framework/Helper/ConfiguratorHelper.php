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

namespace HYPERPC\Helper;

use JBZoo\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ConfiguratorHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ConfiguratorHelper extends AppHelper
{
    /**
     * Checks if any actions available in group
     *
     * @param   ProductMarker $product
     * @param   ProductFolder $group
     *
     * @return  boolean
     *
     * @todo    check quantities
     *
     * @since   2.0
     */
    public function anyActionsAvailable(ProductMarker $product, $group)
    {
        return true; // temporarily returns true in any case

        if ($this->isCanDeselected($product, $group)) {
            return true;
        }

        $allConfigParts = $product->getAllConfigParts(['groupIds' => $group->id]);

        if (count($allConfigParts) > 1) {
            return true;
        } elseif (count($allConfigParts) == 1) {
            /** @var PartMarker $part */
            $part = array_shift($allConfigParts);

            $partIsChecked =
                in_array((string) $part->id, $product->configuration->get('default', [])) ||
                array_key_exists($part->id, $product->configuration->get('option', []));

            if (!$partIsChecked) {
                return true;
            }

            $quantityEnabled = $part->params->get('enable_quantity', 0, 'bool') && $this->groupMaxQuantities($product, $group) > 1;
            if ($quantityEnabled) {
                return true;
            }

            $countOptions = 0;
            foreach ($part->getOptions() as $option) {
                if ($this->isOptionInConfigurator($product, $option)) {
                    $countOptions++;
                };

                if ($countOptions === 2) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get default option
     *
     * @param   ProductMarker $product
     * @param   PartMarker $part
     *
     * @return  OptionMarker|null
     *
     * @since   2.0
     */
    public function getDefaultOption(ProductMarker $product, PartMarker $part)
    {
        $options = $product->getPartOptions($part);
        if (count($options)) {
            $pickedOption = $product->getDefaultPartOption($part, $options);

            if ($this->isOptionInConfigurator($product, $pickedOption)) {
                return $pickedOption;
            } else { // TODO get defaul option from part settings
                /** @var OptionMarker $option */
                foreach ($options as $option) {
                    if ($option->isInStock()) {
                        return $option; // return first instock option
                    }
                }

                // If there are no options instock
                foreach ($options as $option) {
                    $optionAvailability = $option->getAvailability();
                    if (in_array($optionAvailability, [Stockable::AVAILABILITY_OUTOFSTOCK, Stockable::AVAILABILITY_DISCONTINUED])) {
                        continue;
                    }

                    return $option; // return first preordered
                }

                return array_shift($options); // return the first of all
            }
        }

        return null;
    }

    /**
     * Get group tree ids.
     *
     * @param   ProductFolder $group
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getGroupTreeIds($group)
    {
        $ids = [$group->id];
        $children = (array) $group->get('children', []);
        if (count($children)) {
            foreach ($children as $child) {
                $ids = array_merge($ids, $this->getGroupTreeIds($child));
            }
        }

        return $ids;
    }

    /**
     * Get group ids for configuration image
     *
     * @param   ProductMarker $product
     * @param   array $groupList
     * @param   array $productParts
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getImageGroupIds($product, $groupList, $productParts)
    {
        $imageGroupIds = [];

        $customizationGroupIds = $product->getCustomizationGroupIds();
        if (!empty($customizationGroupIds)) {
            foreach ($customizationGroupIds as $groupId) {
                if (isset($groupList[$groupId]) && $this->hasPartsInTree($groupList[$groupId], $productParts)) {
                    $imageGroupIds = array_reverse((array) $customizationGroupIds);
                    break;
                }
            }
        }

        $caseGroupId = $product->getCaseGroupId();
        if (empty($imageGroupIds) && isset($groupList[$caseGroupId]) && $this->hasPartsInTree($groupList[$caseGroupId], $productParts)) {
            $imageGroupIds = (array) $caseGroupId;
        }

        return $imageGroupIds;
    }

    /**
     * Get instock toggler position
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getInstockTogglerPosition()
    {
        return $this->hyper['params']->get('configurator_instock_position', 'navbar');
    }

    /**
     * Get nothing selected image src value.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getNothingSelectedImageSrc()
    {
        //  Transparent 400x225 image
        $imageSrc = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='225' viewBox='0 0 1 1'%3E%3C/svg%3E";

        if ($this->hyper['params']->get('site_context') === 'hyperpc') {
            $imageSrc = '/media/hyperpc/img/nothing-selected_400.jpg';
        }

        return $imageSrc;
    }

    /**
     * Get part quantity select html
     *
     * @param ProductFolder $group
     * @param ProductMarker $product
     * @param string        $key
     * @param array         $attribs
     *
     * @return  string
     *
     * @since   2.0
     */
    public function quantitySelect($group, ProductMarker $product, string $key, $attribs = [])
    {
        $quantities = $product->configuration ? $product->configuration->get($key . '_quantity', []) : [];

        if (array_key_exists($group->id, $quantities)) {
            $quantity = (int) $quantities[$group->id];
        } else {
            $quantity = 0;
        }

        return HTMLHelper::_(
            'select.genericlist',
            [
                0  => Text::_('COM_HYPERPC_FROM_GLOBAL_PARAMS') . ' (' . $group->params->get('configurator_' . $key . '_count', 1, 'int') . ')',
                1  => 1,
                2  => 2,
                3  => 3,
                4  => 4,
                5  => 5,
                6  => 6,
                7  => 7,
                8  => 8,
                9  => 9,
                10 => 10,
                11 => 11,
                12 => 12,
                13 => 13,
                14 => 14,
                15 => 15,
                16 => 16,
                17 => 17,
                18 => 18,
                19 => 19,
                20 => 20,
                21 => 21,
                22 => 22,
                23 => 23,
                24 => 24,
                25 => 25
            ],
            $name = 'jform[configuration][' . $key . '_quantity][' . $group->id . ']',
            $attribs,
            null,
            null,
            $quantity
        );
    }

    /**
     * Get min quantity for group
     *
     * @param   ProductMarker $product
     * @param   ProductFolder $group
     *
     * @return  int
     *
     * @since   2.0
     */
    public function groupMinQuantities(ProductMarker $product, $group)
    {
        if (!$product->configuration) {
            return $group->params->get('configurator_min_count', 1, 'int');
        }

        $minQuantities = new JSON($product->configuration->get('min_quantity', []));
        $groupMinQuantity = $minQuantities->get((int) $group->id, 0, 'int');

        return $groupMinQuantity > 0 ? $groupMinQuantity : $group->params->get('configurator_min_count', 1, 'int');
    }

    /**
     * Get max quantity for group
     *
     * @param   ProductMarker $product
     * @param   ProductFolder $group
     *
     * @return  int
     *
     * @since   2.0
     */
    public function groupMaxQuantities(ProductMarker $product, $group)
    {
        if (!$product->configuration) {
            return $group->params->get('configurator_max_count', 1, 'int');
        }

        $maxQuantities = new JSON($product->configuration->get('max_quantity', []));
        $groupMaxQuantity = $maxQuantities->get((int) $group->id, 0, 'int');

        return $groupMaxQuantity > 0 ? $groupMaxQuantity : $group->params->get('configurator_max_count', 1, 'int');
    }

    /**
     * Return group quantity option
     *
     * @param   ProductMarker $product
     * @param   ProductFolder $group
     *
     * @return  array
     *
     * @since   2.0
     */
    public function groupQuantityOptions(ProductMarker $product, $group)
    {
        $quantityOptionsEls = [];

        $minQuantity = $this->groupMinQuantities($product, $group);
        $maxQuantity = $this->groupMaxQuantities($product, $group);

        if ($maxQuantity > 1) {
            for ($i = $minQuantity; $i <= $maxQuantity; $i++) {
                $quantityOptionsEls[$i] = $i;
            }
        }

        return $quantityOptionsEls;
    }

    /**
     * Checks if parts in the group can be deselected.
     *
     * @param   ProductMarker $product
     * @param   ProductFolder $group
     *
     * @return  boolean
     *
     * @since   2.0
     */
    public function isCanDeselected(ProductMarker $product, $group)
    {
        $canDeselectedProductData = new JSON($product->configuration->get('can_deselected', []));
        $canDeselectedProductGroup = $canDeselectedProductData->get((int) $group->id, -1, 'int');

        if ($canDeselectedProductGroup < 0) {
            return $group->params->get('configurator_can_deselected', true, 'bool');
        }

        return (bool) $canDeselectedProductGroup;
    }

    /**
     * Check parts in group.
     *
     * @param   int|string  $groupId
     * @param   array       $parts
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasPartsInGroup($groupId, array $parts)
    {
        $flag = false;
        /** @var PartMarker $part */
        foreach ($parts as $part) {
            $groups = (array) $part->params->get('groups', []);
            if (in_array((string) $groupId, $groups)) {
                $flag = true;
                break;
            }
        }

        return $flag;
    }

    /**
     * Check has parts in group tree.
     *
     * @param   ProductFolder $group
     * @param   (PartMarker|MoyskladService)[] $parts
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasPartsInTree($group, $parts)
    {
        if (!count($parts)) {
            return false;
        }

        $groupTreeIds = $this->getGroupTreeIds($group);

        foreach ($parts as $part) {
            if (is_array($part)) { // Can $part really be an array?
                $hasPartsInTree = $this->hasPartsInTree($group, $part);
                if ($hasPartsInTree === true) {
                    return true;
                }
            } else {
                if ($part instanceof Stockable && in_array($part->getAvailability(), [Stockable::AVAILABILITY_OUTOFSTOCK, Stockable::AVAILABILITY_DISCONTINUED])) {
                    continue;
                }

                $groupId = $part->product_folder_id;
                if (in_array($groupId, $groupTreeIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check part is checked.
     *
     * @param   ProductMarker $product
     * @param   int           $partId
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isCheckedPart(ProductMarker $product, $partId)
    {
        $selectedParts = $product->configuration->get('default');
        if (empty($selectedParts)) {
            return false;
        }

        $isChecked = in_array((string) $partId, $selectedParts);

        if (!$isChecked) {
            $options = (array) $product->configuration->get('options', []);
            $selectedOptions = (array) $product->configuration->get('option', []);
            if (array_key_exists($partId, $selectedOptions) && array_key_exists($selectedOptions[$partId], $options)) {
                $isChecked = true;
            }
        }

        return $isChecked;
    }

    /**
     * Check debug form mode.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDebugForm()
    {
        return $this->hyper['params']->get('configurator_debug_form', false, 'bool');
    }

    /**
     * Check is multiply or single part select.
     *
     * @param   ProductMarker $product
     * @param   int           $groupId
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isMultiplyPartsSelect(ProductMarker $product, $groupId)
    {
        $multiply = (array) $product->configuration->get('multiply', []);
        $data     = new JSON($multiply);

        return $data->get((int) $groupId, false, 'bool');
    }

    /**
     * Is the option must shown in product configurator
     *
     * @param   ProductMarker              $product
     * @param   PartMarker|MoyskladService $part
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPartInConfigurator(ProductMarker $product, $part)
    {
        $boundParts = (array) $product->configuration->get('parts', []);

        if ($part->id === null || !array_key_exists($part->id, $boundParts)) {
            return false;
        }

        if ($part->isDiscontinued() || $part->isOutOfStock()) {
            return false;
        }

        return true;
    }

    /**
     * Is part in the assembly warehouse
     *
     * @param   PartMarker     $part
     * @param   OptionMarker[] $options
     * @param   array          $optionsInStock
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isPartInStock(PartMarker $part, array $options = [], array &$optionsInStock = [])
    {
        $storeId = $this->hyper['params']->get('warehouse_assembly');

        if (!$storeId) { // If not specified assembly warehouse id
            $partIsInStock = $part->isInStock();

            if (!count($options) || !$partIsInStock) {
                return $partIsInStock;
            }

            /** @var OptionMarker $option */
            foreach ($options as $option) {
                if ($option->isInstock()) {
                    $optionsInStock[] = $option->id;
                }
            }

            if (!empty($optionsInStock)) {
                return true;
            }

            return false;
        }

        // By warehouse id
        if (!count($options)) {
            return $part->getAvailabilityByStore($storeId);
        }

        /** @var OptionMarker $option */
        foreach ($options as $option) {
            if ($option->getAvailabilityByStore($storeId)) {
                $optionsInStock[] = $option->id;
            }
        }

        if (!empty($optionsInStock)) {
            return true;
        }

        return false;
    }

    /**
     * Is the option must shown in product configurator
     *
     * @param   ProductMarker $product
     * @param   OptionMarker $option
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOptionInConfigurator(ProductMarker $product, OptionMarker $option)
    {
        if (empty($option->id)) {
            return false;
        }

        if ($option->isDiscontinued() || $option->isOutOfStock()) {
            return false;
        }

        $boundOptions = (array) $product->configuration->get('options', []);

        if (!array_key_exists($option->id, $boundOptions)) {
            $boundParts = (array) $product->configuration->get('parts', []);
            $partId = (string) $option->part_id;
            if (!in_array($partId, $boundParts) || in_array($partId, $boundOptions)) {
                // if part is not bound or at least 1 option of this part is bound
                return false;
            }
        }

        return true;
    }

    /**
     * Get default init state configurator in stock checkbox.
     *
     * @param  ProductMarker $product
     *
     * @return bool
     *
     * @since  2.0
     */
    public function inStockOnlyInitState(ProductMarker $product)
    {
        $initState = $product->getFolder()->params->get('configurator_instock_default', -1, 'int');

        if ($initState === -1) {
            return $this->hyper['params']->get('configurator_instock_default', 0, 'bool');
        }

        return (bool) $initState;
    }

    /**
     * Prepare producer parts.
     *
     * @param   ProductMarker                   $product
     * @param   (PartMarker|MoyskladService)[]  $parts
     *
     * @return  array
     *
     * @todo change naming
     *
     * @since   2.0
     */
    public function preparePartsByProducer(ProductMarker $product, array $parts)
    {
        $return = [];
        $defaultParts = (array) $product->configuration->get('default', [], 'hpArrayKeyInt');
        $partOptions  = (array) $product->configuration->get('part_options', []);

        foreach ($partOptions as $index => $data) {
            $data   = new JSON($data);
            $partId = $data->get('part_id', 0, 'int');
            if (!in_array($partId, $defaultParts) && $data->get('is_default') === true) {
                $defaultParts[] = $partId;
            }
        }

        foreach ($parts as $part) {
            if (in_array($part->id, $defaultParts)) {
                if ($part instanceof Stockable && !in_array($part->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
                    continue;
                }

                if (!array_key_exists($part->id, $return)) {
                    $return[$part->id] = $part;
                }
            }
        }

        return $return;
    }
}
