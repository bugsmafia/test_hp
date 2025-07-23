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

namespace HYPERPC\Object\SavedConfiguration;

use Joomla\CMS\Date\Date;
use HYPERPC\Money\Type\Money;
use Spatie\DataTransferObject\DataTransferObject;

class CheckData extends DataTransferObject
{
    /**
     * Array of unavalable parts|services
     */
    public array $unavalableParts = [];

    /**
     * Configuration last modified date
     */
    public Date $lastModifiedDate;

    /**
     * Price difference with actual price
     */
    public Money $priceDifference;

    /**
     * Is the configuration in the cart
     */
    public bool $isInCart = false;

    /**
     * Has the configuration any warnings
     */
    public bool $hasWarnings = false;
}
