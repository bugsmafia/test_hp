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
 */

use HYPERPC\Data\JSON;
use HYPERPC\ORM\Table\Table;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcTablePromo_Codes
 *
 * @since 2.0
 */
class HyperPcTablePromo_Codes extends Table
{

    /**
     * HyperPcTablePromo_Codes constructor.
     *
     * @param   \JDatabaseDriver $db
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(\JDatabaseDriver $db)
    {
        parent::__construct(HP_TABLE_PROMO_CODES, HP_TABLE_PRIMARY_KEY, $db);
    }

    /**
     * Overloaded bind function.
     *
     * @param   array|object    $array
     * @param   string          $ignore
     *
     * @return  bool
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    public function bind($array, $ignore = '')
    {
        if (array_key_exists('parts', $array)) {
            $review = new JSON($array['parts']);
            $array['parts'] = $review->write();
        } else {
            $array['parts'] = '{}';
        }

        if (array_key_exists('products', $array)) {
            $review = new JSON($array['products']);
            $array['products'] = $review->write();
        } else {
            $array['products'] = '{}';
        }

        if (array_key_exists('positions', $array)) {
            $review = new JSON($array['positions']);
            $array['positions'] = $review->write();
        } else {
            $array['positions'] = '{}';
        }

        return parent::bind($array, $ignore);
    }
}
