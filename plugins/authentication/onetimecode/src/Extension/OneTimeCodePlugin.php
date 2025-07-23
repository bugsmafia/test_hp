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

namespace HYPERPC\Plugin\Authentication\OneTimeCode\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * One-time Code Authentication plugin
 */
final class OneTimeCodePlugin extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @param   array   $credentials  Array holding the user credentials
     * @param   array   $options      Array of extra options
     * @param   object  &$response    Authentication response object
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        $app = $this->getApplication();

        // Only on front
        if (!$app->isClient('site')) {
            return;
        }

        $response->type = 'OneTimeCode';

        if (empty($credentials['password'])) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = $app->getLanguage()->_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

            return;
        }

        $sessionToken = $app->getSession()->getToken();
        $timeLimit = Factory::getDate('now -1 hour')->toSql();

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['users.id', 'codes.code']))
            ->from($db->quoteName(HP_TABLE_USER_CODES, 'codes'))
            ->join('LEFT', $db->quoteName('#__users', 'users'), $db->quoteName('users.id') . ' = ' . $db->quoteName('codes.user_id'))
            ->where($db->quoteName('users.username') . ' = :username')
            ->where($db->quoteName('codes.token') . ' = :token')
            ->where($db->quoteName('codes.created_time') . ' > :timelimit')
            ->bind(':username', $credentials['username'])
            ->bind(':token', $sessionToken)
            ->bind(':timelimit', $timeLimit)
            ->order($db->quoteName('codes.id') . ' DESC');


        $db->setQuery($query);
        $result = $db->loadObject();

        if ($result) {
            $verified = base64_encode($credentials['password']) === $result->code;

            if ($verified) {
                $response->status = Authentication::STATUS_SUCCESS;
                return;
            }
        }

        $response->status = Authentication::STATUS_FAILURE;
        $response->error_message = 'JGLOBAL_AUTH_INVALID_PASS';
    }

    /**
     * Delete all codes of the user after successful login.
     *
     * @param   array $event
     *
     * @return  boolean
     */
    public function onUserAfterLogin($options): bool
    {
        $app = $this->getApplication();

        // Only on front, only one-time code response
        if (!$app->isClient('site') || $options['responseType'] !== 'OneTimeCode') {
            return true;
        }

        if (!($options['user'] instanceof User)) {
            return true;
        }

        $userId = $options['user']->id;

        $db = $this->getDatabase();
        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName(HP_TABLE_USER_CODES, 'codes'))
                ->where($db->quoteName('codes.user_id') . ' = :userid')
                ->bind(':userid', $userId);

            $db->setQuery($query)->execute();
        } catch (\Throwable $th) {}

        return true;
    }
}
