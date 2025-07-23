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

namespace HYPERPC\MoySklad\Entity;

use JMS\Serializer\Annotation\Type;
use MoySklad\Entity\WebHook as BaseWebHook;

class WebHook extends BaseWebHook
{
    /**
     * @Type("string")
     */
    public $diffType;
}
