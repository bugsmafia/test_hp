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

use HYPERPC\Data\JSON;
use Joomla\CMS\Log\Log;
use HYPERPC\Helper\MoySkladHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerMoysklad
 *
 * @since   2.0
 */
class HyperPcControllerMoysklad extends ControllerLegacy
{

    /**
     * Hold CrmHelper object.
     *
     * @var     MoySkladHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_helper = $this->hyper['helper']['moysklad'];

        $this
            ->registerTask('webhook_create', 'webhookCreateHandler')
            ->registerTask('webhook_update', 'webhookUpdateHandler')
            ->registerTask('webhook_delete', 'webhookDeleteHandler')
            ->registerTask('create_webhook', 'createWebhook')
            ->registerTask('remove_webhook', 'removeWebhook');
    }

    /**
     * Create entity event handler
     *
     * @return  void
     *
     * @since   2.0
     */
    public function webhookCreateHandler()
    {
        $this->_webhookHandler('Create');

        $this->hyper['cms']->close();
    }

    /**
     * Update entity event handler
     *
     * @return  void
     *
     * @since   2.0
     */
    public function webhookUpdateHandler()
    {
        $this->_webhookHandler('Update');

        $this->hyper['cms']->close();
    }

    /**
     * Delete entity event handler
     *
     * @return  void
     *
     * @since   2.0
     */
    public function webhookDeleteHandler()
    {
        $this->_webhookHandler('Delete');

        $this->hyper['cms']->close();
    }

    /**
     * Create webhook
     *
     * @return  void
     *
     * @since 2.0
     */
    public function createWebhook()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON(['result' => false]);

        $entityType = $this->hyper['input']->get('entityType');
        $action = $this->hyper['input']->get('action');
        $url = $this->hyper['input']->get('url', '', 'url');

        try {
            $key = $this->_helper->createWebhook($entityType, $action, $url);
            $output
                ->set('result', true)
                ->set('key', $key);
        } catch (\Throwable $th) {
            $output->set('message', $th->getMessage());
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Remove webhook
     *
     * @return  void
     *
     * @since 2.0
     */
    public function removeWebhook()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON(['result' => false]);

        $webhookToRemove = $this->hyper['input']->get('key');

        try {
            $this->_helper->removeWebhook($webhookToRemove);
            $output->set('result', true);
        } catch (\Throwable $th) {
            $output->set('message', $th->getMessage());
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Webhooks common handler
     *
     * @param   string $action (Create|Update|Delete)
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _webhookHandler($action)
    {
        $json = file_get_contents('php://input');
        $requestData = new JSON(json_decode($json));

        $auditContext = new JSON($requestData->get('auditContext', []));
        if (empty($auditContext)) {
            $this->_helper->log($action . ' warning: empty audit context', Log::WARNING);

            /** @todo Check situations where auditContext may not be in the hook body */

            return;
        }

        $events = $requestData->get('events', []);

        $this->_helper->log(json_encode((array) $requestData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $entities = [];

        foreach ($events as $event) {
            $event = new JSON($event);

            $entityType = $this->_helper->getEntityTypeFromHref($event->find('meta.href', ''));
            $entityUuid = $this->_helper->getEntityUuidFromHref($event->find('meta.href', ''));

            if (!isset($entities[$entityType])) {
                $entities[$entityType] = [];
            }

            $entities[$entityType][] = $entityUuid;

            if ($action === 'Update') {
                $updatedFields = $event->get('updatedFields', []);
            }
        }

        foreach ($entities as $entityType => $uuids) {
            try {
                if ($action === 'Update') {
                    $this->_helper->{$entityType . $action}($uuids, $updatedFields);
                } else {
                    $this->_helper->{$entityType . $action}($uuids);
                }
            } catch (\Throwable $th) {
                $this->_helper->log($entityType . $action . ' throws error: ' . $th->getMessage() . ' in ' . $th->getFile() . ' on line ' . $th->getLine(), Log::ERROR);
            }
        }
    }
}
