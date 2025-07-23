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

namespace HYPERPC\Object\Microtransaction;

use Spatie\DataTransferObject\DataTransferObject;

class ServerConnectionData extends DataTransferObject
{

    /**
     * Server domen or ip address
     */
    public string $host;

    /**
     * Server port
     */
    public int $port;

    /**
     * Request route (relative to host)
     */
    public ?string $route;

    /**
     * Login
     */
    public ?string $login;

    /**
     * Password
     */
    public string $password;
}
