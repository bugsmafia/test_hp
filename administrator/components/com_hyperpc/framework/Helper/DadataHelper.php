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

use Joomla\CMS\Http\HttpFactory;

/**
 * Class DadataHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class DadataHelper extends AppHelper
{
    const API_URL = 'https://dadata.ru/api/v2/clean';

    /**
     * API auth key.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_apiKey;

    /**
     * API secret.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_apiSecret;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->_apiKey    = $this->hyper['params']->get('dadata_api_key');
        $this->_apiSecret = $this->hyper['params']->get('dadata_secret_key');
    }

    /**
     * Standardize data by type.
     *
     * @param   string $type
     * @param   string $data
     * @return  void
     *
     * @since   2.0
     */
    public function clean($type, $data)
    {
        $requestData = array($data);
        return $this->_executeRequest(self::API_URL . '/' . $type, $requestData);
    }

    /**
     * Standardize address.
     *
     * @param   string $data
     * @return  void
     *
     * @since   2.0
     */
    public function cleanAddress($data)
    {
        return $this->clean('address', $data);
    }

    /**
     * Standardize record.
     *
     * @param   array $structure
     * @param   array $data
     * @return  void
     *
     * @since   2.0
     */
    public function cleanRecord($structure, $record) {
        $requestData = array(
          "structure" => $structure,
          "data" => array($record)
        );
        return $this->_executeRequest(self::API_URL, $requestData);
    }

    /**
     * Execute request.
     *
     * @param   string $url
     * @param   array $requestData
     * @return  void
     *
     * @since   2.0
     */
    protected function _executeRequest($url, $requestData)
    {
        $http = HttpFactory::getHttp([], 'curl');

        $response = $http->post(
            $url,
            json_encode($requestData),
            [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Token ' . $this->_apiKey,
                'X-Secret'      => $this->_apiSecret,
            ]
        );

        if ($response->code == 200) {
            return json_decode($response->body);
        } else {
            return 'error ' . $response->code;
        }
    }

}
