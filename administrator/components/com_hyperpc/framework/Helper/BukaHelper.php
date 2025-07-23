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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

namespace HYPERPC\Helper;

use HYPERPC\Data\JSON;

/**
 * Class BukaHelper
 *
 * @package     HYPERPC\Helper
 *
 * @since       2.0
 */
class BukaHelper extends AppHelper
{
    const FUNCTION_CATALOG     = 'catalog';
    const FUNCTION_INFORMATION = 'information';
    const FUNCTION_ORDER       = 'order';

    const PARAM_FULL     = 'full';
    const PARAM_UPDATE   = 'update';
    const PARAM_MAKE     = 'make';
    const PARAM_COMPLETE = 'complete';

    private $url         = 'https://partners.buka.ru/protocol',
            $email       = 'mv@hyperpc.ru',
            $private_key = 'private_key.pem',
            $public_key  = 'public_key.pem',
            $passphrase,
            $orderNumber;

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

        $this->private_key = $this->keyGeneration(
            $this->private_key,
            $this->public_key,
            $this->passphrase
        );
    }

    /**
     * Get a complete product catalog.
     *
     * @param   null|int $page >=1
     *
     * @return  string
     *
     * @throws  Exception
     */
    public function full($page = null)
    {
        $data = [
            'function' => self::FUNCTION_CATALOG,
            'param'    => self::PARAM_FULL,
        ];

        if ($page) {
            $data['page'] = $page;
        }

        return $this->sendCurl($data);
    }

    /**
     * Get catalog updates.
     *
     * @param   int $productId
     *
     * @param   null|string $dateLastUpdate "YYYY-MM-DD"
     *
     * @return  string
     *
     * @throws  Exception
     */
    public function update($productId = null, $dateLastUpdate = null)
    {
        $data = [
            'function' => self::FUNCTION_CATALOG,
            'param'    => self::PARAM_UPDATE,
        ];
        if ($productId) {
            $data['id'] = $productId;
        } elseif ($dateLastUpdate) {
            $data['date_update'] = $dateLastUpdate;
        }

        return $this->sendCurl($data);
    }

    /**
     * Create order
     *
     * @param   string $orderNumber
     *
     * @param   array $products
     *
     * @return  string
     *
     * @throws  Exception
     */
    public function make($orderNumber, $products)
    {
        $data = [
            'function' => self::FUNCTION_ORDER,
            'param'    => self::PARAM_MAKE,
            'order_id' => $orderNumber,
            'items'    => $products,
        ];
        $this->orderNumber = $orderNumber;
        return $this->sendCurl($data);
    }

    /**
     * Complete order
     *
     * @param   string $orderNumber
     *
     * @return  string
     *
     * @throws  Exception
     */
    public function complete($orderNumber = '')
    {
        $data = [
            'function' => self::FUNCTION_ORDER,
            'param'    => self::PARAM_COMPLETE,
            'order_id' => ($orderNumber ?: $this->orderNumber),
        ];
        return $this->sendCurl($data);
    }

    /**
     * Get product information
     *
     * @param   int $productId
     *
     * @return  string
     *
     * @throws  Exception
     */
    public function information($productId)
    {
        $data = [
            'function' => self::FUNCTION_INFORMATION,
            'param'    => $productId,
        ];
        return $this->sendCurl($data);
    }

    /**
     * Send curl
     *
     * @param   array $data
     *
     * @return  string
     *
     * @throws  Exception
     */
    private function sendCurl($data)
    {
        $data['username'] = $this->email;
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->parametersForRequest($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($curl);
        curl_close($curl);

        return new JSON($this->decrypt($output));
    }

    /**
     * @param   string $data
     *
     * @return  string
     */
    public function generateSignature($data)
    {
        openssl_sign($data, $signature, $this->private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * @param   array $data
     *
     * @return  array
     */
    public function parametersForRequest(array $data)
    {
        $jsonParam = base64_encode(json_encode($data));
        return [
            'json' => $jsonParam,
            'signature' => $this->generateSignature($jsonParam),
        ];
    }

    /**
     * @param   string $encryptString
     *
     * @return  string
     *
     * @throws  Exception
     */
    private function decrypt($encryptString)
    {
        $chunkSize = ceil(1024 / 8);
        $output = '';
        while ($encryptString)
        {
            $chunk = substr($encryptString, 0, $chunkSize);
            $encryptString = substr($encryptString, $chunkSize);
            if (!openssl_private_decrypt($chunk, $decrypted, $this->private_key))
            {
                throw new Exception('Failed to decrypt data');
            }
            $output .= $decrypted;
        }
        return gzuncompress($output);
    }

    /**
     * @param   string $privateKey
     *
     * @param   string $publicKey
     *
     * @param   string $passphrase
     *
     * @return  mixed
     */
    private function keyGeneration($privateKey, $publicKey, $passphrase)
    {
        if(file_exists($privateKey))
        {
            $privateKeyResource = openssl_pkey_get_private('file://' . $_SERVER['DOCUMENT_ROOT'] . '/' . $privateKey);
            openssl_pkey_export($privateKeyResource, $privateKey);

            return $privateKey;
        }
        $config = array(
            "digest_alg" => "sha1",
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $res = openssl_pkey_new($config);
        openssl_pkey_export_to_file($res, $privateKey, $passphrase);
        openssl_pkey_export($res, $privateKey);

        $pubKey = openssl_pkey_get_details($res);
        file_put_contents($publicKey, $pubKey['key']);

        return $privateKey;
    }


}
