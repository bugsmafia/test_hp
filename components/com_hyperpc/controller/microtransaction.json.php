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

use Joomla\CMS\Factory;
use Joomla\String\Normalise;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Session\Session;
use HYPERPC\ORM\Entity\Microtransaction;
use HYPERPC\Helper\MicrotransactionHelper;
use HYPERPC\Joomla\Controller\ControllerForm;
use HYPERPC\Object\Microtransaction\ServerConnectionData;

/**
 * Class HyperPcControllerMicrotransaction
 *
 * @since   2.0
 */
class HyperPcControllerMicrotransaction extends ControllerForm
{
    protected const MODULE_EXTENSION = 'mod_hp_microtransaction_form';

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        header('Content-type: application/json');

        $this
            ->registerTask('bank-callback', 'bankCallback')
            ->registerTask('check-payment', 'checkPayment')
            ->registerTask('create', 'create')
            ->registerTask('servers-online', 'serversOnline');
    }

    /**
     * Handle bank callback.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function bankCallback()
    {
        // handle bank callback;
        $this->hyper['cms']->close();
    }

    /**
     * Check payment from sberbank payment button.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function checkPayment()
    {
        $output = new Registry([
            'result' => false
        ]);

        $bankOrderData = $this->hyper['input']->getString('order', '');
        
        if (!is_array($bankOrderData)) {
            $this->hyper['cms']->close(json_encode($output));
        }

        $bankOrderData = new Registry($bankOrderData);

        try {
            $module = $this->_getModule();
        } catch (\Exception $th) {
            $this->hyper['cms']->close(json_encode($output));
        }

        $moduleParams = json_decode($module->get('params', '{}'));
        $productionMode = $moduleParams->production ?? false;
        $tokenPropName = $productionMode ? 'callback_token' : 'callback_token_test';
        $token = $moduleParams->{$tokenPropName} ?? '';

        $concat = join('', [
            $bankOrderData->get('status', ''),
            $bankOrderData->get('formattedAmount', ''),
            $bankOrderData->get('currency', ''),
            $bankOrderData->get('approvalCode', ''),
            $bankOrderData->get('orderNumber', ''),
            $bankOrderData->get('panMasked', ''),
            $bankOrderData->get('refNum', ''),
            $bankOrderData->get('paymentDate', ''),
            $bankOrderData->get('formattedFeeAmount', ''),
            $token,
            ';'
        ]);

        $digest = strtoupper((string) hash_hmac('sha256', $concat, $token));

        $incomingDidgest = $bankOrderData->get('digest');
        if ($digest !== $incomingDidgest) {
            $this->hyper['cms']->close(json_encode($output));
        }

        $output->set('result', true);

        list($orderNumber) = explode('-', (string) $bankOrderData->get('orderNumber', ''));

        /** @var MicrotransactionHelper */
        $microtransactionHelper = $this->hyper['helper']['microtransaction'];

        /** @var Microtransaction */
        $microtransaction = $microtransactionHelper->findById($orderNumber);
        if ($microtransaction->id) {
            $microtransaction->paid = true;

            $requestTemplate = ($moduleParams->server_request ?? '');
            $request = $microtransactionHelper->buildRequestData($requestTemplate, $microtransaction->purchase_key, $microtransaction->player);

            Factory::getLanguage()->load(self::MODULE_EXTENSION, JPATH_BASE . '/modules/' . self::MODULE_EXTENSION);

            $serverConnectionData = $this->_getServerConnectionDataFromPurchaseKey($microtransaction->purchase_key, $moduleParams);
            if (empty($serverConnectionData)) {
                $output->set('message', Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_ACTIVATE_PRIVILAGE'));
            }

            $activated = $microtransactionHelper->sendServerCommand(
                ($moduleParams->server_request_type ?? 'rcon'),
                $serverConnectionData,
                $request
            );

            if (!$activated) {
                $output->set('message', Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_ACTIVATE_PRIVILAGE'));
            }

            $microtransaction->activated = $activated;

            $model = $this->getModel();
            try {
                $model->save($microtransaction->toArray());
            } catch (\Throwable $th) {
                /** @todo log it */
            }
        } else {
            $output->set('message', Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_ACTIVATE_PRIVILAGE'));
        }

        $this->hyper['cms']->close(json_encode($output));
    }

    /**
     * Create microtransaction.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function create()
    {
        $output = new Registry([
            'result' => false
        ]);

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close(json_encode($output));
        };

        $userId = $this->hyper['user']->id;

        if (empty($userId)) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_PLEASE_AUTH'));
            $this->hyper['cms']->close(json_encode($output));
        }

        Factory::getLanguage()->load(self::MODULE_EXTENSION, JPATH_BASE . '/modules/' . self::MODULE_EXTENSION);

        $player = $this->hyper['input']->get('player', '', 'username');
        if (empty(trim($player))) {
            $output->set('message', Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_FIELD_PLAYER_CANNOT_BE_EMPTY'));
            $this->hyper['cms']->close(json_encode($output));
        }

        try {
            $module = $this->_getModule();
        } catch (\Exception $th) {
            $output->set('message', $th->getMessage());
            $this->hyper['cms']->close(json_encode($output));
        }

        $key = $this->hyper['input']->get('key', '', 'url');
        $moduleParams = json_decode($module->get('params', '{}'));

        $serverConnectionData = $this->_getServerConnectionDataFromPurchaseKey($key, $moduleParams);
        if (empty($serverConnectionData)) {
            $output->set('message', Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_PURCHASE_KEY_NOT_FOUND'));
            $this->hyper['cms']->close(json_encode($output));
        }

        $checkPlayer = $this->_checkPlayer($player, $moduleParams, $key);
        if ($checkPlayer === false) {
            $output->set('message', Text::sprintf('MOD_HP_MICROTRANSACTION_FORM_ERROR_USER_NOT_FOUND', $player));
            $this->hyper['cms']->close(json_encode($output));
        }

        /** @var MicrotransactionHelper */
        $microtransactionHelper = $this->hyper['helper']['microtransaction'];
        $db = $microtransactionHelper->getDbo();

        $microtransactions = $microtransactionHelper->findAll([
            'conditions' => [
                $db->qn('a.created_user_id') . ' = ' . $db->q($userId),
                $db->qn('a.purchase_key')  . ' = ' . $db->q($key),
                $db->qn('a.paid')  . ' = ' . $db->q(0),
                $db->qn('a.player')  . ' = ' . $db->q($player)
            ],
            'limit' => 1
        ]);

        if (count($microtransactions)) {
            /** @var Microtransaction */
            $microtransaction = array_shift($microtransactions);

            $output->set('order', $this->_randomizeOrderNumber($microtransaction->id));
            $output->set('price', $microtransaction->total->val());
            $output->set('description', Text::sprintf('MOD_HP_MICROTRANSACTION_FORM_PAYMENT_DESCRIPTION', $microtransaction->description, $microtransaction->player));
            $output->set('result', true);
            $this->hyper['cms']->close(json_encode($output));
        }

        $subjects = (array) ($moduleParams->subjects ?? []);
        $flattenedSubjects = [];
        foreach ($subjects as $subjectsGroup) {
            foreach ($subjectsGroup->subject_group as $subject) {
                $flattenedSubjects[] = $subject;
            }
        }

        $subject = array_filter($flattenedSubjects, function ($subject) use ($key) {
            return $subject->key === $key;
        });
        $subject = array_shift($subject);

        if (empty($subject)) {
            $output->set('message', Text::_('MOD_HP_MICROTRANSACTION_FORM_ERROR_PURCHASE_KEY_NOT_FOUND'));
            $this->hyper['cms']->close(json_encode($output));
        }

        $data = [
            'purchase_key' => $key,
            'description'  => $subject->description,
            'total'        => (float) $subject->price,
            'player'       => $player,
            'module_id'    => $module->get('id')
        ];

        $model = $this->getModel();
        try {
            $model->save($data);
            /** @var Microtransaction */
            $microtransaction = $model->loadFormData();
        } catch (\Throwable $th) {
            $output->set('message', $th->getMessage());
            $this->hyper['cms']->close(json_encode($output));
        }

        $output->set('order', $this->_randomizeOrderNumber($microtransaction->id));
        $output->set('price', $microtransaction->total->val());
        $output->set('description', Text::sprintf('MOD_HP_MICROTRANSACTION_FORM_PAYMENT_DESCRIPTION', $microtransaction->description, $microtransaction->player));

        $output->set('result', true);

        $this->hyper['cms']->close(json_encode($output));
    }

    /**
     * Get servers online users state
     *
     * @return  void
     *
     * @since   2.0
     */
    public function serversOnline()
    {
        $game = Normalise::toCamelCase($this->hyper['input']->getString('game', 'minecraft'));

        /** @var MicrotransactionHelper */
        $microtransactionHelper = $this->hyper['helper']['microtransaction'];
        $method = "get{$game}ServersOnline";

        if (method_exists($microtransactionHelper, $method)) {
            $cache = Factory::getCache();
            $cache->setCaching(1);
            $cache->setLifeTime(1);

            /** @var Registry */
            $serversOnline = $cache->call([get_class($microtransactionHelper), $method]);

            $this->hyper['cms']->close(json_encode($serversOnline));
        }

        $this->hyper['cms']->close(json_encode([]));
    }

    /**
     * Checks if the player exists on the server
     *
     * @param   string $player
     * @param   \stdClass $moduleParams
     * @param   string $purchaseKey
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _checkPlayer($player, $moduleParams, $purchaseKey)
    {
        $requestType = $moduleParams->server_request_type ?? 'rcon';
        $checkPlayer = $moduleParams->player_check ?? false;
        if (strtolower($requestType) === 'post' && $checkPlayer) {
            $checkPlayerConnectionData = $this->_getServerConnectionDataFromPurchaseKey($purchaseKey, $moduleParams);

            $checkPlayerRoute = $moduleParams->player_check_route ?? '';
            $checkPlayerConnectionData->route = $checkPlayerRoute;

            $requestTemplate = $moduleParams->player_check_request ?? '';

            /** @var MicrotransactionHelper */
            $microtransactionHelper = $this->hyper['helper']['microtransaction'];

            $request = $microtransactionHelper->buildRequestData($requestTemplate, $purchaseKey, $player);
            $checkResult = $microtransactionHelper->sendServerCommand(
                'post',
                $checkPlayerConnectionData,
                $request
            );

            return $checkResult;
        }

        return true;
    }

    /**
     * Get module by id from request
     *
     * @return  Registry $module
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _getModule()
    {
        $moduleId = $this->hyper['input']->get('module');
        if (empty($moduleId)) {
            throw new Exception(Text::_('COM_HYPERPC_ERROR_MODULE_NOT_FOUND'));
        }

        $module = new Registry((array) $this->hyper['helper']['module']->findById($moduleId));
        if ($module->get('module') !== self::MODULE_EXTENSION) {
            throw new Exception(Text::_('COM_HYPERPC_ERROR_MODULE_NOT_FOUND'));
        }

        return $module;
    }

    /**
     * Get server connection data from purchase key
     *
     * @param   string $server
     * @param   \stdClass $moduleParams
     *
     * @return  ServerConnectionData|null
     *
     * @since   2.0
     */
    protected function _getServerConnectionDataFromPurchaseKey($purchaseKey, $moduleParams)
    {
        $serverKey = null;
        $segments = explode('/', $purchaseKey);

        foreach ($segments as $segment) {
            $explode = explode(':', $segment);
            if (count($explode) === 2 && $explode[0] === 'server') {
                $serverKey = $explode[1];
                break;
            }
        }

        if (empty($serverKey)) {
            return null;
        }

        $servers = (array) ($moduleParams->servers ?? []);
        foreach ($servers as $data) {
            if ($data->server_key === $serverKey) {
                return new ServerConnectionData([
                    'host' => $data->server_host,
                    'port' => (int) $data->server_port,
                    'route' => $data->server_route,
                    'login' => $data->server_login,
                    'password' => $data->server_password
                ]);
            }
        }

        return null;
    }

    /**
     * Randomize order number
     *
     * @param   string $orderNumber
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _randomizeOrderNumber($orderNumber)
    {
        $randomInt = rand(100, 999);
        return "{$orderNumber}-{$randomInt}";
    }
}
