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

use HYPERPC\Money\Type\Money;
use HYPERPC\Elements\ElementProductService;
use HYPERPC\Joomla\Model\Entity\MoyskladService;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ElementProductServiceCustomPartFixedPrice
 *
 * @since 2.0
 */
class ElementProductServiceCustomPartFixedPrice extends ElementProductService
{

    /**
     * Process price action.
     *
     * @param   MoyskladService $service
     * @param   array           $productParts
     *
     * @return  Money|\JBZoo\SimpleTypes\Type\Money
     *
     * @since   2.0
     */
    public function processPrice($service, $productParts)
    {
        return $service->getListPrice();
    }
}
