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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use Joomla\Registry\Registry;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Compare\Product\CompareFactory;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * Class CompareHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class CompareHelper extends AppHelper
{

    const SESSION_ITEMS_KEY = 'items';
    const SESSION_NAMESPACE = 'compare';
    const TYPE_PART         = 'part';
    const TYPE_PRODUCT      = 'product';
    const TYPE_POSITION     = 'position';

    /**
     * Hold SessionHelper object.
     *
     * @var     SessionHelper
     *
     * @since   2.0
     */
    protected $_session;

    /**
     * Add item to compare.
     *
     * @param   $data
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function addItem($data)
    {
        if (is_array($data)) {
            $data = new Data($data);
        }

        $items = $this->getItems();
        $_key  = (string) $this->getItemKey($data);

        if (!array_key_exists($_key, $items)) {
            $items[$data->get('type')][$_key] = $data->getArrayCopy();
            $this->_session->set(self::SESSION_ITEMS_KEY, (array) $items);
            return true;
        }

        return false;
    }

    /**
     * Clear compare session.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function clear()
    {
        $this->_session->set(self::SESSION_ITEMS_KEY, []);
        return true;
    }

    /**
     * Count all items.
     *
     * @param   null  $type
     *
     * @return  int
     *
     * @since   2.0
     */
    public function countItems($type = null)
    {
        $total = 0;
        $items = new JSON($this->getItems());

        if ($type === self::TYPE_PRODUCT) {
            return count((array) $items->get(self::TYPE_PRODUCT));
        }

        if ($type === self::TYPE_PART) {
            return count((array) $items->get(self::TYPE_PART));
        }

        if ($type === self::TYPE_POSITION) {
            return count((array) $items->get(self::TYPE_POSITION));
        }

        if (is_array($items->get(self::TYPE_PART))) {
            $total += count($items->get(self::TYPE_PART));
        }

        if (is_array($items->get(self::TYPE_PRODUCT))) {
            $total += count($items->get(self::TYPE_PRODUCT));
        }

        if (is_array($items->get(self::TYPE_POSITION))) {
            $total += count($items->get(self::TYPE_POSITION));
        }

        return $total;
    }

    /**
     * Count part by group.
     *
     * @param   null|int $groupId
     *
     * @return  int
     *
     * @since   2.0
     */
    public function countPartByGroup($groupId = null)
    {
        $itemList = $this->getItemList();
        if ($groupId !== null && isset($itemList[self::TYPE_PART][$groupId])) {
            return count($itemList[self::TYPE_PART][$groupId]);
        }

        return 0;
    }

    /**
     * Count position by group.
     *
     * @param   null|int $groupId
     *
     * @return  int
     *
     * @since   2.0
     */
    public function countPositionByGroup($groupId = null)
    {
        $itemList = $this->getItemList();
        if ($groupId !== null && isset($itemList[self::TYPE_POSITION][$groupId])) {
            return count($itemList[self::TYPE_POSITION][$groupId]);
        }

        return 0;
    }

    /**
     * Get compare group html by groupKey.
     *
     * @param   string|int $groupKey
     * @param   string $type
     *
     * @return  string
     *
     * @throws \JBZoo\Utils\Exception
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @todo   Get html for part groups
     */
    public function getGroupHtml($groupKey, $type)
    {
        $html = '';

        if ($groupKey === 'products') {
            $compareType = 'Product';
            if ($type === self::TYPE_POSITION) {
                $compareType = 'Moysklad';
            }
            $compare = (new CompareFactory)::createCompare($compareType);
            $items = $compare->getComparedProducts();

            $html = $this->hyper['helper']['render']->render('compare_products/tmpl/elements/items', [
                'items' => $items
            ], 'views');
        }

        return $html;
    }

    /**
     * Get item key by data.
     *
     * @param   $data
     * @return  string
     *
     * @since   2.0
     */
    public function getItemKey($data)
    {
        if (is_array($data)) {
            $data = new Data($data);
        }

        $key = $data->get('itemId');
        if ($data->get('optionId')) {
            $key .= '-' . $data->get('optionId');
        }

        return (string) $key;
    }

    /**
     * Get item list of compare.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getItemList()
    {
        return [
            self::TYPE_POSITION => $this->_getPositions()
        ];
    }

    /**
     * Get session items.
     *
     * @param   null|string $type
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getItems($type = null)
    {
        $session = $this->_session->get();
        $items   = $session->get(self::SESSION_ITEMS_KEY, []);
        ksort($items);

        if ($type !== null) {
            return array_key_exists($type, $items) ? (array) $items[$type] : [];
        }

        return (array) $items;
    }

    /**
     * Get compare link text for item.
     *
     * @param   bool $isInCompare   Result off CompareHelper::isInCompare()
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getLinkText($isInCompare)
    {
        return $isInCompare ? 'COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT' : 'COM_HYPERPC_COMPARE_ADD_BTN_TEXT';
    }

    /**
     * Get compare link title for item.
     *
     * @param   bool $isInCompare   Result off CompareHelper::isInCompare()
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getLinkTitle($isInCompare)
    {
        return $isInCompare ? 'COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE' : 'COM_HYPERPC_CONFIGURATOR_COMPARE_ADD';
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_session = clone $this->hyper['helper']['session'];
        $this->_session->setNamespace(self::SESSION_NAMESPACE);
    }

    /**
     * Check item in compare.
     *
     * @param   int     $itemId
     * @param   string  $type
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isInCompare($itemId, $type = self::TYPE_PRODUCT)
    {
        $items   = $this->getItems($type);
        $itemKey = $this->getItemKey(['itemId' => $itemId]);

        return array_key_exists($itemKey, $items);
    }

    /**
     * Remove item from compare.
     *
     * @param   $data
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function removeItem($data)
    {
        if (is_array($data)) {
            $data = new Data($data);
        }

        $items = $this->getItems();
        $type  = $data->get('type');
        $key   = (string) $this->getItemKey($data);

        if (count($items[$type]) && array_key_exists($type, $items)) {
            foreach ($items[$type] as $itemKey => $item) {
                if ((string) $itemKey === $key) {
                    unset($items[$type][$key]);
                }
            }

            $this->_session->set(self::SESSION_ITEMS_KEY, $items);
            return true;
        }

        return false;
    }

    /**
     * Get compared position entities
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _getPositions()
    {
        $_positions = $this->getItems(self::TYPE_POSITION);

        $output      = [];
        $positionIds = [];

        foreach ($_positions as $data) {
            if (!in_array($data['itemId'], $positionIds) && !empty($data['itemId'])) {
                $positionIds[] = $data['itemId'];
            }
        }

        /** @var PositionHelper */
        $positionHelper = $this->hyper['helper']['position'];
        /** @var ConfigurationHelper */
        $configurationHelper = $this->hyper['helper']['configuration'];
        /** @var MoyskladVariantHelper */
        $optionHelper = $this->hyper['helper']['moyskladVariant'];

        /** @var Position[] */
        $positions = count($positionIds) ? $positionHelper->findById($positionIds) : [];

        if (count($positions)) {
            foreach ($_positions as $data) {
                $data       = new Registry($data);
                $optionId   = (int) $data->get('optionId', 0);
                $positionId = (int) $data->get('itemId', 0);

                if (array_key_exists($positionId, $positions)) {
                    $position = clone $positionHelper->expandToSubtype($positions[$positionId]);

                    if (!empty($optionId)) {
                        if ($position instanceof MoyskladProduct) {
                            $configuration = $configurationHelper->findById($optionId);
                            if (empty($configuration->id)) {
                                continue;
                            }

                            $position->set('saved_configuration', $configuration->id);
                            $position->setListPrice($configuration->price);
                            $position->setSalePrice($configuration->price);
                        } else {
                            $option = $optionHelper->findById($optionId);
                            if (!$option->id) {
                                continue;
                            }

                            $position->set('option', $option);
                            $position->setListPrice($option->getListPrice());
                            $position->setSalePrice($option->getSalePrice());
                        }
                    }

                    $groupKey = $position->product_folder_id;
                    if (!isset($output[$groupKey])) {
                        $output[$groupKey] = [];
                    }

                    $itemKey = (string) $this->getItemKey($data);
                    if (!isset($output[$groupKey][$itemKey])) {
                        $output[$groupKey][$itemKey] = $position;
                    }
                }
            }
        }

        return $output;
    }
}
