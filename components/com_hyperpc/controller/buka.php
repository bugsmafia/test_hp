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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

use HYPERPC\Data\JSON;
use HYPERPC\Helper\BukaHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcControllerBuka
 *
 * @since       2.0
 */
class HyperPcControllerBuka extends ControllerLegacy
{
    protected $_types = [
        'MESSAGE_CATALOGCHANGE',
        'MESSAGE_PRODUCTAVAILABLE',
        'MESSAGE_PUBLICATIONOFF',
        'MESSAGE_PRODUCTKEY',
        'MESSAGE_CHANGESTOCKS'
    ];

    /**
     * Hold BukaHelper object.
     *
     * @var     BukaHelper
     *
     * @since   2.0
     */
    public $helper;

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->registerTask('getmessage', 'getMessage');

        $this->helper = $this->hyper['helper']['buka'];
    }

    /**
     * Message exchange with Buka Api.
     *
     * @return  void
     *
     * @throws \HYPERPC\Helper\Exception
     * @since   2.0
     */
    public function getMessage()
    {
        $request = new JSON(file_get_contents('php://input'));

        if (!in_array($request->get('type'), $this->_types)) {
            return false;
        }

        switch ($request->get('type')) {
            case 'MESSAGE_CATALOGCHANGE':
                $this->updateCatalog();
                break;
        }

        $this->hyper['cms']->close('OK');
    }

    /**
     * Get full catalog.
     *
     * @return  string
     *
     * @throws \HYPERPC\Helper\Exception
     * @since   2.0
     */
    public function getCatalog()
    {
        return $this->helper->full();
    }

    /**
     * Update catalog.
     *
     * @return  string
     *
     * @throws \HYPERPC\Helper\Exception
     * @since   2.0
     */
    public function updateCatalog()
    {
        return $this->helper->update();
    }

    /**
     * Create order
     *
     * @return  string
     *
     * @throws \HYPERPC\Helper\Exception
     * @since   2.0
     */
    public function createOrder()
    {
        $orderNumber = '23544353';
        $listProducts = [
            [
                'id'                    => 3663,
                'price_retail'          => 299,
                'price_wholesale'       => 199,
                'price_retail_stock'    => 0,
                'price_wholesale_stock' => 0,
            ],
        ];

        return $this->helper->make($orderNumber, $listProducts);
    }

    /**
     * Complete order or get completed order data
     *
     * @return  string
     *
     * @throws \HYPERPC\Helper\Exception
     * @since   2.0
     */
    public function completeOrder()
    {
        return $this->helper->complete();
    }

    /**
     * Get product info
     *
     * @return  string
     *
     * @throws \HYPERPC\Helper\Exception
     * @since   2.0
     */
    public function getProductInfo()
    {
        $productId = 999;

        return $this->helper->information($productId);
    }
}
