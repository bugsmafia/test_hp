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

use HYPERPC\Helper\FacebookHelper;
use HYPERPC\Elements\ElementOrderHook;

/**
 * Class ElementOrderHookFacebookPurchase
 *
 * @since   2.0
 */
class ElementOrderHookFacebookPurchase extends ElementOrderHook
{

    /**
     * Hook action.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function hook()
    {
        $order = $this->_getOrder();

        /** @var FacebookHelper */
        $facebookHelper = $this->hyper['helper']['facebook'];
        try {
            $facebookHelper->purchaseEvent($order);
        } catch (\Throwable $th) {}
    }
}
