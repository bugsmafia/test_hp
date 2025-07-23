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

namespace HYPERPC\MoySklad;

use MoySklad\ApiClient as BaseApiClient;
use HYPERPC\MoySklad\Client\EntityClient;

/**
 * Class ApiClient
 *
 * @package HYPERPC\MoySklad
 *
 * @since 2.0
 */
class ApiClient extends BaseApiClient
{

    /**
     * @return EntityClient
     */
    public function entity(): EntityClient
    {
        return new EntityClient($this);
    }
}
