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

namespace HYPERPC\Object\Tabby\Models;

use HYPERPC\Object\Tabby\Models\Webhook\Header;
use Spatie\DataTransferObject\DataTransferObject;

class Webhook extends DataTransferObject
{
    /**
     * Unique webhook ID, assigned by Tabby.
     */
    public string $id;

    /**
     * HTTPS endpoint for notifications.
     */
    public string $url;

    /**
     * Indicates whether to use this hook in test environment or not.
     */
    public bool $is_test;

    /**
     * Header
     */
    public ?Header $header;
}
