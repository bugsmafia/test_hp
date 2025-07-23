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

namespace HYPERPC\Helper\Traits;

use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Filesystem\Folder;

/**
 * Trait CrmOauth
 *
 * @package HYPERPC\Helper\Traits
 *
 * @since   2.0
 */
trait CrmOauth
{

    private $_apiPathAccount = '/api/v4/account';

    /**
     * Get oAuth dir path
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getOauthDirPath();

    /**
     * Get oAuth state hash
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getOauthStateHash();

    /**
     * Get crm site url
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract protected function _getCrmSiteUrl();

    /**
     * Get access token
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getAccessToken()
    {
        $tokensData = $this->_getTokensData();
        $accessToken = $tokensData->get('access_token', '');

        $nowTimestamp = time();
        $accessTokenExpires = $tokensData->get('access_token_expires', $nowTimestamp);
        if ($accessTokenExpires <= $nowTimestamp) {
            $accessToken = $this->_refreshAccessToken();
        }

        return $accessToken;
    }

    /**
     * Get access token by authorization code
     *
     * @param   string $authorizationCode
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function getAccessTokenByCode($authorizationCode)
    {
        try {
            $response = $this->_oauthRequest($authorizationCode, 'authorization_code');
        } catch (\Exception $e) {
            return false;
        }

        $accessToken = $response->get('access_token', '');
        $refreshToken = $response->get('refresh_token', '');
        $expiresIn = (int) $response->get('expires_in', 0);

        return $this->_saveTokensToFile($accessToken, $refreshToken, $expiresIn);
    }

    /**
     * Get client id
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getClientId()
    {
        $clientData = $this->_getClientData();
        return $clientData->get('client_id', '');
    }

    /**
     * Get client secret
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getClientSecret()
    {
        $clientData = $this->_getClientData();
        return $clientData->get('client_secret', '');
    }

    /**
     * Check oAuth state
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function checkOauthState()
    {
        $accessToken = $this->getAccessToken();
        if (empty($accessToken)) {
            return false;
        }

        $http = HttpFactory::getHttp([], 'curl');
        $headers['Authorization'] = 'Bearer ' . $accessToken;

        try {
            $response = $http->get(
                $this->_getCrmSiteUrl() . $this->_apiPathAccount,
                $headers
            );
        } catch (\Throwable $th) {
            return false;
        }

        if ($response->code != 200) {
            return false;
        }

        return true;
    }

    /**
     * Get client file path
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getClientFilePath()
    {
        return Path::clean($this->getOauthDirPath() . 'client.php');
    }

    /**
     * Save secrets
     *
     * @param   string $clientId
     * @param   string $clientSecret
     *
     * @return  void
     *
     * @since   2.0
     */
    public function saveSecrets(string $clientId, string $clientSecret)
    {
        $dirPath = $this->getOauthDirPath();
        $clientFilePath = $this->getClientFilePath();

        if (!Folder::exists($dirPath)) {
            Folder::create($dirPath);
        }

        File::write(Path::clean($dirPath . 'index.html'), '<!DOCTYPE html><title></title>');

        $eol = PHP_EOL;
        File::write(
            $clientFilePath,
            "<?php{$eol}defined('_JEXEC') or die();{$eol}{$eol}return " .
            "['client_id' => '{$clientId}', " .
            "'client_secret' => '{$clientSecret}'];{$eol}"
        );
    }

    /**
     * Get client data
     *
     * @return  Registry
     *
     * @since   2.0
     */
    private function _getClientData()
    {
        static $clientData;

        if (isset($clientData)) {
            return $clientData;
        }

        $clientFilePath = $this->getClientFilePath();
        if (File::exists($clientFilePath)) {
            $clientData = require_once $clientFilePath;
        } else {
            $clientData = [];
        }

        $clientData = new Registry($clientData);

        return $clientData;
    }

    /**
     * Get tokens data
     *
     * @return  Registry
     *
     * @since   2.0
     */
    private function _getTokensData()
    {
        static $tokensData;

        if (isset($tokensData)) {
            return $tokensData;
        }

        $tokensFilePath = $this->_getTokensFilePath();
        if (File::exists($tokensFilePath)) {
            $tokensData = require_once $tokensFilePath;
        } else {
            $tokensData = [];
        }

        $tokensData = new Registry($tokensData);

        return $tokensData;
    }

    /**
     * Get tokens file path
     *
     * @return  string
     *
     * @since   2.0
     */
    private function _getTokensFilePath()
    {
        return Path::clean($this->getOauthDirPath() . 'tokens.php');
    }

    /**
     * OAuth Request
     *
     * @param   string $token
     * @param   string $grantType authorization_code | refresh_token
     *
     * @return  Registry
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \UnexpectedValueException
     *
     * @since   2.0
     */
    private function _oauthRequest(string $token, string $grantType)
    {
        $requestData = [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'grant_type' => $grantType,
            'redirect_uri' => rtrim(Uri::root(), '/'),
        ];

        switch ($grantType) {
            case 'authorization_code':
                $requestData['code'] = $token;
                break;
            case 'refresh_token':
                $requestData['refresh_token'] = $token;
                break;
            default:
                throw new \UnexpectedValueException('Unexpected grant type in ' . __FUNCTION__);
                break;
        }

        $requestUrl = $this->_getCrmSiteUrl() . '/oauth2/access_token';

        $http = HttpFactory::getHttp([], 'curl');
        $http->setOption('userAgent', 'amoCRM-oAuth-client/1.0');

        /** @var Response $response */
        $response = $http->post(
            $requestUrl,
            json_encode($requestData),
            [
                'Content-Type' => 'application/json'
            ]
        );

        if ($response->code < 200 || $response->code > 204) {
            throw new \Exception('Responce code ' . $response->code . ' in ' . __FUNCTION__);
        }

        return new Registry($response->body);
    }

    /**
     * Refresh access token
     *
     * @return  string access token
     *
     * @since   2.0
     */
    private function _refreshAccessToken()
    {
        $tokensData = $this->_getTokensData();
        $accessToken = $tokensData->get('access_token');

        try {
            $response = $this->_oauthRequest($tokensData->get('refresh_token'), 'refresh_token');
        } catch (\Throwable $th) {
            return $accessToken;
        }

        $accessToken = $response->get('access_token', '');
        $refreshToken = $response->get('refresh_token', '');
        $expiresIn = (int) $response->get('expires_in', 0);

        $this->_saveTokensToFile($accessToken, $refreshToken, $expiresIn);

        return $accessToken;
    }

    /**
     * Save tokens to a file
     *
     * @param   string $accessToken
     * @param   string $refreshToken
     * @param   int $expiresIn
     *
     * @return  bool
     *
     * @since   2.0
     */
    private function _saveTokensToFile(string $accessToken, string $refreshToken, int $expiresIn)
    {
        $accessTokenExpires = time() + $expiresIn;
        $refreshTokenExpires = time() + 2592000; // 30 days

        $tokensFilePath = $this->_getTokensFilePath();

        $eol = PHP_EOL;
        return File::write(
            $tokensFilePath,
            "<?php{$eol}defined('_JEXEC') or die();{$eol}{$eol}return " .
            "['access_token' => '{$accessToken}', " .
            "'refresh_token' => '{$refreshToken}', " .
            "'access_token_expires' => {$accessTokenExpires}, " .
            "'refresh_token_expires' => {$refreshTokenExpires}];{$eol}"
        );
    }
}
