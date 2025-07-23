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

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Money\Type\Money;
use HYPERPC\Object\Order\PositionData;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Object\SavedConfiguration\PartData;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Object\SavedConfiguration\PriceData;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Object\SavedConfiguration\PartDataCollection;

/**
 * Class ConfigurationHelper
 *
 * @property    \HyperPcTableSaved_Configurations $table
 *
 * @method      SaveConfiguration findById($value, array $options = [])
 * @method      \HyperPcTableSaved_Configurations getTable()
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class ConfigurationHelper extends EntityContext
{

    /**
     * Copy configuration by configuration id.
     *
     * @param   int         $configurationId
     * @param   int|null    $createdUserId
     *
     * @return  mixed|null
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function copy($configurationId, $createdUserId = null)
    {
        $configuration = $this->findById($configurationId);
        if ($configuration->id) {
            $date = Date::getInstance();

            $data = [
                'created_user_id'   => 0,
                'created_time'      => $date->toSql(),
                'modified_time'     => $date->toSql(),
                'deleted'           => $configuration->deleted,
                'price'             => $configuration->price->val(),
                'parts'             => $configuration->parts->write(),
                'product'           => $configuration->product->write()
            ];

            if ($createdUserId) {
                $data['created_user_id'] = Filter::int($createdUserId);
            }

            if ($this->getTable()->save($data)) {
                return $this->getTable()->getDbo()->insertid();
            }
        }

        return null;
    }

    /**
     * Get user saved configurations list.
     *
     * @param   int  $userId
     * @param   int  $limit
     * @param   int  $offset
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getUserConfigurations($userId, $limit = 5, $offset = 0)
    {
        $db = $this->_table->getDbo();

        $_configurations = $db->setQuery(
            $db
                ->getQuery(true)->select(['a.*'])
                ->from($db->quoteName($this->getTable()->getTableName(), 'a'))
                ->where([
                    $db->quoteName('a.created_user_id') . ' = ' . $db->quote($userId),
                    $db->quoteName('a.deleted')         . ' IS NULL OR ' .
                    $db->quoteName('a.deleted')         . ' != ' . $db->quote(HP_STATUS_PUBLISHED)
                ])
                ->order($db->quoteName('a.modified_time') . ' DESC')
                ->setLimit($limit, $offset)
        )->loadAssocList('id');

        $class          = $this->getTable()->getEntity();
        $configurations = [];
        foreach ($_configurations as $id => $item) {
            $configurations[$id] = new $class($item);
        }

        $countOfDeleted = 0;

        /** @var SaveConfiguration $configuration */
        foreach ($configurations as $id => $configuration) {
            if (!$configuration->isProductExists()) {
                $countOfDeleted++;
                unset($configurations[$id]);
            }
        }

        if ($limit !== 0 && $countOfDeleted > 0) {
            $newOffset = $offset + $limit;
            $configurations += $this->getUserConfigurations($userId, $countOfDeleted, $newOffset);
        }

        return $configurations;
    }

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
        $table = Table::getInstance('Saved_Configurations');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Actualize configuration price
     *
     * @param   SaveConfiguration $configuration
     *
     * @return  PriceData
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function actualizePrice(SaveConfiguration $configuration): PriceData
    {
        /** @var Money $productPrice */
        $productPrice = $this->hyper['helper']['money']->get(0);
        /** @var Money $servicesPrice */
        $servicesPrice = $this->hyper['helper']['money']->get(0);
        /** @var Money $totalPrice */
        $totalPrice = $this->hyper['helper']['money']->get(0);

        $hasChanges = false;

        $items = $configuration->getParts(true);
        $itemsData = PartDataCollection::create($configuration->parts->getArrayCopy());
        foreach ($items as $item) {
            $itemPrice = $item->getSalePrice();
            if ($item instanceof PartMarker && $item->option?->id) {
                $itemPrice = $item->option->getSalePrice();
            }

            /** @var PartData $itemData */
            $itemData = $itemsData->offsetGet($item->id);

            $itemPriceVal = $itemPrice->val();

            if ($itemData->price !== (int) $itemPriceVal) {
                $hasChanges = true;
                $itemData->price = (int) $itemPriceVal;
            }

            $quantityPriceVal = $itemPriceVal * $itemData->quantity;

            $totalPrice->add($quantityPriceVal);
            if ($item instanceof PartMarker) {
                if (!$item->isDetached()) {
                    $productPrice->add($quantityPriceVal);
                }
            } else {
                $servicesPrice->add($quantityPriceVal);
            }
        }

        if ($hasChanges) {
            $configuration->price = $totalPrice->getClone();
            $configuration->parts = new JSON($itemsData->toArray());

            $this->getTable()->save($configuration);
        }

        return new PriceData([
            'product' => $productPrice,
            'services' => $servicesPrice,
            'total' => $totalPrice
        ]);
    }

    /**
     * Save custom configuration.
     *
     * @param   ProductMarker $product
     * @param   array   $partIds
     * @param   array   $options
     * @param   array   $partsQuantity
     *
     * @return  bool|mixed
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function save(ProductMarker $product, array $partIds = [], $options = [], array $partsQuantity = [])
    {
        $productData = new JSON([
            'id'    => $product->id,
            'price' => $product->getListPrice()->val()
        ]);

        $context = SaveConfiguration::CONTEXT_MOYSKLAD;

        if (is_array($options)) {
            $options = new JSON($options);
        }

        $partsSavedData   = [];
        $optionsSavedData = $this->_getOptionSaveData($options, $context);

        $partIds = $this->_preparePartIds($partIds);
        $parts   = $this->_getPartsByIds($partIds, $partsQuantity, 'a.id ASC', $context);

        /** @var Money $productActualPrice */
        $productActualPrice = $this->hyper['helper']['money']->get();

        /** @var MoyskladPart|MoyskladService $part */
        foreach ($parts as $part) {
            $partData = [
                'id'        => $part->id,
                'price'     => $part->getListPrice()->val(),
                'group_id'  => $part->getGroupId(),
                'quantity'  => $part->quantity,
                'option_id' => null
            ];

            if (array_key_exists($part->id, $optionsSavedData)) {
                $partData['price']     = $optionsSavedData[$part->id]->get('price', 0, 'int');
                $partData['option_id'] = $optionsSavedData[$part->id]->get('id', 0, 'int');
            }

            $partsSavedData[$part->get('id')] = $partData;

            $partTotal = $partData['price'] * $part->quantity;
            $productActualPrice->add($partTotal);
        }

        $saveData = [
            'context' => $context,
            'product' => $productData->write(),
            'price'   => $productActualPrice->val(),
            'parts'   => (new JSON($partsSavedData))->write()
        ];

        $currentUser = Factory::getUser();
        if ($currentUser->id) {
            $saveData['created_user_id'] = $currentUser->id;
        }

        //  If save in foreach saved only one configuration.
        $table = clone $this->_table;
        if ($table->save($saveData)) {
            return $table->getDbo()->insertid();
        }

        return false;
    }

    /**
     * Update custom configuration.
     *
     * @param   int     $id Configuration id.
     * @param   array   $partIds
     * @param   array   $options
     * @param   array   $partsQuantity
     *
     * @return  bool|int
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function update($id, array $partIds = [], $options = [], array $partsQuantity = [])
    {
        /** @var SaveConfiguration $configuration */
        $configuration = $this->hyper['helper']['configuration']->findById($id);

        $context = $configuration->getContext();

        if ($configuration->id > 0) {
            $parts = new JSON($this->_getPartsByIds($partIds, $partsQuantity, 'a.id ASC', $context));

            if (is_array($options)) {
                $options = new JSON($options);
            }

            $optionsSavedData = [];
            $optionList = $options->getArrayCopy();

            if (count($optionList)) {
                $optionHelper = $this->hyper['helper']['moyskladVariant'];

                foreach ($optionList as $option) {
                    if (!$option instanceof OptionMarker && isset($option['id'])) {
                        $option = $optionHelper->findById($option['id']);
                    }

                    if (!isset($optionsSavedData[$option->part_id])) {
                        $optionsSavedData[$option->part_id] = new JSON([
                            'id'    => $option->id,
                            'price' => $option->getListPrice()->val()
                        ]);
                    }
                }
            }

            $partsSavedData = [];
            /** @var Money $totalPrice */
            $totalPrice = $this->hyper['helper']['money']->get();

            foreach ($parts as $part) {
                $partData = [
                    'id'        => $part->id,
                    'price'     => $part->getListPrice()->val(),
                    'group_id'  => $part->getGroupId(),
                    'quantity'  => $part->quantity ?? 1,
                    'option_id' => null
                ];

                if (array_key_exists($part->id, $optionsSavedData)) {
                    $partData['price']     = $optionsSavedData[$part->id]->get('price', 0, 'int');
                    $partData['option_id'] = $optionsSavedData[$part->id]->get('id', 0, 'int');
                }

                $partsSavedData[$part->get('id')] = $partData;

                $partTotal = $partData['price'] * $part->quantity;
                $totalPrice->add($partTotal);
            }

            if (!$configuration->created_user_id && $this->hyper['user']->id) {
                $configuration->set('created_user_id', $this->hyper['user']->id);
            }

            $configuration->set('parts', (new JSON($partsSavedData))->write());
            $configuration->set('options', $options->write());
            $configuration->set('price', $totalPrice->val());

            if ($this->_table->save($configuration->getArray())) {
                return $configuration->id;
            }
        }

        return false;
    }

    /**
     * Reassign all saved configuration between users
     *
     * @param  string $oldUserId
     * @param  string $userId
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function reassignUser(string $oldUserId, string $userId)
    {
        $db = $this->_table->getDbo();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName($this->getTable()->getTableName()))
            ->set([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($userId),
            ])
            ->where([
                $db->quoteName('created_user_id') . ' = ' . $db->quote($oldUserId),
            ]);

        return $db->setQuery($query)->execute();
    }

    /**
     * Finds all configurations in the order and updates their services
     *
     * @param   Order $order
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since 2.0
     */
    public function updateServicesFromOrder(Order $order)
    {
        $positionsData = PositionDataCollection::create($order->positions->getArrayCopy());
        // Iterate order positions data
        foreach ($positionsData as $data) {
            if ($data->type !== 'productvariant' || empty($data->option_id)) {
                continue; // skip non-product positions
            }

            $configId = $data->option_id;
            $configuration = $this->findById($configId);
            if ($configuration->order_id !== $order->id) {
                continue; // Skip if the original order does not match the current one (possible in the stock products)
            }

            $configurationPartsData = PartDataCollection::create($configuration->parts->getArrayCopy());
            $configurationParts = $configuration->getParts();

            // collect related services data from order
            $relatedServicesData = new PositionDataCollection();
            $pattern = '/position-\d+-product-\d+-' . $configId . '/';
            foreach ($positionsData->items() as $key => $_data) {
                if (preg_match($pattern, $key)) {
                    $relatedServicesData->offsetSet($_data->id, $_data);
                }
            }

            // Add new services data to configuration parts data collection and updata prices for all services
            $serviceHelper = $this->hyper['helper']['moyskladService'];
            /** @var PositionData $serviceData */
            foreach ($relatedServicesData->items() as $id => $serviceData) {
                if ($configurationPartsData->offsetExists($id)) {
                    /** @var PartData $configurationPartData */
                    $configurationPartData = $configurationPartsData->offsetGet($id);
                    // Set price from order to position data
                    $configurationPartData->price = (int) $serviceData->price * (100 - $serviceData->discount) / 100;
                    continue;
                }

                $service = $serviceHelper->findById($id);
                $configurationPartsData->offsetSet($id, new PartData([
                    'id'       => $id,
                    'group_id' => $service->product_folder_id,
                    'price'    => (int) $serviceData->price,
                    'quantity' => 1
                ]));
            }

            // Remove old services from configuration parts data collection and calculate new configuration total price
            $configurationTotal = 0;
            foreach ($configurationPartsData->items() as $id => $partData) {
                 // position is a service and doesn't exists in the list of related services
                if (!$relatedServicesData->offsetExists($id) && !($configurationParts[$id] instanceof MoyskladPart)) {
                    $configurationPartsData->offsetUnset($id);
                    continue;
                }

                $configurationTotal += $partData->price * $partData->quantity;
            }

            $configuration->price = $this->hyper['helper']['money']->get($configurationTotal);
            $configuration->parts = json_encode($configurationPartsData->toArray(), JSON_PRETTY_PRINT);

            $this->getTable()->save($configuration->getArray());
        }
    }

    /**
     * Get option list for save.
     *
     * @param   Data $options
     * @param   string $context
     *
     * @return  array
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getOptionSaveData(Data $options, $context)
    {
        $optionsSavedData = [];
        $optionList = $options->getArrayCopy();

        foreach ($optionList as $option) {
            if ((!$option instanceof OptionMarker)) {
                $optionId = (isset($option['id'])) ? $option['id'] : $option;
                $option   = $this->hyper['helper']['moyskladVariant']->findById($optionId);
            }

            /** @var OptionMarker $option */
            if (!isset($optionsSavedData[$option->part_id])) {
                $optionsSavedData[$option->part_id] = new JSON([
                    'id'    => $option->id,
                    'price' => $option->getPrice(false)->val()
                ]);
            }
        }

        return $optionsSavedData;
    }

    /**
     * Get part list by ids.
     *
     * @param   array $ids              Array ids.
     * @param   array $partsQuantity    Array part quantity list.
     * @param   string $order           Order records.
     * @param   string $context         Configuration context.
     *
     * @return  (PartMarker|MoyskladService)[]
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function _getPartsByIds(array $ids = [], array $partsQuantity = [], $order = 'a.id ASC', $context = SaveConfiguration::CONTEXT_MOYSKLAD)
    {
        if (count($ids)) {
            $partHelper  = $this->hyper['helper']['moyskladPart'];
            $db          = $partHelper->getDbo();

            $conditions = [
                'key'        => 'id',
                'order'      => $order,
                'conditions' => [
                    $db->quoteName('a.id') . ' IN (' . implode(', ', $ids) . ')'
                ]
            ];

            $partList = $partHelper->findAll($conditions);
            if ($context === SaveConfiguration::CONTEXT_MOYSKLAD) {
                $serviceList = $this->hyper['helper']['moyskladService']->findAll($conditions);
                $partList += $serviceList;
            }

            foreach ($partList as $id => $part) {
                if (array_key_exists($id, $partsQuantity)) {
                    $part->set('quantity', (int) $partsQuantity[$id]);
                } else {
                    $part->set('quantity', HP_QUANTITY_MIN_VAL);
                }
            }

            return $partList;
        }

        return [];
    }

    /**
     * Prepare part ids list.
     *
     * @param   array $partIds
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _preparePartIds(array $partIds)
    {
        $_partIds = [];
        foreach ($partIds as $partId) {
            if (is_array($partId)) {
                foreach ($partId as $id) {
                    $_partIds[] = $id;
                }
            } else {
                $_partIds[] = $partId;
            }
        }

        return $_partIds;
    }
}
