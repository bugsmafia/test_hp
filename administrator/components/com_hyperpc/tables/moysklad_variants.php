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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;
use HYPERPC\Money\Type\Money;

/**
 * Class HyperPcTableMoysklad_Variants
 *
 * @since   2.0
 */
class HyperPcTableMoysklad_Variants extends Table
{

    /**
     * HyperPcTableMoysklad_Variants constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_MOYSKLAD_VARIANTS, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Overloaded bind function.
     *
     * @param   array|object $array
     * @param   string $ignore
     * @return  bool
     *
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        if (key_exists('images', $array)) {
            $images = new JSON($array['images']);
            $array['images'] = $images->write();
        } else {
            $array['images'] = '{}';
        }

        if (key_exists('review', $array)) {
            $review = new JSON($array['review']);
            $array['review'] = $review->write();
        } else {
            $array['review'] = '{}';
        }

        if (!key_exists('description', $array)) {
            $array['description'] = '';
        }

        if (isset($array['list_price']) && $array['list_price'] instanceof Money) {
            $array['list_price'] = $array['list_price']->val();
        }

        if (isset($array['sale_price']) && $array['sale_price'] instanceof Money) {
            $array['sale_price'] = $array['sale_price']->val();
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Override check function.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function check()
    {
        $this->alias = trim($this->alias);
        if ($this->alias === '') {
            $this->alias = $this->uuid;
        }

        return true;
    }
}
