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

namespace HYPERPC\ORM\Entity;

use HYPERPC\Data\JSON;
use HYPERPC\Money\Type\Money;

/**
 * Class Stock
 *
 * @property    int     $id
 * @property    int     $product_id
 * @property    string  $configuration_id
 * @property    int     $balance
 * @property    Money   $price
 * @property    JSON    $configuration
 * @property    JSON    $params
 *
 * @package     HYPERPC\ORM\Entity
 *
 * @since       2.0
 */
class ProductInStock extends Entity
{

    /**
     * Field list of json type.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_fieldJsonType = [
        'params',
        'configuration',
    ];

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this
            ->setTablePrefix()
            ->setTableType('Products_In_Stock');

        parent::initialize();
    }

    /**
     * Get configuration id.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getConfigurationId()
    {
        preg_match('/-/', $this->configuration_id) ? list(, $id) = explode('-', $this->configuration_id) : $id = $this->configuration_id;
        return $id;
    }

    /**
     * Get admin (backend) edit url.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAdminEditUrl()
    {
    }
}
