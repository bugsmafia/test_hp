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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Object\Delivery;

use Spatie\DataTransferObject\DataTransferObject;

class DeliveryOptions extends DataTransferObject
{
    /**
     * To door delivery type data
     */
    public TariffDataCollection $todoor;

    /**
     * Self pickup delivery type data
     */
    public TariffDataCollection $pickup;

    /**
     * Post delivery type data
     */
    public TariffDataCollection $post;

    /**
     * Create from array
     */
    public static function fromArray(array $params): self
    {
        foreach ($params as $key => $paramData) {
            $result[$key] = TariffDataCollection::create($paramData);
        }

        return new self($result);
    }
}
