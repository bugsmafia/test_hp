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

use HYPERPC\App;
use Joomla\CMS\Log\Log;
use HYPERPC\ORM\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Http\HttpFactory;
use xPaw\SourceQuery\SourceQuery;
use HYPERPC\Helper\Traits\LoadAssets;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Object\Microtransaction\ServerConnectionData;

/**
 * Class MicrotransactionHelper
 *
 * @package     HYPERPC\Helper
 *
 * @property    \HyperPcTableMicrotransactions $_table
 *
 * @since       2.0
 */
class MicrotransactionHelper extends EntityContext
{

    use LoadAssets;

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
        $table = Table::getInstance('Microtransactions');
        $this->setTable($table);

        parent::initialize();
    }

    /**
     * Build a query string for the game server to activate the privilege
     *
     * @param   string $requestTemplate
     * @param   string $purchaseKey
     * @param   string $user
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function buildRequestData($requestTemplate, $purchaseKey, $user)
    {
        /** @var MacrosHelper */
        $macros = $this->hyper['helper']['macros'];

        $request = $macros
                    ->setData(['user' => $user])
                    ->text($requestTemplate);

        $requestParams = [];
        foreach (explode('/', $purchaseKey) as $param) {
            list($key, $value) = explode(':', $param);
            $requestParams[$key] = $value;
        }

        preg_match_all('/{([a-zA-Z]+)[\}|\[]/', $request, $keysMatches);
        list(,$keys) = $keysMatches;

        preg_match_all('/\{([a-zA-Z]+(\[\d{1}\])?)\}/', $request, $indexMatches);
        list(,,$indexes) = $indexMatches;

        $macrosData = [];
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];
            $value = $requestParams[$key];
            if (!empty($indexes[$i])) {
                $values = explode('&', $requestParams[$key]);
                $targetIndex = str_replace(['[', ']'], '', $indexes[$i]);
                $value = $values[$targetIndex] ?? '';
                $key .= $indexes[$i];
            }
            $macrosData[$key] = $value;
        }

        $request = $macros
                    ->setData($macrosData)
                    ->text($request);

        return $request;
    }

    /**
     * Send command to game server
     *
     * @param   string $requestType (post|rcon)
     * @param   ServerConnectionData $connectionData
     * @param   string $command
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function sendServerCommand($requestType, ServerConnectionData $connectionData, $command)
    {
        $result = false;
        switch (strtolower($requestType)) {
            case 'rcon':
                $query = new SourceQuery();
                try {
                    $query->Connect($connectionData->host, $connectionData->port);
                    $query->SetRconPassword($connectionData->password);

                    $serverAnswer = $query->Rcon($command);

                    if (strpos($serverAnswer, 'L ') === 0) { // success answer for cs:go server
                        $result = true;
                        $this->log($serverAnswer);
                    } else {
                        $this->log($serverAnswer, Log::ERROR);
                    }
                } catch (\Exception $e) {
                    $this->log($e->getMessage(), Log::WARNING);
                } finally {
                    $query->Disconnect();
                }
                break;
            case 'post':
                $http = HttpFactory::getHttp([], 'curl');

                $requestUrl = "{$connectionData->host}:{$connectionData->port}";
                if (!empty($connectionData->route)) {
                    $requestUrl .= '/' . ltrim($connectionData->route, '/');
                }

                try {
                    $response = $http->post(
                        $requestUrl,
                        $command,
                        [
                            'Authorization' => 'Basic '.base64_encode($connectionData->login.':'.$connectionData->password),
                            'Content-Type'  => 'application/json'
                        ]
                    );

                    if ($response->code === 200) {
                        $data = json_decode($response->body);
                        $status = $data->status ?? false;
                        $result = $status === true || $status === 'OK'; // success answer for minecraft server
                        $this->log("{$command}: {$response->body}");
                    } else {
                        $this->log("{$response->code}: {$response->body}", Log::WARNING);
                    }
                } catch (\Exception $e) {
                    $this->log($e->getMessage(), Log::WARNING);
                }
                break;
        }

        return $result;
    }

    /**
     * Write file log.
     *
     * @param   string $msg
     * @param   int|null $priority
     *
     * @return  void
     *
     * @since 2.0
     */
    public function log($msg, $priority = Log::INFO)
    {
        $this->hyper->log(
            $msg,
            $priority,
            'microtransactions/' . date('Y/m/d') . '/log.php'
        );
    }

    /**
     * Get Minecraft players online count
     *
     * @return  Registry
     *
     * @since   2.0
     */
    public static function getMinecraftServersOnline()
    {
        $http = HttpFactory::getHttp([], 'curl');
        $requestUrl = '51.250.48.209:8800/status';

        $hp = App::getInstance();

        try {
            $response = $http->get($requestUrl, ['Content-Type' => 'application/json']);

            if ($response->code === 200) {
                $data = new Registry(json_decode($response->body));
                return $data;
            } else {
                $hp->log(
                    "Servers online: {$response->code} - {$response->body}",
                    Log::WARNING,
                    'microtransactions/' . date('Y/m/d') . '/log.php'
                );
            }
        } catch (\Exception $e) {
            $hp->log(
                'Servers online: ' . $e->getMessage(),
                Log::WARNING,
                'microtransactions/' . date('Y/m/d') . '/log.php'
            );
        }

        return new Registry();
    }
}
