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

use HYPERPC\Elements\Manager;
use Joomla\CMS\Http\HttpFactory;
use HYPERPC\Object\Tabby\Models\Webhook;

/**
 * Class TabbyhHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class TabbyHelper extends AppHelper
{
    private const API_URL = 'https://api.tabby.ai';

    private const API_ENDPOINT_WEBHOOKS = '/api/v1/webhooks';

    private const MERCHANT_CODE = 'UAE';

    private string $publicKey = '';
    private string $secretKey = '';

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $elementsManager = Manager::getInstance();
        $tabbyElement = $elementsManager->getElement('payment', 'tabby');
        if ($tabbyElement instanceof \ElementPaymentTabby) {
            $this->publicKey = $tabbyElement->getConfig('api_public_key', '');
            $this->secretKey = $tabbyElement->getConfig('api_secret_key', '');
        }
    }

    /**
     * Registers or updates webhook in Tabby
     *
     * @param   string $actionUrl
     * @param   bool $isTest
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function registerWebhook(string $actionUrl, bool $isTest)
    {
        $apiUrl = self::API_URL . self::API_ENDPOINT_WEBHOOKS;
        $http = HttpFactory::getHttp([], 'curl');

        $headers = [
            'Content-Type' => 'application/json',
            'X-Merchant-Code' => self::MERCHANT_CODE,
            'Authorization' => 'Bearer ' . $this->secretKey
        ];

        $listResponse = $http->get($apiUrl, $headers);

        if ($listResponse->code !== 200) {
            $result = json_decode($listResponse->body);
            $message = $result->error ?? '';
            throw new \Exception($message, $listResponse->code);
        }

        $webhookId = '';

        $webhooks = json_decode($listResponse->body, true);
        foreach ($webhooks as $webhookData) {
            $webhook = new Webhook($webhookData);
            if ($webhook->url === $actionUrl) {
                $webhookId = $webhook->id;
                if ($webhook->is_test !== $isTest) {
                    $webhook->is_test = $isTest;
                    $updateResponse = $http->put(
                        $apiUrl . '/' . $webhookId,
                        json_encode($webhook->toArray()),
                        $headers
                    );

                    if ($updateResponse->code !== 200) {
                        $result = json_decode($updateResponse->body);
                        $message = $result->error ?? '';
                        throw new \Exception($message, $updateResponse->code);
                    }
                }
                break;
            }
        }

        if (!empty($webhookId)) {
            return $webhookId;
        }

        $registerResponse = $http->post(
            $apiUrl,
            json_encode([
                'url' => $actionUrl,
                'is_test' => $isTest
            ]),
            $headers
        );

        if ($registerResponse->code !== 200) {
            $result = json_decode($registerResponse->body);
            $message = $result->error ?? '';
            throw new \Exception($message, $registerResponse->code);
        }

        $webhook = new Webhook(json_decode($registerResponse->body, true));

        return $webhook->id;
    }
}
