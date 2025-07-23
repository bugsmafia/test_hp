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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Plugin;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Class plgSystemCrm
 */
class plgSystemCrm extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  2.0
     */
    protected $autoloadLanguage = true;

    /**
     * Listener for the `onAfterRoute` event
     *
     * @return  void
     *
     * @since   2.0
     */
    public function onAfterRoute()
    {
        $app = App::getInstance();
        if ($app['cms']->isClient('site')) {
            /** @var Input $input */
            $input = $app['input'];

            $code = $input->get('code');
            $state = $input->get('state');
            $referer = $input->get('referer');
            $platform = $input->get('platform');
            $clientId = $input->get('client_id');

            if (!$code || !$state || !$referer || !$platform || !$clientId) {
                return;
            }

            if ($state !== $app['helper']['crm']->getOauthStateHash()) {
                $app['cms']->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
                return;
            }

            $result = $app['helper']['crm']->getAccessTokenByCode($code);

            $message = $result ? 'PLG_SYSTEM_CRM_OAUTH_TOKEN_SUCCESSFULLY_RECEIVED' : 'PLG_SYSTEM_CRM_OAUTH_TOKEN_NOT_RECEIVED';
            $style = $result ? 'message' : 'error';

            $crmPlugin = new Plugin((array) PluginHelper::getPlugin('system', 'crm'));

            $app['cms']->enqueueMessage(
                Text::sprintf(
                    $message,
                    '/administrator/index.php?option=com_plugins&view=plugin&layout=edit&extension_id=' . $crmPlugin->id
                ),
                $style
            );
        }
    }
}
