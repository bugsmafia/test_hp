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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Helper\Traits;

use HYPERPC\Helper\PositionHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Trait Product
 *
 * @package     HYPERPC\Helper\Traits
 *
 * @since 2.0
 */
trait Product
{

    /**
     * Get products by game alias and resolution.
     *
     * @param   string $game in format 'game@resolution'
     * @param   string $order
     * @param   bool   $getIds
     *
     * @return  array
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function findByGame(string $game, $order = 'a.price ASC', $getIds = false)
    {
        list($gameAlias, $gameResolution) = explode('@', $game);

        $db         = $this->hyper['db'];
        $products   = [];
        $conditions = [$db->qn('a.' . self::PUBLISHED_KEY) . ' = ' . $db->q(HP_STATUS_PUBLISHED)];

        if ($this instanceof PositionHelper) {
            // Load only products
            $conditions[] = $db->qn('a.type_id') . ' = 3';
        }

        $_products = $this->findAll([
            'order'      => $order,
            'conditions' => $conditions
        ]);

        $fpsHelper = $this->hyper['helper']['fps'];
        foreach ($_products as $product) {
            if ($product instanceof Position) {
                $product = $this->expandToSubtype($product);
            }

            $fps = $fpsHelper->getFps($product, $gameAlias);
            if (isset($fps[$gameAlias]) && $fps[$gameAlias]['ultra'][$gameResolution] >= 60) {
                $products[] = $getIds === true ? $product->id : $product;
            }
        }

        return $products;
    }

    /**
     * Get mini description for product from specified groups and parts
     *
     * @param   ProductMarker $product
     * @param   string  $divider
     * @param   array   $descriptionGroups
     * @param   bool    $groupsName
     *
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getMiniDescription(ProductMarker $product, array $descriptionGroups, $groupsName = true, $divider = '. ')
    {
        if (empty($descriptionGroups)) {
            return '';
        }

        $offerDescription = [];
        $configParts = $product->getConfigParts(true, 'a.product_folder_id ASC', false, false, true);

        foreach ($descriptionGroups as $groupId => $groupData) {
            if (key_exists($groupId, $configParts)) {
                $partName = $configParts[$groupId][0]->getName();
                if ((int) $configParts[$groupId][0]->quantity > 1) {
                    $partName = $configParts[$groupId][0]->quantity . ' x ' . $partName;
                }

                $offerDescription[] = $groupsName ? $groupData->title . ': ' . $partName : $partName;
            }
        }

        return implode($divider, $offerDescription);
    }

    /**
     * Set query condition for tags
     *
     * @param $queryData
     *
     * @param $data
     *
     * @since 2.0
     */
    protected function _setTagsCondition(&$queryData, $data)
    {
        $tags = (array) $data->get('tags');

        if (count($tags)) {
            $tagData      = [];
            $typeAlias    = HP_OPTION . '.product';
            $tagsFindType = ctype_digit($tags[0]) ? 'tag_id' : 'value';

            foreach ($tags as $_tag) {
                if ($tagsFindType === 'tag_id') {
                    $tagData[] = (int) $_tag;
                } else {
                    $tagData[] = $this->_db->quote((string) $_tag);
                }
            }

            $query = $this->_db
                ->getQuery(true)
                ->select(['DISTINCT b.content_item_id'])
                ->join('LEFT', $this->_db->quoteName('#__contentitem_tag_map', 'b') . ' ON a.id = b.tag_id')
                ->from($this->_db->quoteName('#__tags', 'a'));

            if ($tagsFindType === 'tag_id') {
                $query->where($this->_db->quoteName('a.id') . ' IN (' . implode(', ', $tagData) . ')');
            } elseif ($tagsFindType === 'value') {
                $query->where([
                    $this->_db->quoteName('b.type_alias')  . ' = ' . $this->_db->quote($typeAlias),
                    $this->_db->quoteName('a.title')       . ' IN (' . implode(', ', $tagData) . ')'
                ]);
            }

            $queryData['conditions'][] = $this->_db->quoteName('a.id') . ' IN(' . $query->__toString() . ')';
        }
    }

    /**
     * Set query condition for config
     *
     * @param $queryData
     * @param $data
     * @param string $context
     *
     * @since 2.0
     */
    protected function _setConfigCondition(&$queryData, $data, $context = HP_OPTION . '.part')
    {
        $config = (array) $data->get('config');

        if (empty($config)) {
            return;
        }

        $configData = [];

        foreach ($config as $_config) {
            $configFindType = ctype_digit($_config) ? 'part_id' : 'value';

            $configData[$configFindType] ??= [];
            $configData[$configFindType][] = $_config;
        }

        $configConditions = [];
        foreach ($configData as $type => $values) {
            foreach ($values as $value) {
                $value = $this->_db->quote(trim($value));

                if ($type === 'part_id') {
                    $configConditions[] = $this->_db->quoteName('a.part_id') . ' = ' . $value;
                } elseif ($type === 'value') {
                    $configConditions[] = $this->_db->quoteName('a.value') . ' LIKE ' . $value;
                }
            }
        }

        $queryConfig = $this->_db
            ->getQuery(true)
            ->select(['a.product_id'])
            ->from($this->_db->qn(HP_TABLE_PRODUCTS_CONFIG_VALUES, 'a'))
            ->where($this->_db->qn('a.context') . ' = ' . $this->_db->q($context))
            ->where($this->_db->qn('a.stock_id') . ' IS NULL')
            ->where(join(' OR ', $configConditions));

        $queryData['conditions'][] = $this->_db->qn('a.id') . ' IN (' . $queryConfig->__toString() . ')';
    }

    /**
     * Set query condition for games
     *
     * @param $queryData
     *
     * @param $data
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws \JBZoo\Utils\Exception
     *
     * @since 2.0
     */
    protected function _setGamesCondition(&$queryData, $data)
    {
        $game    = $data->get('game') ? $data->get('game')[0] : null;
        $gameIds = [];

        if ($game) {
            list($gameAlias, ) = explode('@', $game);
            $this->hyper['helper']['game']->setDefaultGame($gameAlias);
            $gameIds = $this->findByGame($game, $queryData['order'], true);
        }

        if ($gameIds) {
            $queryData['conditions'][] = $this->_db->quoteName('a.id') . ' IN(' . implode(',', $gameIds) . ')';
        }
    }
}
