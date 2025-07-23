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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

namespace HYPERPC\Joomla\Model\Entity;

use HYPERPC\Data\JSON;
use Joomla\CMS\Date\Date;

/**
 * Class PromoCode
 *
 * @package     HYPERPC\Joomla\Model\Entity
 *
 * @since       2.0
 */
class PromoCode extends Entity
{

    const TYPE_SALE_FIXED = 2;
    const TYPE_SALE       = 1;
    const TYPE_GIFT       = 0;

    /**
     * Promo code id.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $id;

    /**
     * Type of promo code.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $type;

    /**
     * Promo code value.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $code;

    /**
     * Promo code description.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $description;

    /**
     * Published flag.
     *
     * @var     bool
     *
     * @since   2.0
     */
    public $published = true;

    /**
     * Code params.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $params;

    /**
     * Items context
     *
     * @var     string
     *
     * @since   2.0
     */
    public $context;

    /**
     * Positions list.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $positions;

    /**
     * Parts list.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $parts;

    /**
     * Products list.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    public $products;

    /**
     * Promo code rate.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $rate;

    /**
     * Promo code limit.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $limit;

    /**
     * Promo code used quantity.
     *
     * @var     int
     *
     * @since   2.0
     */
    public $used;

    /**
     * Promo code date from.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $publish_up;

    /**
     * Promo code date end.
     *
     * @var     string
     *
     * @since   2.0
     */
    public $publish_down;

    /**
     * Get site view category url.
     *
     * @param   array $query
     * @return  null|string
     *
     * @since   2.0
     */
    public function getViewUrl(array $query = [])
    {
        return null;
    }

    /**
     * Get promo code items.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getItems()
    {
        $items = [];

        $positions = array_keys($this->positions->getArrayCopy());
        if (count($positions)) {
            $positionHelper = $this->hyper['helper']['position'];
            $positionList   = $positionHelper->getByIds($positions);

            $positionList = array_map(function ($position) use ($positionHelper) {
                return $positionHelper->expandToSubtype($position);
            }, $positionList);

            $items = array_merge($positionList, $items);
        }

        return $items;
    }

    /**
     * Fields of integer data.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldJsonData()
    {
        return array_merge(['positions'], parent::_getFieldJsonData());
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
        return array_merge(['type'], parent::_getFieldInt());
    }

    /**
     * Fields of datetime.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getFieldDate()
    {
        return ['publish_up', 'publish_down'];
    }

    /**
     * Prepare entity properties.
     *
     * @param   mixed $propName
     * @param   mixed $propValue
     * @return  void
     *
     * @since   2.0
     */
    protected function _prepareData($propName, $propValue)
    {
        parent::_prepareData($propName, $propValue);

        if (in_array($propName, $this->_getFieldDate())) {
            if ($propValue == "0000-00-00 00:00:00") {
                $propValue = null;
            } else {
                $propValue = new Date($propValue);
            }

            $this->set($propName, $propValue);
        }
    }
}
