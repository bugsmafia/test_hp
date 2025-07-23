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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Money\Type\Money;
use HYPERPC\Elements\ElementProductService;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * Class ElementProductServicePercentProductPrice
 *
 * @since   2.0
 */
class ElementProductServicePercentProductPrice extends ElementProductService
{

    /**
     * Process price action.
     *
     * @param   MoyskladService $service
     * @param   array           $productParts
     *
     * @return  Money
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function processPrice($service, $productParts)
    {
        /** @var Money $totalPercentPrice */
        $totalPercentPrice = $this->hyper['helper']['money']->get();

        $serviceType = $service->getServiceElement();

        $copyProductParts = $productParts;
        // remove from array all services and detached parts
        foreach ($copyProductParts as $groupId => $partGroup) {
            foreach ($partGroup as $key => $item) {
                if (!$item instanceof MoyskladPart || $item->isDetached()) {
                    unset($copyProductParts[$groupId][$key]);
                }
            }

            if (empty($copyProductParts[$groupId])) {
                unset($copyProductParts[$groupId]);
            }
        }

        foreach ($copyProductParts as $groupParts) {
            /** @var PartMarker $groupPart */
            foreach ($groupParts as $groupPart) {
                if ($groupPart->option instanceof OptionMarker) {
                    $totalPercentPrice->add($groupPart->option->getListPrice()->val() * $groupPart->quantity);
                } else {
                    $totalPercentPrice->add($groupPart->getQuantityPrice(false));
                }
            }
        }

        $price = $totalPercentPrice->division(100)->multiply($service->getListPrice())->val();

        return $totalPercentPrice->set(ceil($price));
    }
}
