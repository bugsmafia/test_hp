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

use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\MoyskladStockHelper;
use HYPERPC\Helper\MoyskladStoreHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\Traits\PriceTrait;
use HYPERPC\Render\MoyskladVariant as VariantRender;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Traits\AvailabilityTrait;

/**
 * Class MoyskladVariants
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @property    VariantRender $_render
 * @method      VariantRender getRender()
 *
 * @since       2.0
 */
class MoyskladVariant extends Entity implements OptionMarker
{
    use PriceTrait, AvailabilityTrait;

    /**
     * Folder alias.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $alias;

    /**
     * Option sorting.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $ordering;

    /**
     * Balance of goods.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $balance = 0;

    /**
     * Full description.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $description;

    /**
     * Primary key.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Service images.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $images;

    /**
     * List price object.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $list_price = 0.00;

    /**
     * Category meta data.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $metadata;

    /**
     * Name of service.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $name;

    /**
     * Folder params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Parent part id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $part_id;

    /**
     * Review tabs.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $review;

    /**
     * Sale price object.
     *
     * @var     Money
     *
     * @since   2.0
     */
    public $sale_price = 0.00;

    /**
     * Published status.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $state = 0;

    /**
     * Position translatable params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $translatable_params;

    /**
     * Folder uuid.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $uuid;

    /**
     * Vendor code.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $vendor_code;

    /**
     * Initialize entity.
     *
     * @return  void
     *
     * @since   2.0
     *
     * @todo remove after discounts are done
     */
    public function initialize()
    {
        parent::initialize();

        $this->sale_price = ($this->list_price instanceof Money ? clone $this->list_price : $this->list_price);
    }

    /**
     * Get option fields list.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getFields()
    {
        $db = $this->hyper['db'];

        if ($this->id !== null) {
            $query = $db
                ->getQuery(true)
                ->select(['v.*', 'f.*'])
                ->from($db->quoteName('#__fields_values', 'v'))
                ->join('LEFT', $db->quoteName('#__fields', 'f') . ' ON v.field_id = f.id')
                ->where([
                    $db->quoteName('f.context') . ' = ' . $db->quote(HP_OPTION . '.variant'),
                    $db->quoteName('v.item_id') . ' = ' . $db->quote($this->id),
                ])
                ->order($db->quoteName('f.ordering') . ' ASC');

            return $this->hyper['helper']['object']->createList(
                $db->setQuery($query)->loadObjectList(),
                Field::class
            );
        }


        return [];
    }

    /**
     * Get available by store.
     *
     * @param   null|int  $storeId
     *
     * @return  JSON|mixed
     *
     * @since   2.0
     */
    public function getAvailabilityByStore($storeId = null)
    {
        static $output = [];
        $hash = md5((new JSON([$this->id]))->write());

        if (!array_key_exists($hash, $output)) {
            /** @var MoyskladStockHelper */
            $stockHelper = $this->hyper['helper']['moyskladStock'];
            $stocks = $stockHelper->getItems([
                'optionIds' => [$this->id]
            ]);

            $_items = [];

            /** @var MoyskladStoreHelper */
            $storeHelper = $this->hyper['helper']['moyskladStore'];
            foreach ($stocks as $storeItem) {
                $stockStoreId = $storeHelper->convertToLagacyId((int) $storeItem->store_id);
                if (!array_key_exists($stockStoreId, $_items)) {
                    $_items[$stockStoreId] = ['available' => 0];
                }

                $_items[$stockStoreId]['available'] += $storeItem->balance;
            }

            $output[$hash] = new JSON($_items);
        }

        return ($storeId !== null) ? $output[$hash]->find($storeId . '.available') : $output[$hash];
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
        return trim($this->images->get('image_assembled', '', 'hpimagepath'));
    }

    /**
     * Get free stocks
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getFreeStocks()
    {
        $stores = $this->hyper['helper']['moyskladStore']->getList();
        $stocks = $this->hyper['helper']['moyskladStock']->getItems([
            'optionIds' => [$this->id],
            'storeIds'  => array_keys($stores)
        ]);

        $balance = 0;
        foreach ($stocks as $storeItem) {
            $balance += $storeItem->balance;
        }

        return $balance;
    }

    /**
     * Get option page title with part.
     *
     * @return  string
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getPageTitle()
    {
        $part = $this->getPart();

        if (!$part->id) {
            throw new \Exception(Text::_('COM_HYPERPC_NOT_FOUND_PART'), 404);
        }

        return $part->getPageTitle() . ' ' . $this->name;
    }

    /**
     * Get variant part object.
     *
     * @return  MoyskladPart
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getPart()
    {
        return $this->hyper['helper']['moyskladPart']->findById($this->part_id);
    }

    /**
     * Get sorting review array.
     *
     * @param   string $sorting
     * @param   string $order
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getReview($order = 'asc', $sorting = '{n}.sorting')
    {
        return Hash::sort($this->review->getArrayCopy(), $sorting, $order);
    }

    /**
     * Get item dimensions and weight
     *
     * @return  MeasurementsData
     *
     * @since   2.0
     *
     * @todo    get own measurements if they are different from the parent part
     */
    public function getDimensions(): MeasurementsData
    {
        return $this->getPart()->getDimensions();
    }

    /**
     * Get option price.
     *
     * @param   bool $checkRate
     *
     * @return  Money
     *
     * @since   2.0
     *
     * @todo remove after discounts are done
     */
    public function getPrice($checkRate = true)
    {
        if (!$checkRate) {
            return clone $this->list_price;
        }

        return clone $this->sale_price;
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
        $id = $this->part_id . '-' . $this->id;
        return $this->hyper['helper']['cart']->getItemKey($id, CartHelper::TYPE_POSITION);
    }

    /**
     * Get site view url.
     *
     * @param   array $query
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        $part = $this->hyper['helper']['moyskladPart']->findById($this->part_id, ['select' => 'a.product_folder_id']);
        return $this->hyper['helper']['route']->url(array_replace($query, [
            'view' => 'moysklad_variant',
            'id' => $this->id,
            'part_id' => $this->part_id,
            'product_folder_id' => $part->product_folder_id
        ]));
    }

    /**
     * Get merged params
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getParams()
    {
        static $result = [];

        if (!key_exists($this->id, $result)) {
            $params = $this->params->getArrayCopy();

            $translatableParams = $this->translatable_params?->getArrayCopy();

            $result[$this->id] = new JSON(array_merge($params, (array) $translatableParams));
        }

        return $result[$this->id];
    }

    /**
     * Get option name for configurator.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfigurationName()
    {
        $configuratorTitle = $this->getParams()->get('configurator_title');
        return (!empty($configuratorTitle)) ? $configuratorTitle : $this->name;
    }

    /**
     * Get option availability.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAvailability()
    {
        if ($this->hasBalance()) {
            return self::AVAILABILITY_INSTOCK;
        }

        $part = $this->getPart();

        if ($part->isArchived() || $this->isArchived()) {
            return self::AVAILABILITY_DISCONTINUED;
        } elseif ($part->canBePreordered()) {
            return self::AVAILABILITY_PREORDER;
        }

        return self::AVAILABILITY_OUTOFSTOCK;
    }

    /**
     * Get picking dates
     *
     * @return  JSON
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function getPickingDates()
    {
        $part = $this->getPart();
        $part->set('option', $this);
        return $part->getPickingDates();
    }

    /**
     * Get ralated case position
     *
     * @return  MoyskladPart|null
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getRelatedCase()
    {
        $relatedCaseItemKey = $this->params->get('related_case', '');

        $relatedCase = $this->hyper['helper']['position']->getByItemKey($relatedCaseItemKey);

        return $relatedCase instanceof MoyskladPart ? $relatedCase : null;
    }

    /**
     * Get sending dates
     *
     * @return  JSON
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getSendingDates()
    {
        $part = $this->getPart();
        $part->set('option', $this);
        return $part->getSendingDates();
    }

    /**
     * Get short description
     *
     * @return  string
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function getShortDescription()
    {
        $optionShortDesc = trim(strip_tags($this->getParams()->get('short_desc', ''), '<span>'));
        if (empty($optionShortDesc)) {
            $part = $this->getPart();
            return strip_tags($part->getParams()->get('short_desc', ''), '<span>');
        }

        return $optionShortDesc;
    }

    /**
     * Check has balance.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasBalance()
    {
        static $balances = [];

        if (!array_key_exists($this->id, $balances)) {
            /** @var MoyskladStockHelper */
            $stockHelper = $this->hyper['helper']['moyskladStock'];
            $stocks = $stockHelper->getItems([
                'optionIds' => [$this->id]
            ]);

            $balances[$this->id] = !empty($stocks);
        }

        return $balances[$this->id];
    }

    /**
     * Check variant is archived
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isArchived()
    {
        return (int) $this->state === HP_STATUS_ARCHIVED;
    }

    /**
     * Check service is trashed
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isTrashed()
    {
        return (int) $this->state === HP_STATUS_TRASHED;
    }

    /**
     * Check service is trashed
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPublished()
    {
        return (int) $this->state === HP_STATUS_PUBLISHED;
    }

    /**
     * Check service is trashed
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isUnpublished()
    {
        return (int) $this->state === HP_STATUS_UNPUBLISHED;
    }

    /**
     * Fields of JSON data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return ['params', 'metadata', 'images', 'review', 'translatable_params'];
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldInt()
    {
        return ['id', 'state', 'part_id', 'balance'];
    }

    /**
     * Fields of money.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldMoney()
    {
        return ['list_price', 'sale_price'];
    }
}
