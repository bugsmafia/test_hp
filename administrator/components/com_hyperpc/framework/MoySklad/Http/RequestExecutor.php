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

namespace HYPERPC\MoySklad\Http;

use MoySklad\ApiClient;
use HYPERPC\MoySklad\Util\Serializer\SerializerInstance;
use MoySklad\Http\RequestExecutor as BaseRequestExecutor;

/**
 * Class RequestExecutor
 *
 * @package HYPERPC\MoySklad\Http
 *
 * @since   2.0
 */
class RequestExecutor extends BaseRequestExecutor
{
    /**
     * @param   ApiClient $api
     * @param   string $path
     *
     * @return  RequestExecutor
     */
    public static function path(ApiClient $api, string $path): self
    {
        $instance = parent::path($api, $path);
        $instance->serializer = SerializerInstance::getInstance();
        $instance->header('Accept-Encoding', 'gzip');

        return $instance;
    }

    /**
     * @param   ApiClient $api
     * @param   string $url
     *
     * @return  RequestExecutor
     */
    public static function url(ApiClient $api, string $url): self
    {
        $instance = parent::path($api, $url, static::TYPE_URL);
        $instance->serializer = SerializerInstance::getInstance();
        $instance->header('Accept-Encoding', 'gzip');

        return $instance;
    }
}
