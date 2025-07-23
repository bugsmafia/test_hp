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

namespace HYPERPC\Helper;

use JBZoo\Data\Data;
use HYPERPC\Data\ShortCode;
use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use UnexpectedValueException;
use Joomla\CMS\Language\Text;
use MoySklad\Entity\Attribute;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Helper\Traits\Product;
use MoySklad\Entity\Product\Service;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Helper\Traits\PositionsFinder;
use MoySklad\Entity\Product\ProductFolder;
use MoySklad\Entity\Product\AbstractProduct;
use HYPERPC\Helper\Traits\MoyskladEntityPrices;
use HYPERPC\Helper\Traits\MoyskladEntityBarcodes;
use HYPERPC\Helper\Traits\TranslatableProperties;
use HYPERPC\Joomla\Model\Entity\ProductFolder as HpProductFolder;

/**
 * Class PositionHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class PositionHelper extends EntityContext
{
    use Product;
    use MoyskladEntityPrices;
    use MoyskladEntityBarcodes;
    use TranslatableProperties;
    use PositionsFinder;

    const PUBLISHED_KEY = 'state';

    const POSITION_TYPE_SERVICE = 'service';
    const POSITION_TYPE_PART = 'part';
    const POSITION_TYPE_PRODUCT = 'product';

    const REGEX               = '/{positions\s(.+?)}/i';
    const REGEX_POSITION_NAME = '/{positionname\s(.*?)}/i';
    const POSITION_PRICE_REGEX = '/{(positionprice|positioncredit)(.*?)}/i';

    /**
     * Position type field uuid
     *
     * @var string
     *
     * @since   2.0
     */
    protected static $_configIdFieldUuid;

    /**
     * Position type field uuid
     *
     * @var string
     *
     * @since   2.0
     */
    protected static $_typeFieldUuid;

    /**
     * Hold snippet product prices by id
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_snippetProductPrices = [];

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
        $table = Table::getInstance('Positions');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Get translations table.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTranslationsTableName(): string
    {
        return 'Positions_Translations';
    }

    /**
     * Get array of translatable fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getTranslatableFields(): array
    {
        return ['description', 'metadata', 'translatable_params', 'review'];
    }

    /**
     * Find position by itemKey
     *
     * @param   string $itemkey
     *
     * @return  Position|null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getByItemKey(string $itemKey)
    {
        preg_match('/position-(\d+)(-(\d+))?/', $itemKey, $matches);
        if (empty($matches)) {
            return null;
        }

        $positionId = $matches[1];
        $position = $this->findById($positionId, ['new' => true]);
        if (empty($position->id)) {
            return null;
        }

        $position = clone $this->expandToSubtype($position);

        if (isset($matches[3])) {
            if ($position->getType() === self::POSITION_TYPE_PART) {
                $optionId = $matches[3];
                $option = $this->hyper['helper']['moyskladVariant']->findById($optionId, ['new' => true]);
                $position->set('option', $option);
                $position->list_price = clone $option->list_price;
                $position->sale_price = clone $option->sale_price;
            }

            /** @todo get product variant if type is product */
        }

        return $position;
    }

    /**
     * Get configuration id field uuid
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfigIdFieldUuid()
    {
        self::$_configIdFieldUuid =
            self::$_configIdFieldUuid ??
            $this->hyper['params']->get('moysklad_product_config_id_field_uuid', '');

        return self::$_configIdFieldUuid;
    }

    /**
     * Get position type field uuid
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getTypeFieldUuid()
    {
        self::$_typeFieldUuid =
            self::$_typeFieldUuid ??
            $this->hyper['params']->get('moysklad_position_type_field_uuid', '');

        return self::$_typeFieldUuid;
    }

    /**
     * Get Moysklad type text value
     *
     * @param   string $type
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getMoyskladTypeValue($type)
    {
        return $this->hyper['params']->get("moysklad_position_type_{$type}_value", $type);
    }

    /**
     * Expand position to subtype entity
     *
     * @param   Position $position
     *
     * @return  Position
     *
     * @since   2.0
     */
    public function expandToSubtype(Position $position)
    {
        $typeAlias = $position->getType();
        return $this->hyper['helper']['moysklad' . $typeAlias]->findById($position->id);
    }

    /**
     * Check if part has filled properties.
     *
     * @param   array $properties
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasProperties(array $properties)
    {
        if (is_array($properties)) {
            foreach ($properties as $property) {
                if ($property instanceof Field) {
                    if (!in_array($property->value, ['', 'none', null])) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get records by ids.
     *
     * @param   array|string    $id
     * @param   array           $select
     * @param   string          $order
     * @param   null|string     $key
     * @param   array           $conditions
     * @param   bool            $cache
     *
     * @return  array
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getByIds($id, array $select = ['a.*'], $order = 'a.id ASC', $key = null, array $conditions = [], $cache = true)
    {
        $ids = (array) $id;
        if (!count($ids)) {
            return [];
        }

        $data = new Data([
            'key'        => $key,
            'ids'        => $ids,
            'order'      => $order,
            'select'     => $select,
            'conditions' => $conditions
        ]);

        $data->set('table', $this->_table->getTableName());

        $hash = md5($data->write());

        if (!array_key_exists($hash, self::$_getByIds)) {
            $db = $this->_table->getDbo();
            /** @var \JDatabaseQueryMysqli $query */
            $query = $db
                ->getQuery(true)
                ->select($select)
                ->from($this->_getFromQuery())
                ->order($order)
                ->where($db->qn('a.id') . ' IN (' . implode(', ', $ids) . ')');

            if (strpos($order, 'g.lft') !== false) {
                $query->join(
                    'LEFT',
                    $db->quoteName(HP_TABLE_PRODUCT_FOLDERS, 'g') . ' ON (' . $db->quoteName('a.product_folder_id') . ' = ' . $db->quoteName('g.id') . ')'
                );
            }

            $query = $this->_setConditions($query, $conditions);

            $_list = $db->setQuery($query)->loadAssocList($key);

            $class = $this->_getTableEntity();
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            self::$_getByIds[$hash] = $list;
        }

        return self::$_getByIds[$hash];
    }

    /**
     * Get position type
     *
     * @param   AbstractProduct $product
     *
     * @return  string $type
     *
     * @since   2.0
     */
    public function getPositionTypeFromMoyskladEntity(AbstractProduct $position)
    {
        $type = self::POSITION_TYPE_SERVICE;

        if ($position instanceof Service) {
            return $type;
        }

        if (property_exists($position, 'attributes') && is_array($position->attributes)) {
            /** @var Attribute $attribute */
            foreach ($position->attributes as $attribute) {
                if ($attribute->id === $this->getTypeFieldUuid()) {
                    $typeName = $attribute->value->name;

                    $db = $this->hyper['db'];

                    $query = $db->getQuery(true)
                        ->select('a.*')
                        ->from($db->quoteName(HP_TABLE_POSITION_TYPES, 'a'))
                        ->where($db->quoteName('a.name') . ' = ' . $db->quote($typeName));

                    $typeInfo = $db->setQuery($query)->loadAssoc();
                    if (is_array($typeInfo) && isset($typeInfo['alias'])) {
                        return $typeInfo['alias'];
                    }
                }
            }
        }

        return $type;
    }

    /**
     * Get position type id
     *
     * @param   string $alias
     *
     * @return  int
     *
     * @throws  UnexpectedValueException
     *
     * @since   2.0
     */
    public function getTypeIdByAlias(string $alias)
    {
        $db = $this->hyper['db'];

        $query = $db->getQuery(true)
            ->select('a.id')
            ->from($db->quoteName('#__hp_position_types', 'a'))
            ->where($db->quoteName('a.alias') . ' = ' . $db->quote($alias));

        $db->setQuery($query);

        $typeId = (int) ($db->loadResult());

        if (!$typeId) {
            throw new UnexpectedValueException(Text::_('COM_HYPERPC_NOT_FOUND_POSITION_TYPE'));
        }

        return $typeId;
    }

    /**
     * Render position name snippet
     *
     * @param   object $article
     *
     * @since   2.0
     */
    public function renderBySnippet(&$article)
    {
        $items = [];

        $this->_renderPositions($article, $items);
        $this->_renderPositionName($article);
        $this->_renderPositionPrice($article);

        return $items;
    }

    /**
     * Render position snippet
     *
     * @param   object $article
     * @param   array $items
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _renderPositions(&$article, &$items)
    {
        preg_match_all(self::REGEX, $article->text, $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $match) {
                $data = new ShortCode([
                    'order'  => 'a.price ASC',
                    'attrs'  => $match[1]
                ]);

                $normalizedData = $this->normalizeData($data->getArrayCopy());

                $initialAmount = $normalizedData->get('initialAmount');

                try {
                    $positions = $this->findByConditions($normalizedData->toArray(), $initialAmount);
                } catch (UnexpectedValueException $e) {
                    $output = '<script>console.warn("Can\'t render positions via snippet: ' . $e->getMessage() . '")</script>';
                    $article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
                    continue;
                }

                /** @var RenderHelper $renderHelper */
                $renderHelper = $this->hyper['helper']['render'];

                $output = [];

                if ($normalizedData->get('type') === 'product') {
                    $ajaxLoadArgs = [];
                    if ($initialAmount && $normalizedData->get('limit') && $initialAmount === \count($positions)) {
                        $ajaxLoadArgs = [
                            'limit'            => $normalizedData->get('limit'),
                            'offset'           => $initialAmount,
                            'ids'              => $normalizedData->get('ids') ? \join(',', $normalizedData->get('ids')) : null,
                            'game'             => $normalizedData->get('game') ?: null,
                            'order'            => $normalizedData->get('order'),
                            'parts'            => $normalizedData->get('config') ? \join(',', $normalizedData->get('config')) : null,
                            'price-range'      => $normalizedData->get('priceRange'),
                            'layout'           => $normalizedData->get('layout'),
                            'type'             => $normalizedData->get('type'),
                            'showFps'          => (int) $normalizedData->get('showFps'),
                            'context'          => 'position',
                            'platform'         => $normalizedData->get('platform'),
                            'load-unavailable' => (int) $normalizedData->get('loadUnavailable'),
                            'instock'          => $normalizedData->get('instock')
                        ];
                    }

                    $categories = $this->hyper['helper']['productFolder']->findAll();

                    $layout = $normalizedData->get('layout');

                    if (preg_match('/2024-(\w+)-default/', $layout, $matches)) {
                        $layoutType = $matches[1];
                        $args = [
                            'products' => $positions,
                            'groups'   => $categories,
                            'showFps'  => $normalizedData->get('showFps'),
                            'game'     => $normalizedData->get('game'),
                            'instock'  => $normalizedData->get('instock')
                        ];

                        if ($layoutType === 'grid' && !empty($ajaxLoadArgs)) {
                            $args['jsSupport'] = true;
                        }

                        $output[] = $renderHelper->render('product/teaser/' . $layout, $args);

                        if (!empty($ajaxLoadArgs)) {
                            $output[] = $renderHelper->render('category/load_more_button', [
                                'ajaxLoadArgs' => $ajaxLoadArgs
                            ], 'renderer', false);
                        }
                    } else {
                        $variants = $this->hyper['helper']['moyskladVariant']->getVariants();

                        $output[] = $renderHelper->render('product/teaser/' . $layout, [
                            'products' => $positions,
                            'options'  => $variants,
                            'groups'   => $categories,
                            'showFps'  => $normalizedData->get('showFps'),
                            'game'     => $normalizedData->get('game')
                        ]);

                        if (!empty($ajaxLoadArgs)) {
                            $output[] = $renderHelper->render('category/load_more_button', [
                                'ajaxLoadArgs' => $ajaxLoadArgs
                            ], 'renderer', false);
                        }
                    }
                } else {
                    $compareItems = $this->hyper['helper']['compare']->getItems('position');
                    foreach ($positions as $part) {
                        $type = $part->getType();
                        $layout = $normalizedData->get('layout');
                        $output[] = $renderHelper->render('part/teaser/' . $layout, [
                            $type          => $part,
                            'group'        => $part->getFolder(),
                            'compareItems' => $compareItems
                        ]);
                    }
                }

                $items = array_merge($items, $positions);
                $article->text = preg_replace("|$match[0]|", addcslashes(implode(PHP_EOL, $output), '\\$'), $article->text, 1);
            }
        }
    }

    /**
     * Render position name snippet
     *
     * @param   object $article
     *
     * @since   2.0
     */
    protected function _renderPositionName(&$article)
    {
        preg_match_all(self::REGEX_POSITION_NAME, $article->text, $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $match) {
                $match1 = str_replace(': ', ':', $match[1]);
                $match1 = str_replace(' :', ':', $match1);

                $values = explode(' ', $match1, 2);

                foreach ($values as $value) {
                    list($key, $val) = explode(':', $value);
                    $data[$key] = $val;
                }

                $output = [];

                $view    = $this->hyper['input']->get('view');
                $inputId = $this->hyper['input']->get('id');
                if ($view === 'product_in_stock') {
                    $products = $this->hyper['helper']['moyskladStock']->getProductsByConfigurationId($inputId);
                    if (count($products)) {
                        $product = array_shift($products);
                    }
                } elseif ($view === 'configurator_moysklad') {
                    if (!isset($data['product'])) {
                        $data['product'] = $this->hyper['input']->get('product_id');
                    }

                    if (preg_match('/^config-\d+$/', $inputId)) {
                        $configId = str_replace('config-', '', $inputId);
                        $configuration = $this->hyper['helper']['configuration']->findById($configId);
                        $product = $configuration->getProduct();
                    } else {
                        $product = $this->hyper['helper']['moyskladProduct']->findById($data['product']);
                    }
                } else {
                    if (!isset($data['product']) && $view === 'moysklad_product') {
                        $data['product'] = $inputId;
                    }

                    $segmentItem = $data['product'];
                    $keyProduct  = ctype_digit($data['product']) ? 'id' : 'alias';

                    $product = $this->hyper['helper']['moyskladProduct']->getBy($keyProduct, $segmentItem, ['a.*'], [], false);
                }

                if (!isset($product) || !$product->id) {
                    $article->text = preg_replace("|$match[0]|", '', $article->text, 1);
                    continue;
                }

                $segmentGroup = $data['group'];
                $keyGroup     = ctype_digit($data['group']) ? 'id' : 'alias';

                $folder = $this->hyper['helper']['productFolder']->getBy($keyGroup, $segmentGroup, ['a.*'], [], false);
                $parts  = $product->getConfigParts(
                    true,
                    'a.product_folder_id ASC',
                    false,
                    false,
                    true
                );

                if (!array_key_exists($folder->id, $parts)) {
                    break;
                }

                foreach ($parts[$folder->id] as $part) {
                    $output[] = $part->getConfiguratorName($product->id, true, true);
                }

                $article->text = preg_replace("|$match[0]|", addcslashes(implode(', ', $output), '\\$'), $article->text, 1);
            }
        }
    }

    /**
     * Render position prices snippet
     *
     * @param   object $article
     *
     * @since   2.0
     */
    protected function _renderPositionPrice(&$article)
    {
        preg_match_all(self::POSITION_PRICE_REGEX, $article->text, $productPriceMatches, PREG_SET_ORDER);

        if ($productPriceMatches) {
            foreach ($productPriceMatches as $match) {
                $id = (int) $match[2];

                if (!array_key_exists($id, self::$_snippetProductPrices)) {
                    $product = $this->hyper['helper']['moyskladProduct']->getById($id);

                    if ($product->id === null) {
                        $article->text = preg_replace("|$match[0]|", '', $article->text, 1);
                        continue;
                    }

                    self::$_snippetProductPrices[$id] = $product->getConfigPrice(true);
                }

                $price = self::$_snippetProductPrices[$id];

                if ($match[1] === 'positioncredit') {
                    $price = $this->hyper['helper']['credit']->getMonthlyPayment($price->val());
                }

                $article->text = preg_replace("|$match[0]|", addcslashes($price->text(), '\\$'), $article->text, 1);
            }
        }
    }

    /**
     * Get parent folder id.
     *
     * @param   AbstractProduct $position
     *
     * @return  int
     *
     * @since   2.0
     */
    protected function _getParentFolderId(AbstractProduct $position)
    {
        $parentFolderId = 1;
        $parentFolder = $position->productFolder;
        if ($parentFolder instanceof ProductFolder) {
            $parentFolderUuid = $parentFolder->getMeta()->getId();

            $hpParentFolder = $this->hyper['helper']['productFolder']->findBy('uuid', $parentFolderUuid);

            if ($hpParentFolder instanceof HpProductFolder && $hpParentFolder->id) {
                $parentFolderId = $hpParentFolder->id;
            }
        }

        return $parentFolderId;
    }
}
