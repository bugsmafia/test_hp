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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Elements\Manager;
use HYPERPC\Elements\ElementPayment;

/**
 * Class ElementPaymentCredit
 *
 * @since   2.0
 */
class ElementPaymentCredit extends ElementPayment
{

    /**
     * Get allowed credit methods.
     *
     * @return  mixed
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMethods()
    {
        static $payments;

        $position = Manager::ELEMENT_TYPE_CREDIT;
        if (!isset($payments[$position])) {
            $payments[$position] = $this->getManager()->getByPosition($position);
        }

        return $payments[$position];
    }
}
