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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CalltouchHelper;
use HYPERPC\Elements\ElementOrderHook;

/**
 * Class ElementOrderHookCalltouch
 */
class ElementOrderHookCalltouch extends ElementOrderHook
{
    /**
     * Hook action.
     *
     * @return  void
     */
    public function hook()
    {
        $order = $this->_getOrder();

        $name       = $order->getBuyer();
        $phone      = $order->getBuyerPhone();
        $email      = $order->getBuyerEmail();
        $requestUrl = Uri::root() . ltrim($this->hyper['helper']['cart']->getUrl(), '/');
        $subject    = Text::sprintf('COM_HYPERPC_ORDER_NUMBER', $order->id);

        try {
            /** @var CalltouchHelper */
            $calltouchHelper = $this->hyper['helper']['calltouch'];
        } catch (\Exception $th) {
            return;
        }

        $calltouchHelper->registerCall($name, $phone, $email, $subject, $requestUrl);
    }
}
