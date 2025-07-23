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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Elements\ElementOrderHook;

/**
 * Class ElementOrderHookPromoCode
 *
 * @since   2.0
 */
class ElementOrderHookPromoCode extends ElementOrderHook
{

    /**
     * Hook action.
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @return  void
     *
     * @since   2.0
     */
    public function hook()
    {
        $order = $this->_getOrder();

        $promoCode = $this->hyper['helper']['promocode']->findByCode($order->promo_code);

        if ($promoCode->limit > 0 && $promoCode->used < $promoCode->limit) {
            $promoCode->set('used', $promoCode->used + 1);

            /** @var HyperPcTablePromo_Codes  $table */
            $table = $this->hyper['helper']['promocode']->getTable();
            $table->save($promoCode->getArray());
        }
    }
}
