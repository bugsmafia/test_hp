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

namespace HYPERPC\Helper\Traits;

use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Object\Variant\Characteristics\CharacteristicDataCollection;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;

/**
 * Trait CharacteristicsBuilder
 *
 * @package HYPERPC\Helper\Traits
 */
trait CharacteristicsBuilder
{
    use DatabaseAwareTrait;

    /**
     * Gets the characteristic list for a set of variants.
     *
     * @param   OptionMarker[]  $variants
     * @param   OptionMarker    $currentVariant
     *
     * @return  CharacteristicDataCollection
     */
    public function getCharacteristics(array $variants, OptionMarker $currentVariant): CharacteristicDataCollection
    {
        $this->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

        $currentVariantId = $currentVariant->get('id');
        $allCharacteristics = $this->loadCharacteristicOptions($variants, $currentVariant);
        $characteristicKeys = $this->getCharacteristicKeysFromCurrentVariant($allCharacteristics, $currentVariantId);
        $variantsMap = $this->mapVariantsToAttributes($allCharacteristics);
        $result = $this->buildCharacteristics($allCharacteristics, $characteristicKeys, $variantsMap, $currentVariantId);

        return $result;
    }

    /**
     * Builds raw characteristics data to the target structure list.
     *
     * @param   array       $characteristics    Raw characteristics data keyed by characteristic UUID.
     * @param   string[]    $characteristicKeys Ordered list of characteristic UUIDs.
     * @param   array       $variantsMap        Map of variants.
     * @param   int         $currentVariantId   ID of the currently selected variant.
     *
     * @return  CharacteristicDataCollection    Built characteristics.
     */
    private function buildCharacteristics(array $characteristics, array $characteristicKeys, array $variantsMap, int $currentVariantId): CharacteristicDataCollection
    {
        $result = $this->initializeCharacteristics($characteristics, $characteristicKeys);

        $this
            ->populateCurrentVariantOptions($result, $characteristicKeys, $variantsMap, $currentVariantId)
            ->populateMissingVariantIds($result, $characteristicKeys, $variantsMap, $currentVariantId);

        return CharacteristicDataCollection::create($result);
    }

    /**
     * Determine the ordered list of characteristic keys based on the current variant.
     *
     * @param   array   $characteristics    Raw attribute data keyed by characteristic UUID.
     * @param   int     $currentVariantId   ID of the currently selected variant.
     *
     * @return  string[] Ordered list of characteristic UUIDs, top to bottom.
     */
    private function getCharacteristicKeysFromCurrentVariant(array $characteristics, int $currentVariantId): array
    {
        $orderMap = [];

        foreach ($characteristics as $uuid => $info) {
            foreach ($info['options'] as $option) {
                if ($option['variant_id'] === $currentVariantId) {
                    $orderMap[$option['ordering']] = $uuid;
                    break;
                }
            }
        }

        \ksort($orderMap);

        return \array_values($orderMap);
    }

    /**
     * Initialize the result structure with unique values and null variant_ids.
     *
     * @param   array       $characteristics    Raw characteristics data keyed by characteristic UUID.
     * @param   string[]    $characteristicKeys Ordered list of characteristic UUIDs.
     *
     * @return  array<int, array{name:string,options:array<int,array{value:string,variant_id:null,is_active:bool}>}>
     */
    private function initializeCharacteristics(array $characteristics, array $characteristicKeys): array
    {
        $chars = [];

        foreach ($characteristicKeys as $uuid) {
            $name       = $characteristics[$uuid]['name'];
            $seenValues = [];
            $options    = [];

            foreach ($characteristics[$uuid]['options'] as $option) {
                $value = $option['value'];
                if (!isset($seenValues[$value])) {
                    $seenValues[$value] = true;
                    $options[] = ['value' => $value, 'variant_id' => null, 'is_active' => false];
                }
            }

            $chars[] = ['name' => $name, 'options' => $options];
        }

        return $chars;
    }

    /**
     * Loads raw characteristic options from the database for specified variants.
     *
     * @param   OptionMarker[]  $variants
     * @param   OptionMarker    $currentVariant
     */
    private function loadCharacteristicOptions(array $variants, OptionMarker $currentVariant): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('cv.variant_id'),
                $db->quoteName('c.uuid', 'characteristic_id'),
                $db->quoteName('c.name', 'characteristic_name'),
                $db->quoteName('cv.value'),
                $db->quoteName('cv.ordering')
            ])
            ->from($db->quoteName(HP_TABLE_MOYSKLAD_CHARACTERISTICS_VALUES, 'cv'))
            ->innerJoin(
                $db->quoteName(HP_TABLE_MOYSKLAD_CHARACTERISTICS, 'c'),
                $db->quoteName('c.uuid') . ' = ' . $db->quoteName('cv.characteristic')
            )
            ->innerJoin(
                $db->quoteName(HP_TABLE_MOYSKLAD_VARIANTS, 'v'),
                $db->quoteName('v.id') . ' = ' . $db->quoteName('cv.variant_id')
            )
            ->whereIn(
                $db->quoteName('cv.variant_id'),
                \array_map(fn($variant) => $variant->get('id'), $variants)
            )
            ->order($db->quoteName('cv.ordering') . ' ASC')
            ->order($db->quoteName('v.ordering') . ' ASC');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $characteristics = [];
        foreach ($rows as $row) {
            if (!\array_key_exists($row->characteristic_id, $characteristics)) {
                $characteristics[$row->characteristic_id] = [
                    'name' => $row->characteristic_name,
                    'options' => []
                ];
            }

            $characteristics[$row->characteristic_id]['options'][] = [
                'variant_id' => (int) $row->variant_id,
                'value'      => $row->value,
                'ordering'   => $row->ordering
            ];
        }

        $currentVariantId = $currentVariant->get('id');

        $characteristics = \array_filter(
            $characteristics,
            fn($characteristic) =>
                \count(
                    \array_filter(
                        $characteristic['options'],
                        fn($option) => $option['variant_id'] === $currentVariantId
                    )
                )
            );

        return $characteristics;
    }

    /**
     * Builds a map of variants to their characteristic values.
     *
     * @param   array $characteristics  Raw characteristics data keyed by characteristic UUID.
     *
     * @return  array<int, array<string,string>> Map: variant_id => [dimensionUuid => value, ...]
     */
    private function mapVariantsToAttributes(array $characteristics): array
    {
        $variantsMap = [];

        foreach ($characteristics as $dimensionUuid => $info) {
            foreach ($info['options'] as $option) {
                $variantsMap[$option['variant_id']][$dimensionUuid] = $option['value'];
            }
        }

        return $variantsMap;
    }

    /**
     * Picks the best matching variant for a given characteristic and value.
     *
     * @param   string      $characteristicUuid UUID of the characteristic dimension.
     * @param   string      $value              The value being tested.
     * @param   string[]    $characteristicKeys Ordered list of all characteristic UUIDs.
     * @param   array       $variantsMap        Map from mapVariantsToAttributes().
     * @param   array       $currentValues      Attribute values of the current variant.
     *
     * @return  int|null    Chosen variant_id or null if none found.
     */
    private function pickBestVariant(string $characteristicUuid, string $value, array $characteristicKeys, array $variantsMap, array $currentValues): ?int
    {
        if (!\in_array($characteristicUuid, \array_keys($currentValues))) {
            return null;
        }

        $searchStack = $currentValues;
        $searchStack[$characteristicUuid] = $value;

        // Sort search stack by ordered characteristic keys
        $searchStack = \array_merge(\array_flip($characteristicKeys), $searchStack);

        while(!empty($searchStack)) {
            foreach ($variantsMap as $vid => $attrs) {
                $matches = \array_intersect_assoc($attrs, $searchStack);
                if (\count($matches) === \count($searchStack)) {
                    return $vid;
                }
            }

            if ($characteristicUuid === \array_key_last($searchStack)) {
                break;
            }

            \array_pop($searchStack);
        }

        return null;
    }

    /**
     * Populates the options that belong to the current variant.
     *
     * @param   array       $characteristics    Structure from initializeCharacteristics().
     * @param   string[]    $characteristicKeys Ordered list of characteristic UUIDs.
     * @param   array       $variantsMap        Map from mapVariantsToAttributes().
     * @param   int         $currentVariantId   ID of the currently selected variant.
     *
     * @return  $this
     */
    private function populateCurrentVariantOptions(array &$characteristics, array $characteristicKeys, array $variantsMap, int $currentVariantId): static
    {
        $currentValues = $variantsMap[$currentVariantId] ?? [];

        foreach ($characteristics as $index => &$dim) {
            $uuid         = $characteristicKeys[$index];
            $currentValue = $currentValues[$uuid] ?? null;

            foreach ($dim['options'] as &$opt) {
                if ($opt['value'] === $currentValue) {
                    $opt['variant_id'] = $currentVariantId;
                    $opt['is_active'] = true;
                    break;
                }
            }
            unset($opt);
        }
        unset($dim);

        return $this;
    }

    /**
     * Fills in missing variant_ids.
     *
     * @param   array       $characteristics    Structure from markCurrentVariantOptions().
     * @param   string[]    $characteristicKeys Ordered list of characteristic UUIDs.
     * @param   array       $variantsMap        Map from mapVariantsToAttributes().
     * @param   int         $currentVariantId   ID of the currently selected variant.
     *
     * @return  $this
     */
    private function populateMissingVariantIds(array &$characteristics, array $characteristicKeys, array $variantsMap, int $currentVariantId): static
    {
        $currentValues = $variantsMap[$currentVariantId] ?? [];

        foreach ($characteristicKeys as $index => $uuid) {
            foreach ($characteristics[$index]['options'] as &$opt) {
                if ($opt['variant_id'] === null) {
                    $opt['variant_id'] = $this->pickBestVariant(
                        $uuid,
                        $opt['value'],
                        $characteristicKeys,
                        $variantsMap,
                        $currentValues
                    );
                }
            }
            unset($opt);
        }

        return $this;
    }
}
