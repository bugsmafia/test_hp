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

namespace HYPERPC\Object\Tabby\Models\Webhook;

use Spatie\DataTransferObject\DataTransferObject;

class Header extends DataTransferObject
{
    /**
     * Arbitrary header name to sign the request.
     */
    public string $title = '';

    /**
     * Random string to sign the request.
     */
    public string $value = '';
}
