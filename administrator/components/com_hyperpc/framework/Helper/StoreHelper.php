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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Helper;

use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\StoreItem;
use HYPERPC\Helper\Context\EntityContext;

defined('_JEXEC') or die('Restricted access');

/**
 * Class StoreHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class StoreHelper extends EntityContext
{

    const CONTEXT_PART = HP_OPTION . '.part';

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
        $this->setTable(Table::getInstance('Stores'));
        parent::initialize();
    }

    /**
     * Get store item.
     *
     * @param   mixed   $storeIds
     * @param   int     $itemId
     * @param   int     $optionId
     * @param   string  $context
     * @param   bool    $multiply
     *
     * @return  array|StoreItem
     *
     * @since   2.0
     */
    public function getItem($storeIds, $itemId, $optionId = 0, $context = self::CONTEXT_PART, $multiply = false)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select(['*'])
            ->from($db->quoteName(HP_TABLE_STORE_ITEMS, 'a'));

        $conditions = [
            $db->quoteName('a.context') . ' = ' . $db->quote($context),
            $db->quoteName('a.item_id') . ' = ' . $db->quote($itemId)
        ];

        if (is_array($storeIds)) {
            $conditions[] = $db->quoteName('a.store_id') . ' in (' . implode(', ', $db->quote($storeIds)) . ')';
        } else {
            $conditions[] = $db->quoteName('a.store_id') . ' = ' . $db->quote($storeIds);
        }

        if ($optionId > 0 && !is_array($optionId)) {
            $conditions[] = $db->quoteName('a.option_id') . ' = ' . $db->quote($optionId);
        } elseif (is_array($optionId) && $multiply) {
            $conditions[] = $db->quoteName('a.option_id') . ' IN (' . implode(', ', $optionId) . ')';
        }

        $query->where($conditions);

        if ($multiply) {
            $_list = $db->setQuery($query)->loadAssocList('id');

            $class = StoreItem::class;
            $list  = [];
            foreach ($_list as $id => $item) {
                $list[$id] = new $class($item);
            }

            return $list;
        }

        $store = $db->setQuery($query)->loadAssoc();

        return new StoreItem(isset($store) ? $store : []);
    }

    /**
     * Get items count by store
     *
     * @param array $parts
     * @param int   $storeId
     *
     * @return int
     *
     * @since 2.0
     */
    public function getItemsCount(array $parts, int $storeId)
    {
        if (!count($parts)) {
            return 0;
        }

        $partsIds     = [];
        $optionsIds[] = 0;
        foreach ($parts as $part) {
            if (in_array($part->id, $partsIds)) {
                continue;
            }

            $partsIds[]   = $part->id;
            if ($part->getOptions()) {
                foreach ($part->getOptions() as $option)
                {
                    $optionsIds[] = $option->id;
                }
            }
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('COUNT(*)')
            ->from($db->quoteName(HP_TABLE_STORE_ITEMS, 'a'));

        $conditions = [
            $db->quoteName('a.context') . ' = ' . $db->quote(self::CONTEXT_PART),
            $db->quoteName('a.item_id') . ' IN (' . implode(',', $partsIds) . ')',
            $db->quoteName('a.option_id') . ' IN (' . implode(',', $optionsIds) . ')',
            $db->quoteName('a.store_id') . ' = ' . $db->quote($storeId)
        ];

        $query->where($conditions);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Get store address by store id
     *
     * @param   int $storeId
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAddress($storeId)
    {
        $store = $this->findById($storeId);

        return trim($store->getParam('city', $store->name)) . ', ' . trim($store->getParam('address', ''));
    }

    /**
     * @param   array $minPickingDate [
     *     'raw'        => string,
     *     'value'      => string,
     *     'isToday'    => bool,
     *     'isTomorrow' => bool
     * ]
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPickupFromTheStoreStr($minPickingDate)
    {
        $pickupFromTheStoreStr = Text::sprintf(
            'COM_HYPERPC_PICKUP_FROM_THE_STORE',
            mb_strtolower(
                ($minPickingDate['isToday'] || $minPickingDate['isTomorrow'] || empty($minPickingDate['value'])) ?
                    $minPickingDate['value'] : Text::sprintf('COM_HYPERPC_SINCE', $minPickingDate['value'])
            )
        );

        return $pickupFromTheStoreStr;
    }
}
