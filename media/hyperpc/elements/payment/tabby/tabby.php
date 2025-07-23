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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Elements\ElementPayment;

/**
 * Class ElementPaymentTabby
 *
 * @since   2.0
 */
class ElementPaymentTabby extends ElementPayment
{
    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();

        $this->registerAction('webhook');
    }

    /**
     * Get edit html params.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getEditParams()
    {
        $webhookId = $this->getConfig('webhook_id');
        $publicKey = $this->getConfig('api_public_key');
        $secretKey = $this->getConfig('api_secret_key');

        if (empty($webhookId) && !empty($publicKey) && !empty($secretKey)) {
            $webhookId = $this->_registerWebhook();

            $this->_config->set('webhook_id', $webhookId);
        }

        return parent::getEditParams();
    }

    /**
     * Handles an incoming webhook
     *
     * @since   2.0
     */
    public function webhook()
    {
        $json = file_get_contents('php://input');

        $this->hyper->log(
            $json,
            Log::INFO,
            'tabby/' . date('Y/m/d') . '/log.php'
        );

        $this->hyper['cms']->close();
    }

    /**
     * Register webhook in Tabby
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _registerWebhook()
    {
        $group = $this->getGroup();
        $identifier = $this->getIdentifier();

        $route = [
            'option'     => HP_OPTION,
            'task'       => 'elements.call',
            'group'      => $group,
            'identifier' => $identifier,
            'action'     => 'webhook'
        ];

        $actionUrl = Uri::root() . 'index.php?' . Uri::buildQuery($route);

        $isTest = $this->getConfig('is_test', false, 'bool');

        try {
            $webhookId = $this->hyper['helper']['tabby']->registerWebhook($actionUrl, $isTest);
        } catch (\Throwable $th) {
            $this->hyper['cms']->enqueueMessage("Element {$group}.{$identifier} " . $th->getMessage(), 'error');
            return '';
        }

        return $webhookId;
    }
}
