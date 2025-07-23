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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Html\Data\Category;

use Exception;
use JBZoo\Utils\Filter;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\View\Html\Data\HtmlData;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class Complectation
 *
 * @package HYPERPC\Html\Data\Specification
 *
 * @since   2.0
 */
class Complectation extends HtmlData
{
    const COMPLECTATION_INDEX_FIELDS  = 'complectation_fields';

    /**
     * Hold index table
     *
     * @var   CategoryMarker
     *
     * @since 2.0
     */
    protected $table;

    /**
     * Category item
     *
     * @var   CategoryMarker
     *
     * @since 2.0
     */
    protected $category;

    /**
     * Hold params
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $params = [];

    /**
     * Hold fields
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $fields = [];

    /**
     * Hold category products
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $products = [];

    /**
     * Category complectations
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $complectations = [];

    /**
     * Hold active complectation
     *
     * @var   int
     *
     * @since 2.0
     */
    protected $activeComplectation;

    /**
     * Hold available complectations
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $availableComplectations = [];

    /**
     * Hold active platform
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $platform = [];

    /**
     * Hold active platform state
     *
     * @var   array
     *
     * @since 2.0
     */
    protected $platformState = [];

    /**
     * Container constructor.
     *
     * @param   CategoryMarker $category
     * @param   int            $productId
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function __construct($category, $productId)
    {
        parent::__construct();

        $db         = $this->hyper['db'];
        $conditions = [$db->quoteName('a.on_sale') . ' = 1'];

        $this->table    = $category::INDEX_TABLE;
        $this->category = $category;
        $this->products = $this->category->getProducts($conditions, 'a.price ASC', true);
        $this->params   = $this->category->params->get(self::COMPLECTATION_INDEX_FIELDS, []);

        if (!count($this->products)) {
            return false;
        }

        if ($productId && array_key_exists($productId, $this->products)) {
            $this->activeComplectation = $productId;
        }

        $this->build();
    }

    /**
     * Build specification.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function build()
    {
        $this->setComplectations();
        $this->setActiveComplectation();
        $this->setPlatformData();
    }

    /**
     * Set complectations
     *
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since  2.0
     */
    protected function setComplectations()
    {
        /** @var CartHelper $cartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        $imageMaxHeight = 250;

        /** @var ProductMarker $product */
        foreach ($this->products as $product) {
            /** @var MoyskladProductHelper $producthelper */
            $productHelper = $product->getHelper();

            $parts         = $productHelper->getTeaserParts($product, 'platform', false, false);
            $platformParts = $productHelper->getSpecificationWithFieldValues($parts);

            $imageSrc = $cartHelper->getItemImage($product, 0, $imageMaxHeight);

            $this->complectations[$product->id] = [
                'name'    => $product->getNameWithoutBrand(),
                'itemKey' => $product->getItemKey(),
                'price'   => Filter::int($product->getConfigPrice(true)->val()),
                'href'    => $product->getConfigUrl(0, 'default'),
                'image'   => $imageSrc,
                'parts'   => $platformParts
            ];
        }
    }

    /**
     * Get complectations
     *
     * @return array
     *
     * @since  2.0
     */
    public function getComplectations()
    {
        return $this->complectations;
    }

    /**
     * get index products
     *
     * @return array
     *
     * @since  2.0
     */
    public function getIndexProducts()
    {
        $db     = $this->hyper['db'];
        $ids    = [];
        $fields = [];

        foreach ($this->params as $field) {
            $ids[] = $field['id'];
        }

        foreach ($this->hyper['helper']['fields']->getFieldsById($ids) as $field) {
            $this->fields[$field->name] = $field;
            $fields[array_search($field->id, $ids)] = $field->name;
        }

        ksort($fields);

        $conditions[] = $db->quoteName('a.in_stock') . ' IS NULL';
        $conditions[] = $db->quoteName('a.product_id') . ' IN (' . implode(', ', array_keys($this->products)) . ')';

        $query = $db
            ->getQuery(true)
            ->select([
                'a.*'
            ])
            ->from(
                $db->qn($this->table, 'a')
            )
            ->where($conditions)
            ->order($db->qn('a.product_id') . ' ASC');

        $indexProducts = [];
        foreach ($db->setQuery($query)->loadObjectList() as $indexProduct) {
            /** @var ProductMarker $product */
            $product = $this->products[$indexProduct->product_id];

            foreach ($fields as $field) {
                if (!$indexProduct->$field) {
                    if ($this->activeComplectation === $product->id) {
                        $this->activeComplectation = null;
                        $this->setActiveComplectation();
                    }

                    break;
                }

                $fieldOptions = $this->fields[$field]->fieldparams->get('options');
                $fieldData = array_filter($fieldOptions, function ($fieldOption) use ($field, $indexProduct) {
                    return $fieldOption['value'] === $indexProduct->$field;
                });

                if (!count($fieldData)) {
                    continue;
                }

                $fieldData  = array_shift($fieldData);

                $indexProducts[$indexProduct->product_id][$field] = [
                    'value'     => $fieldData['name'],
                    'subline'   => isset($fieldData['subline']) ? $fieldData['subline'] : ''
                ];
            }
        }

        return $indexProducts;
    }

    /**
     * Set active complectation
     *
     * @since 2.0
     */
    protected function setActiveComplectation()
    {
        if ($this->activeComplectation) {
            return $this->activeComplectation;
        }

        $minPrice = min(array_column($this->complectations, 'price'));

        $activeComplectation = array_filter($this->complectations, function ($complectation) use ($minPrice) {
            return ($complectation['price'] === $minPrice);
        });

        $this->activeComplectation = array_key_first($activeComplectation);
    }

    /**
     * Get active complectations
     *
     * @return int
     *
     * @since  2.0
     */
    public function getActiveComplectation()
    {
        return $this->activeComplectation;
    }

    /**
     * Set platform data
     *
     * @since 2.0
     */
    public function setPlatformData()
    {
        $indexProducts = $this->getIndexProducts();

        if (array_key_exists($this->activeComplectation, $indexProducts)) {
            foreach ($indexProducts[$this->activeComplectation] as $fieldKey => $field) {
                $fieldValue = $field['value'];
                $fieldId    = $this->fields[$fieldKey]->id;

                $platformFieldTitle = $this->_findParamTitleByFieldId($fieldId);

                $this->platform[$fieldKey] = [];
                $this->platform[$fieldKey]['title'] = $platformFieldTitle ? $platformFieldTitle : $this->fields[$fieldKey]->title;
                $this->platform[$fieldKey]['value'] = $fieldValue;
            }
        }

        foreach ($indexProducts as $key => $indexProduct) {
            $countPlatformFields = count($this->platform);

            $index = 0;
            foreach ($indexProduct as $fieldName => $field) {
                $fieldValue   = $field['value'];
                $fieldSubline = $field['subline'];
                $related      = [];
                $isDisabled   = true;

                if ($index === 0) {
                    $isDisabled = false;
                }

                if ($this->activeComplectation === (int) $key) {
                    $isActive = true;
                } elseif (isset($this->platformState[$fieldName][$fieldValue]['isActive'])) {
                    $isActive = $this->platformState[$fieldName][$fieldValue]['isActive'];
                } else {
                    $isActive = false;
                }

                if (isset($this->platformState[$fieldName][$fieldValue]['related'])) {
                    $related = $this->platformState[$fieldName][$fieldValue]['related'];
                }

                $related[] = $key;

                $this->platformState[$fieldName][$fieldValue] = [
                    'subline'    => $fieldSubline,
                    'isActive'   => $isActive,
                    'isDisabled' => $isDisabled,
                    'related'    => $related
                ];

                if (isset($this->platform[$fieldName]['value']) && $this->platform[$fieldName]['value'] === $fieldValue) {
                    $countPlatformFields--;
                }

                $index++;
            }

            if ($countPlatformFields === 0) {
                $this->availableComplectations[] = $key;
            }
        }

        // TODO: check for what use $prevState
        $prevState = false;
        $index     = 0;
        foreach ($this->platformState as $fieldKey => $platformState) {
            $options = [];
            foreach ($this->fields[$fieldKey]->fieldparams->get('options') as $option) {
                $options[] = $option['name'];
            }

            uksort($this->platformState[$fieldKey], function ($key1, $key2) use ($options) {
                return (array_search($key1, $options) > array_search($key2, $options) ? 1 : -1);
            });

            foreach ($platformState as $fieldValue => $state) {
                if (!$prevState) {
                    $related = $state['related'];
                } else {
                    $availableStateKey = $this->platform[$prevState]['value'];
                    $availableState    = $this->platformState[$prevState][$availableStateKey];
                    $related = $availableState['filtered'];
                }
                $this->platformState[$fieldKey][$fieldValue]['filtered'] = array_values(array_intersect($related, $state['related']));

                if (count(array_intersect($related, $state['related']))) {
                    $this->platformState[$fieldKey][$fieldValue]['isDisabled'] = false;
                }
            }

            $prevState = $fieldKey;
            $index++;
        }
    }

    /**
     * get platform data
     *
     * @return array
     *
     * @since  2.0
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * get platform data
     *
     * @return array
     *
     * @since  2.0
     */
    public function getPlatformState()
    {
        return $this->platformState;
    }

    /**
     * get platform data
     *
     * @return array
     *
     * @since  2.0
     */
    public function getAvailableComplectations()
    {
        return $this->availableComplectations;
    }

    /**
     * Find param title by field id
     *
     * @param $id
     *
     * @return mixed|string
     *
     * @since 2.0
     */
    protected function _findParamTitleByFieldId($id)
    {
        foreach ($this->params as $param) {
            if ((int) $param['id'] === $id) {
                return $param['title'];
            }
        }

        return '';
    }
}
