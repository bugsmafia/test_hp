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

namespace HYPERPC\Helper;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Object\Delivery\GeoCityDataCollection;

/**
 * Class DeliveryHelper
 *
 * @package     HYPERPC\Helper
 *
 * @property    string  $_cityFrom
 *
 * @since       2.0
 */
class DeliveryHelper extends AppHelper
{
    const DELIVERY_API_URL = 'https://delivery.yandex.ru/api/last/';

    const CLIENT_ID         = '56413';
    const SENDER_ID_HYPERPC = '41926';
    const SENDER_ID_EPIX    = '51224';

    const API_METHOD_AUTOCOMPLETE = 'autocomplete';
    const API_KEY_AUTOCOMPLETE    = 'e83f74326ba662a7bf513028a7dca169bc7b28ef95f2d804857f56557620480f';

    /**
     * Get predefined cities with its Yandex geoId and fiasId.
     *
     * @return  GeoCityDataCollection
     *
     * @since   2.0
     *
     * @todo    move to params helper
     */
    public function getPredefinedCities()
    {
        $cities = $this->hyper['params']->get('geo_cities', [], 'arr');

        return GeoCityDataCollection::create($cities);
    }
    
    /**
     * Get location suggestions.
     *
     * @param   string $requestedString
     *
     * @return  object|string
     *
     * @since   2.0
     */
    public function getLocalitySuggestions($requestedString)
    {
        $requestData = [
            'client_id' => self::CLIENT_ID,
            'sender_id' => $this->_getSenderID(),
            'type'      => 'locality',
            'term'      => $requestedString
        ];

        return $this->_createApiRequest($requestData, self::API_METHOD_AUTOCOMPLETE, self::API_KEY_AUTOCOMPLETE);
    }

    /**
     * Generate a secret key.
     *
     * @param   array   $requestData
     * @param   string  $methodKey
     *
     * @return  string
     *
     * @since   2.0
     */
    private function _getSecretKey(array $requestData, $methodKey)
    {
        ksort($requestData);
        $joinedData = join('', $requestData);
        $secretKey  = md5($joinedData . $methodKey);

        return $secretKey;
    }

    /**
     * Create API request.
     *
     * @param   array   $requestData
     * @param   string  $method
     * @param   string  $methodKey
     *
     * @return  object|string
     *
     * @since   2.0
     */
    private function _createApiRequest(array $requestData, $method, $methodKey)
    {
        $requestData['secret_key'] = $this->_getSecretKey($requestData, $methodKey);

        $http = HttpFactory::getHttp([], 'curl');
        $url  = self::DELIVERY_API_URL . $method;

        $response = $http->post(
            $url,
            $requestData,
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        );

        if ($response->code == 200) {
            return json_decode($response->body);
        } else {
            return 'error ' . $response->code;
        }
    }

    /**
     * Get senderId depending on the site context.
     *
     * @return  string
     *
     * @since   2.0
     */
    private function _getSenderID()
    {
        $senderId    = '';
        $siteContext = $this->hyper['params']->get('site_context', HP_CONTEXT_HYPERPC);
        switch ($siteContext) {
            case HP_CONTEXT_HYPERPC:
                $senderId = self::SENDER_ID_HYPERPC;
                break;
            case HP_CONTEXT_EPIX:
                $senderId = self::SENDER_ID_EPIX;
                break;
        }

        return $senderId;
    }
}
