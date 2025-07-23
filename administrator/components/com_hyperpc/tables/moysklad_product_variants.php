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

use HYPERPC\ORM\Table\Table;
use HYPERPC\Money\Type\Money;

/**
 * Class HyperPcTableMoysklad_Product_Variants
 *
 * @since   2.0
 */
class HyperPcTableMoysklad_Product_Variants extends Table
{

    /**
     * HyperPcTableMoysklad_Product_Variants constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_MOYSKLAD_PRODUCT_VARIANTS, HP_TABLE_PRIMARY_KEY, $db);

        $this->_autoincrement = false;
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
        if (isset($array['list_price']) && $array['list_price'] instanceof Money) {
            $array['list_price'] = $array['list_price']->val();
        }

        if (isset($array['sale_price']) && $array['sale_price'] instanceof Money) {
            $array['sale_price'] = $array['sale_price']->val();
        }

        return parent::bind($array, $ignore);
    }
}
