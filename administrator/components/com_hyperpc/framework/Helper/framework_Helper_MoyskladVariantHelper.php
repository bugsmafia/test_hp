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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use MoySklad\Entity\Variant;
use MoySklad\Entity\MetaEntity;
use Joomla\Database\ParameterType;
use MoySklad\Entity\Product\Product;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Helper\Traits\MoyskladEntityPrices;
use HYPERPC\Helper\Traits\MoyskladEntityActions;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Helper\Traits\CharacteristicsBuilder;
use HYPERPC\Helper\Traits\TranslatableProperties;
use HyperPcViewMoysklad_Variant as ModelMoyskladVariant;

/**
 * Class MoyskladVariantHelper.
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class MoyskladVariantHelper extends EntityContext
{
    use MoyskladEntityPrices;
    use MoyskladEntityActions {
        MoyskladEntityActions::updateByMoyskladEntity as baseUpdateByMoyskladEntity;
    }
    use TranslatableProperties;
    use CharacteristicsBuilder;

    /**
     * Update variant by Moysklad entity
     *
     * @param   Variant $entity
     * @param   array $updatedFields
     *
     * @return  int
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    public function updateByMoyskladEntity(MetaEntity $entity, array $updatedFields = [])
    {
        $variantId = $this->baseUpdateByMoyskladEntity($entity, $updatedFields);

        if (\array_intersect(['description', 'attributes', 'externalCode'], $updatedFields)) {
            $this->updateCharacteristicsFromMoyskladEntity($entity);
        }

        return $variantId;
    }

    /**
     * Hold model.
     *
     * @var     ModelMoyskladVariant
     *
     * @since   2.0
     */
    protected static $_model;

    /**
     * Hold variants list.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_variants = [];

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $table = Table::getInstance('Moysklad_Variants');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Get translations table name.
     *
     * @return  string
     */
    public function getTranslationsTableName(): string
    {
        return 'Moysklad_Variants_Translations';
    }

    /**
     * Get array of translatable fields.
     *
     * @return  array
     */
    public function getTranslatableFields(): array
    {
        return ['description', 'metadata', 'translatable_params', 'review'];
    }

    /**
     * Get model.
     *
     * @return  ModelMoyskladVariant
     *
     * @since   2.0
     */
    public function getModel()
    {
        if (!isset(self::$_model)) {
            self::$_model = ModelAdmin::getInstance('Moysklad_Variant');
        }
        return self::$_model;
    }

    /**
     * Get parent from Moysklad entity
     *
     * @param   Variant $variant
     *
     * @return  Position
     *
     * @since   2.0
     */
    public function getParentFromMoyskladEntity(Variant $variant)
    {
        $parent = $variant->product;
        if ($parent instanceof Product) {
            $parentUuid = $parent->getMeta()->getId();

            /** @var PositionHelper */
            $positionHelper = $this->hyper['helper']['position'];

            /** @var Position */
            $parentPosition = $positionHelper->findBy('uuid', $parentUuid);

            if ($parentPosition->id) {
                return $positionHelper->expandToSubtype($parentPosition);
            }
        }

        return new Position();
    }

    /**
     * Get all variants.
     *
     * @param   bool $groupByPart
     * @param   array $partIds Parts array keys list.
     * @param   bool $loadArchive Flag of load archive options.
     * @return  array
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getVariants($groupByPart = true, array $partIds = [], $loadArchive = true)
    {
        $conditions = [];
        $db = $this->hyper['db'];

        if (count($partIds) > 0) {
            $conditions = [
                $db->quoteName('a.part_id') . ' IN (' . implode(', ', $partIds) . ')'
            ];
        }

        if ($loadArchive === false) {
            $conditions[] = 'NOT (' . $db->quoteName('a.state') . ' = ' . HP_STATUS_ARCHIVED . ' AND ' . $db->quoteName('a.balance') . ' = 0)';
        }

        if (count(self::$_variants) === 0) {
            self::$_variants = $this->hyper['helper']['moyskladVariant']->findAll([
                'conditions' => $conditions,
                'order'      => 'a.ordering ASC',
                'key'        => 'id'
            ]);
        }

        if ($groupByPart === false) {
            return self::$_variants;
        }

        static $variants = [];
        if (count($variants) === 0) {
            /** @var MoyskladVariant $variant */
            foreach (self::$_variants as $variant) {
                if (!isset($variants[$variant->part_id][$variant->id])) {
                    $variants[$variant->part_id][$variant->id] = $variant;
                }
            }
        }

        return $variants;
    }

    /**
     * Get part variants from option list.
     *
     * @param   int $partId
     * @param   array $variants
     * @return  array|mixed
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getPartVariants($partId, array $variants = [])
    {
        if (count($variants) === 0) {
            $variants = $this->getVariants();
        }

        if (array_key_exists((int) $partId, $variants)) {
            return $variants[$partId];
        }

        return [];
    }

    /**
     * Prepare data.
     *
     * @param   Variant $entity
     *
     * @return  array
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function prepareData(MetaEntity $entity): array
    {
        if (!($entity instanceof Variant)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to ' . __METHOD__ . ' must be an instance of ' . Variant::class . ', ' . get_class($entity) . ' given'
            );
        }

        return [
            'uuid'              => $entity->id,
            'name'              => $this->_getNameFromMoyskladEntity($entity),
            'part_id'           => $this->_getParentIdFromMoyskladEntity($entity),
            'list_price'        => $this->_getListPriceFromMoyskladEntity($entity),
            'sale_price'        => $this->_getSalePriceFromMoyskladEntity($entity),
            // 'vendor_code'       => '<vendor code>',
            'balance'           => (int) $entity->stock
        ];
    }

    /**
     * Updates variant characteristics.
     *
     * @param   Variant $variant
     *
     * @return  int rows updated
     */
    public function updateCharacteristicsFromMoyskladEntity(Variant $variant)
    {
        if (!\is_numeric($variant->externalCode)) {
            return 0;
        }

        $characteristics = \array_map(fn($characteristic) => [
                'variant_id' => $variant->externalCode,
                'characteristic' => $characteristic->id,
                'value' => $characteristic->value
            ], $variant->characteristics
        );

        $rowsUpdated = 0;

        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = $this->_db;
        foreach ($characteristics as $key => $value) {
            $rowData = \array_merge(
                $value,
                ['ordering' => $key]
            );

            $query = $db->getQuery(true);
            $query
                ->insert(HP_TABLE_MOYSKLAD_CHARACTERISTICS_VALUES)
                ->columns(\array_map(fn($column) => $db->quoteName($column), \array_keys($rowData)))
                ->values(\implode(',', \array_map(fn($value) => $db->quote($value), $rowData)));

            $sql = (string) $query .
                ' ON DUPLICATE KEY UPDATE ' .
                    $db->quoteName('value') . ' = VALUES(' . $db->quoteName('value') . '), ' .
                    $db->quoteName('ordering') . ' = VALUES(' . $db->quoteName('ordering') . ')';

            $result = false;
            try {
                $result = $db->setQuery($sql)->execute();
            } catch (\Throwable $th) {
                // log it
            }

            if ($result) {
                $rowsUpdated++;
            }
        }

        // Clear outdated rows
        $query = $db->getQuery(true);
        $query
            ->delete(HP_TABLE_MOYSKLAD_CHARACTERISTICS_VALUES)
            ->where($db->quoteName('variant_id') . ' = :variantid')
            ->bind(':variantid', $variant->externalCode, ParameterType::INTEGER)
            ->whereNotIn(
                $db->quoteName('characteristic'),
                \array_column(
                    $characteristics,
                    'characteristic'),
                ParameterType::STRING
            );
        try {
            $db->setQuery($query)->execute();
        } catch (\Throwable $th) {
            // log it
        }

        return $rowsUpdated;
    }

    /**
     * Get variant name from Moysklad entity
     *
     * @param   Variant $variant
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getNameFromMoyskladEntity(Variant $variant)
    {
        $originalName = trim($variant->name);

        $re = '/.+\((.+)\)$/';
        preg_match($re, $originalName, $matches);

        return isset($matches[1]) ? $matches[1] : $originalName;
    }

    /**
     * Get parent id from Moysklad entity
     *
     * @param   Variant $variant
     *
     * @return  int
     *
     * @since   2.0
     */
    protected function _getParentIdFromMoyskladEntity(Variant $variant)
    {
        $parent = $variant->product;
        if ($parent instanceof Product) {
            $parentUuid = $parent->getMeta()->getId();

            /** @var PositionHelper */
            $positionHelper = $this->hyper['helper']['position'];

            /** @var Position */
            $parentPosition = $positionHelper->findBy('uuid', $parentUuid);

            if ($parentPosition->id) {
                return $parentPosition->id;
            }
        }

        return 0;
    }
}
