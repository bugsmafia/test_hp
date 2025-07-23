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

namespace HYPERPC\Joomla\Model\Entity;

use Exception;
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoyskladStockHelper;
use HYPERPC\Helper\MoyskladStoreHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Render\MoyskladPart as PartRender;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Traits\AvailabilityTrait;

/**
 * Class MoyskladPart
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @property    PartRender $_render
 * @method      PartRender getRender()
 *
 * @since       2.0
 */
class MoyskladPart extends MoyskladService implements PartMarker
{
    use AvailabilityTrait;

    /**
     * Balance of goods.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $balance = 0;

    /**
     * Height value.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $height = 0;

    /**
     * Length value.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $length = 0;

    /**
     * Options count.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $options_count = 0;

    /**
     * Can be preordered.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $preorder = -1;

    /**
     * Can buy flag.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $retail = 0;

    /**
     * Weight value.
     *
     * @var     float
     *
     * @since   2.0
     */
    public $weight = 0.0;

    /**
     * Vendor code.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $vendor_code;

    /**
     * Width value.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $width = 0;

    /**
     * Virtual field of default option.
     *
     * @var     null|MoyskladVariant
     *
     * @since   2.0
     */
    public $option;

    /**
     * Get part availability.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAvailability()
    {
        if (!$this->hasBalance()) {
            if ($this->isDiscontinued()) {
                return self::AVAILABILITY_DISCONTINUED;
            } elseif ($this->canBePreordered()) {
                return self::AVAILABILITY_PREORDER;
            } else {
                return self::AVAILABILITY_OUTOFSTOCK;
            }
        }

        return self::AVAILABILITY_INSTOCK;
    }

    /**
     * Checks if a part can be preordered.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function canBePreordered()
    {
        $partPreorder = (int) $this->preorder;
        if ($partPreorder !== -1) {
            return (bool) $partPreorder;
        }

        $group = $this->getFolder();
        // @todo check preorder param in group\folder
        $groupPreorder = $group->params->get('preorder');

        return (bool) $groupPreorder;
    }

    /**
     * Get image assembled
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getImageAssembled()
    {
        return $this->images->get('image_assembled', '', 'hpimagepath');
    }

    /**
     * Get part name with option name
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName()
    {
        $name = $this->name;

        if ($this->option instanceof MoyskladVariant) {
            $name .= ' ' . $this->option->name;
        }

        return $name;
    }

    /**
     * Get part configurator name.
     *
     * @param   mixed $productId
     * @param   bool $considerOption
     * @param   bool $considerQuantity
     * @return  mixed|string
     *
     * @since   2.0
     */
    public function getConfiguratorName($productId = null, $considerOption = false, $considerQuantity = false)
    {
        $reloadName       = $this->getParams()->get('reload_content_name');
        $productReloadIds = (array) $this->getParams()->get('reload_content_product_ids');

        if ($productId && in_array((string) $productId, $productReloadIds) && !empty($reloadName)) {
            if ($considerQuantity && $this->quantity > 1) {
                $reloadName = sprintf('%s x %s', $this->quantity, $reloadName);
            }
            return $reloadName;
        }

        $partName = $this->getConfigurationName();
        if ($considerQuantity && $this->quantity > 1) {
            $partName = sprintf('%s x %s', $this->quantity, $partName);
        }

        if ($considerOption && $this->option instanceof MoyskladVariant && $this->option->id) {
            $partName .= ' ' . $this->option->getConfigurationName();
        }

        return $partName;
    }

    /**
     * Get availability by store (default).
     *
     * @param   null|int $storeId
     *
     * @return  mixed|JSON
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getAvailabilityByStore($storeId = null)
    {
        static $output = [];
        $hash = md5((new JSON([$this->id]))->write());

        if (!array_key_exists($hash, $output)) {
            $_output = [];

            $options   = $this->getOptions();
            $defOption = (int) $this->getDefaultOptionId();

            /** @var MoyskladStockHelper */
            $stockHelper = $this->hyper['helper']['moyskladStock'];
            $stocks = $stockHelper->getItems([
                'itemIds' => [$this->id],
                'optionIds' => !empty($options) ? array_keys($options) : []
            ]);

            /** @var MoyskladStoreHelper */
            $storeHelper = $this->hyper['helper']['moyskladStore'];
            foreach ($stocks as $storeItem) {
                $stockStoreId = $storeHelper->convertToLagacyId((int) $storeItem->store_id);
                if (!array_key_exists($stockStoreId, $_output)) {
                    $_output[$stockStoreId] = [
                        'available' => 0,
                        'options'   => []
                    ];
                }

                if (!empty($options)) {
                    if (!empty($defOption) && $storeItem->option_id === $defOption) {
                        $_output[$stockStoreId]['available'] += $storeItem->balance;
                    }

                    $_output[$stockStoreId]['options'][$storeItem->option_id] = (int) $storeItem->balance;
                } else {
                    $_output[$stockStoreId]['available'] += $storeItem->balance;
                }
            }

            $output[$hash] = new JSON($_output);
        }

        return ($storeId !== null) ? $output[$hash]->find($storeId . '.available') : $output[$hash];
    }

    /**
     * Get free stocks
     *
     * @return  int
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getFreeStocks()
    {
        $stores = $this->hyper['helper']['moyskladStore']->getList();
        $stocks = $this->hyper['helper']['moyskladStock']->getItems([
            'itemIds'  => [$this->id],
            'storeIds' => array_keys($stores)
        ]);

        $balance = 0;
        foreach ($stocks as $storeItem) {
            $balance += $storeItem->balance;
        }

        return $balance;
    }

    /**
     * Get item key
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey()
    {
        $key = parent::getItemKey();

        if ($this->option && $this->option->id) {
            $key .= '-' . $this->option->id;
        }

        return $key;
    }

    /**
     * Get option button title.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOptionBtnTitle()
    {
        $customTitle = (string) $this->params->get('option_btn_title', Text::_('COM_HYPERPC_OPTION_BTN_TITLE'));
        return ($customTitle !== '') ? $customTitle : Text::_('COM_HYPERPC_OPTION_BTN_TITLE');
    }

    /**
     * Get default option id.
     *
     * @return  bool|int
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getDefaultOptionId()
    {
        if (!$this->params) {
            return false;
        }

        $default = $this->params->get('default_option');
        if ($default === null) {
            if ($this->hasOptions()) {
                $options = $this->getOptions(false, false);
                $partAvailability = $this->getAvailability();
                /** @var MoyskladVariant $option */
                foreach ($options as $option) {
                    if ($partAvailability === Stockable::AVAILABILITY_INSTOCK) {
                        if ($option->isInStock()) {
                            $default = $option->id;
                            break;
                        }
                    } else {
                        $default = $option->id;
                        break;
                    }
                }
            }
        }

        return !empty($default) ? (int) $default : false;
    }

    /**
     * Get default option.
     *
     * @param   bool $loadFields
     *
     * @return  MoyskladVariant
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getDefaultOption($loadFields = false): MoyskladVariant
    {
        static $option   = [];
        $defaultOptionId = $this->getDefaultOptionId();
        $hash = $defaultOptionId . '.' . (int) $loadFields;

        if (!isset($option[$hash])) {
            $db = $this->hyper['db'];

            if ($defaultOptionId === false) {
                return new MoyskladVariant();
            }

            /** @var MoyskladVariant $_option */
            $_option = $this->hyper['helper']['moyskladVariant']->getById($defaultOptionId);

            $options = $this->getOptions(true);
            if (count($options) && !$_option->id) {
                $_option = array_shift($options);
            }

            if ($this->isInStock() && !$_option->isInStock()) {
                $options = $this->getOptions();
                foreach ($options as $optionItem) {
                    if ($optionItem->isInStock()) {
                        $optionInStock = $optionItem;
                        break;
                    }
                }
            }

            if (isset($optionInStock)) {
                $_option = $optionInStock;
            }

            if ($loadFields === true) {
                $query = $db
                    ->getQuery(true)
                    ->select(['f.id'])
                    ->from($db->quoteName('#__fields', 'f'))
                    ->where([
                        $db->quoteName('f.state')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED),
                        $db->quoteName('f.context') . ' = ' . $db->quote(HP_OPTION . '.variant')
                    ])
                    ->order($db->quoteName('f.ordering') . ' ASC');

                $fields   = [];
                $fieldIds = array_keys($db->setQuery($query)->loadAssocList('id'));

                if (count($fieldIds)) {
                    $query = $db
                        ->getQuery(true)
                        ->select(['v.*', 'f.*'])
                        ->from($db->quoteName('#__fields_values', 'v'))
                        ->join('LEFT', $db->quoteName('#__fields', 'f') . ' ON v.field_id = f.id')
                        ->where([
                            $db->quoteName('v.field_id') . ' IN (' . implode(', ', $fieldIds) . ')',
                            $db->quoteName('v.item_id')  . ' = ' . $db->quote($_option->id),
                            $db->quoteName('f.context')  . ' = ' . $db->quote(HP_OPTION . '.variant')
                        ])
                        ->order('FIELD (f.id, ' . implode(', ', $fieldIds) . ')');

                    $fields = $this->hyper['helper']['object']->createList(
                        $db->setQuery($query)->loadObjectList(),
                        Field::class
                    );
                }

                $_option->set('fields', $fields);
            }

            $option[$hash] = $_option;
        }

        return $option[$hash];
    }

    /**
     * Get item dimensions and weight
     *
     * @return MeasurementsData
     *
     * @since   2.0
     */
    public function getDimensions(): MeasurementsData
    {
        $defaultWeight = 1.0;
        $defaultLength = 50;
        $defaultWidth  = 20;
        $defaultHeight = 10;

        $result = [
            'weight' => !empty($this->weight) ? (double) $this->weight : $defaultWeight,
            'dimensions' => [
                'length' => !empty($this->length) ? $this->length : $defaultLength,
                'width'  => !empty($this->width) ? $this->width : $defaultWidth,
                'height' => !empty($this->height) ? $this->height : $defaultHeight
            ]
        ];

        return new MeasurementsData($result);
    }

    /**
     * Check has options in part.
     *
     * @return  bool
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function hasOptions()
    {
        $optionsCount = $this->optionsCount();

        return ($optionsCount > 0);
    }

    /**
     * Get part options count.
     *
     * @param   bool $allowArchive
     *
     * @return  int
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function optionsCount($allowArchive = false)
    {
        $options = $this->getOptions(false, $allowArchive);

        return count($options);
    }


    /**
     * Get part options
     *
     * @todo refactor code and use loadFields and archive
     *
     * @param   bool $loadFields    Flag of load option fields.
     * @param   bool $archive       Load archive options.
     *
     * @return  MoyskladVariant[]
     *
     * @since   2.0
     */
    public function getOptions($loadFields = false, $archive = true)
    {
        $db = $this->hyper['db'];

        $conditions = [
            $db->quoteName('a.part_id') . ' = ' . $db->quote($this->id)
        ];

        return $this->hyper['helper']['MoyskladVariant']->getAll(['a.*'], 'a.ordering ASC', $conditions, 'id', false);
    }

    /**
     * Get part price list image url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPriceListImage()
    {
        $imagePath = $this->images->get('image_y_market', '', 'hpimagepath');
        if ($this->option instanceof MoyskladVariant && $this->option->id) {
            $imagePath = $this->option->images->get('image_y_market', '', 'hpimagepath') ?: $imagePath;
        }

        if (empty($imagePath)) {
            $imagePath = $this->getExportImage() ?: $this->hyper['helper']['image']->getPlaceholderPath();
        }

        return Uri::root() . ltrim($imagePath, '/');
    }

    /**
     * Get id with type.
     *
     * @param   MoyskladVariant $option
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getProductId(MoyskladVariant $option = null)
    {
        $productId = 'part-' . $this->id;
        if ($option instanceof MoyskladVariant) {
            $productId .= $option->id ? '-' . $option->id : '';
        } elseif ($this->option instanceof MoyskladVariant) {
            $productId .= '-' . $this->option->id;
        } elseif ($this->getDefaultOptionId()) {
            $productId .= '-' . $this->getDefaultOptionId();
        }

        return $productId;
    }

    /**
     * Site view part url.
     *
     * @param   array   $query
     * @param   bool    $isFull
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [], $isFull = false)
    {
        $defaultQuery = [
            'view'     => 'moysklad_part',
            'id'       => $this->id,
            'product_folder_id' => $this->product_folder_id,
        ];

        $args = new JSON(array_replace($query, $defaultQuery));

        if ($args->get('opt', false, 'bool')) {
            if ($this->option instanceof MoyskladVariant && !empty($this->option->id)) {
                $args->set('view', 'moysklad_variant');
                $args->set('id', $this->option->id);
                $args->set('part_id', $this->id);
            }
            $args->offsetUnset('opt');
        }

        return $this->hyper['route']->build($args->getArrayCopy(), $isFull);
    }

    /**
     * Get export image path.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getExportImage()
    {
        $image = $this->images->get('image', '', 'hpimagepath');

        if ($this->option instanceof MoyskladVariant && $this->option->images) {
            $oImage = $this->option->images->get('image', '', 'hpimagepath');
            if (!empty($oImage)) {
                $image = $oImage;
            }
        }

        return $image;
    }

    /**
     * Is part available only for upgrade.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isOnlyForUpgrade()
    {
        if ($this->isDetached()) {
            return false;
        }

        $folder = $this->getFolder();
        return $folder->isOnlyForUpgrade();
    }

    /**
     * Check if position can buy.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isForRetailSale()
    {
        return $this->retail === -1 ? $this->getFolder()->isForRetailSale() : (bool) $this->retail;
    }

    /**
     * Check is discontinued.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isDiscontinued()
    {
        if (!$this->hasBalance()) {
            if ($this->isArchived()) {
                return true;
            }

            $options = $this->getOptions();
            if (count($options)) {
                $discontinued = true;
                foreach ($options as $option) {
                    if (!$option->isDiscontinued()) {
                        $discontinued = false;
                        break;
                    }
                }

                return $discontinued;
            }
        }

        return false;
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        $parentFields = parent::_getFieldInt();
        return array_merge(
            ['balance', 'height', 'length', 'options_count', 'preorder', 'width'],
            $parentFields
        );
    }

    /**
     * Fields of float data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldFloat()
    {
        return ['weight'];
    }
}
