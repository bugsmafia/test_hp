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

/**
 * Class CalltouchHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class CalltouchHelper extends AppHelper
{
    private const MANAGER_TAG = 'Manager';

    private $_isManager;
    private $_sessionId;
    private $_apiUrl;

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        $cookie = $this->hyper['input']->cookie;

        $this->_isManager = $cookie->get(HP_COOKIE_HMP);

        $this->_sessionId = $this->_isManager ? null : $cookie->get('_ct_session_id');

        $siteId = $this->hyper['params']->get('calltouch_site_id');
        if (empty($siteId)) {
            throw new \Exception('Failed to initialize CalltouchHelper. Site id not set.', 500);
        }

        $this->_apiUrl = "https://api.calltouch.ru/calls-service/RestAPI/requests/{$siteId}/register/";
    }
    
    /**
     * Send form data
     *
     * @param   string $name
     * @param   string $phone
     * @param   string $email
     * @param   string $subject
     * @param   ?string $requestUrl
     *
     * @return  void
     *
     * @since   2.0
     */
    public function registerCall(string $name, string $phone, string $email, string $subject, ?string $requestUrl = null)
    {
        $clearedPhone = (string) $this->hyper['helper']['string']->clearMobilePhone($phone);
        $phone = strlen($clearedPhone) >= 10 ? $phone : null;

        $http = curl_init();
        curl_setopt($http, CURLOPT_HTTPHEADER, ['Content-type: application/x-www-form-urlencoded;charset=utf-8']);
        curl_setopt($http, CURLOPT_URL, $this->_apiUrl);
        curl_setopt($http, CURLOPT_POST, 1);
        curl_setopt(
            $http,
            CURLOPT_POSTFIELDS,
            'fio=' . urlencode($name) .
            (strlen($clearedPhone) >= 10 ? '&phoneNumber=' . $phone : '') .
            '&email=' . $email .
            '&subject=' . urlencode($subject) .
            (!empty($requestUrl) ? '&requestUrl=' . $requestUrl : '') .
            ($this->_sessionId && $email !== 'save@hyperpc.ru' ? '&sessionId=' . $this->_sessionId : '') .
            ($this->_isManager || $email === 'save@hyperpc.ru' ? '&tags=' . self::MANAGER_TAG : '')
        );
        curl_setopt($http, CURLOPT_RETURNTRANSFER, true);
        curl_exec($http);
        curl_close($http);
    }
}
