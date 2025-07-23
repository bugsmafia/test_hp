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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

namespace HYPERPC;

use JBZoo\Utils\FS;
use JBZoo\Data\Data;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\Event\Event;
use Joomla\CMS\Log\Log;
use JBZoo\Utils\Filter;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Router\Route;
use HYPERPC\Joomla\Factory;
use Detection\MobileDetect;
use HYPERPC\ORM\Entity\User;
use Joomla\CMS\Log\LogEntry;
use JBZoo\Event\EventManager;
use JBZoo\Path\Path as JBPath;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Language\LanguageHelper;
use Pimple\Container as PimpleContainer;
use Joomla\CMS\Component\ComponentHelper;
use JBZoo\Assets\Manager as AssetsManager;
use HYPERPC\Helper\Manager as HelperManager;
use HYPERPC\Joomla\Log\Logger\FormatedTextLogger;

/**
 * Class App
 *
 * @package     HYPERPC
 *
 * @since       2.0
 */
class App extends PimpleContainer
{

    /**
     * The application language object.
     *
     * @var    Language
     * @since  1.7.3
     */
    protected $language;

    /**
     * Constant defining an enqueued info message
     *
     * @var    string
     * @since  4.0.0
     */
    public const MSG_INFO = 'info';

    /**
     * Application Init flag.
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $_isInit = false;

    /**
     * Get site context.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getContext()
    {
        return Str::low($this['params']->get('site_context'));
    }

    /**
     * Method to close the application.
     *
     * @param  integer  $code  The exit code (optional; default is 0).
     *
     * @return  void
     *
     * @codeCoverageIgnore
     * @since   1.0.0
     */
    public function close($code = 0)
    {
        exit($code);
    }

    /**
     * Get application instance.
     *
     * @return  App
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new self();

            /** @todo delete if not need */
//            $instance->initialize();
        }

        return $instance;
    }

    /**
     * Method to get the application language object.
     *
     * @return  Language  The language object
     *
     * @since   1.7.3
     */
    public function getLanguage()
    {
        return $this->language;
    }

    public function initPaths()
    {
        $path = new JBPath();
        $path->setRealPathFlag(false);

        $path->set('cache', JPATH_ROOT . '/cache');
        $path->set('media', JPATH_ROOT . '/media');
        $path->set('site', JPATH_ROOT . '/components/' . HP_OPTION);
        $path->set('admin', JPATH_ROOT . '/administrator/components/' . HP_OPTION);
        $path->set('framework', $path->get('admin:framework'));
        $path->set('assets', [
            JPATH_ROOT . '/media/hyperpc',
            JPATH_ROOT . '/components/' . HP_OPTION . '/assets',
            JPATH_ROOT . '/administrator/components/' . HP_OPTION . '/assets'
        ]);
        $path->set('js', [
            JPATH_ROOT . '/media/hyperpc/js',
            JPATH_ROOT . '/components/' . HP_OPTION . '/assets/js',
            JPATH_ROOT . '/administrator/components/' . HP_OPTION . '/assets/js'
        ]);
        $path->set('css', [
            JPATH_ROOT . '/media/hyperpc/css',
            JPATH_ROOT . '/components/' . HP_OPTION . '/assets/css',
            JPATH_ROOT . '/administrator/components/' . HP_OPTION . '/assets/css'
        ]);
        $path->set('img', [
            JPATH_ROOT . '/media/hyperpc/img',
            JPATH_ROOT . '/components/' . HP_OPTION . '/assets/img',
            JPATH_ROOT . '/administrator/components/' . HP_OPTION . '/assets/img'
        ]);
        $path->set('less', [
            JPATH_ROOT . '/media/hyperpc/less',
            JPATH_ROOT . '/components/' . HP_OPTION . '/assets/less',
            JPATH_ROOT . '/administrator/components/' . HP_OPTION . '/assets/less'
        ]);
        $path->set('renderer', [
            JPATH_ROOT . '/templates/hyperpc/html/com_hyperpc/renderer',
            JPATH_ROOT . '/media/hyperpc/render'
        ]);
        $path->set('printer', [
            $path->get('admin:framework/Printer')
        ]);
        $path->set('modules', [
            JPATH_ROOT . '/modules'
        ]);
        $path->set('layouts', [
            $path->get('admin:layouts')
        ]);
        $path->set('views', [
            $path->get('admin:views'),
            $path->get('site:views'),
            JPATH_ROOT . '/templates/hyperpc/html',
            JPATH_ROOT . '/templates/hyperpc/html/com_hyperpc'
        ]);
        $path->set('elements', [
            $path->get('media:hyperpc/elements')
        ]);

        return $path;
    }

    /**
     * Get user current ip.
     *
     * @return  array|null
     *
     * @since   2.0
     */
    public function getUserIp()
    {
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return null;
    }

    /**
     * Initialize component application.
     *
     * @return  bool|void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function initialize()
    {
        if ($this->_isInit) {
            return false;
        }

        $this->_loadGlobalConfig();

        $this->_isInit = true;

        $this['app'] = function () {
            return Factory::getApplication();
        };

        $this['input'] = function () {
            return Factory::getApplication()->input;
        };

        $this['route'] = function () {
            return new Route();
        };

        $this['path'] = function () {
            return $this->initPaths();
        };

        $this['db'] = function () {
            return Factory::getDbo();
        };

        $this['doc'] = function () {
            return Factory::getDocument();
        };

        $this['helper'] = function () {
            return new HelperManager();
        };

        $this['cms'] = function () {
            return Factory::getApplication();
        };

        $this['user'] = function () {
            return new User(Factory::getUser()->getProperties());
        };

        $this['params'] = function () {
            $params = ComponentHelper::getParams(HP_OPTION)->toArray();

            $contentLangs = LanguageHelper::getContentLanguages();
            $lang         = $contentLangs[$this->getDefaultLanguageCode()];
            $sef          = $lang->sef;
            foreach ($params as $key => $param) {
                if (is_array($param) && array_key_exists($sef, $param)) {
                    $params[$key] = $param[$sef];
                }
            }

            return new JSON($params);
        };

        $this['event'] = function () {
            return new EventManager();
        };

        $this['detect'] = function () {
            return new MobileDetect();
        };

        $this['wa'] = function () {
            return Factory::getApplication()->getDocument()->getWebAssetManager();
        };

        $this->_initAssets();
    }

    /**
     * Method to get the application document object.
     *
     * @return  Document  The document object
     *
     * @since   1.7.3
     */
    public function getDocument()
    {
        return $this['doc'];
    }

    /**
     * Get the application identity.
     *
     * @return  \Joomla\CMS\User\User
     *
     * @since   4.0.0
     */
    public function getIdentity()
    {
        return Factory::getApplication()->getIdentity();
    }

    /**
     * Check the client interface by name.
     *
     * @param   string  $identifier  String identifier for the application interface
     *
     * @return  boolean  True if this application is of the given type client interface.
     *
     * @since   3.7.0
     */
    public function isClient($identifier)
    {
        return Factory::getApplication()->isClient($identifier);
    }

    /**
     * Returns the application \JMenu object.
     *
     * @param   string  $name     The name of the application/client.
     * @param   array   $options  An optional associative array of configuration settings.
     *
     * @return  AbstractMenu
     *
     * @since   3.2
     */
    public function getMenu($name = null, $options = [])
    {
        return Factory::getApplication()->getMenu();
    }

    /**
     * Check user is development.
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function isDevUser()
    {
        $app      = self::getInstance();
        $devUsers = (array) $app['config']->get('devUsers', []);
        $userId   = Filter::int($app['user']->id);

        return (in_array($userId, $devUsers));
    }

    /**
     * Check local domain.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isLocalDomain()
    {
        $uri = Uri::getInstance();
        return in_array($uri->getHost(), (array) $this['config']->get('local_domain'));
    }

    /**
     * Check site context.
     *
     * @param   string $context
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isSiteContext($context)
    {
        return (Str::low($context) === $this->getContext());
    }

    /**
     * Write file log.
     *
     * @param   string  $message
     * @param   int     $priority
     * @param   string  $textFile
     *
     * @return  void
     *
     * @since   2.0
     */
    public function log($message, $priority = null, $textFile = HP_OPTION)
    {
        if (!$priority) {
            $priority = Log::INFO;
        }

        if (!FS::ext($textFile)) {
            $textFile .= '.php';
        }

        $logConfig = [
            'text_file' => $textFile
        ];

        $logger = new FormatedTextLogger($logConfig);
        $entry  = new LogEntry($message, $priority);

        try {
            $logger->addEntry($entry);
        } catch (\Throwable $th) {
        }
    }

    /**
     * Calls all handlers associated with an event group.
     *
     * This is a legacy method, implementing old-style (Joomla! 3.x) plugin calls. It's best to go directly through the
     * Dispatcher and handle the returned EventInterface object instead of going through this method. This method is
     * deprecated and will be removed in Joomla! 5.x.
     *
     * This method will only return the 'result' argument of the event
     *
     * @param   string       $eventName  The event name.
     * @param   array|Event  $args       An array of arguments or an Event object (optional).
     *
     * @return  array  An array of results from each function call. Note this will be an empty array if no dispatcher is set.
     *
     * @since       4.0.0
     * @throws      \InvalidArgumentException
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the Dispatcher method instead
     *              Example: Factory::getApplication()->getDispatcher()->dispatch($eventName, $event);
     *
     */
    public function triggerEvent($eventName, $args = [])
    {
        $cmsApp = Factory::getApplication();

        return $cmsApp->triggerEvent($eventName, $args);
    }

    /**
     * Enqueue a system message.
     *
     * @param   string $msg The message to enqueue.
     * @param   string $type The message type. Default is message.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   3.2
     */
    public function enqueueMessage($msg, $type = self::MSG_INFO)
    {
        $cmsApp = Factory::getApplication();

        $cmsApp->enqueueMessage($msg, $type);
    }

    /**
     * Sets the value of a user state variable.
     *
     * @param   string $key The path of the state.
     * @param   mixed $value The value of the variable.
     *
     * @return  mixed  The previous state, if one existed. Null otherwise.
     *
     * @throws  \Exception
     *
     * @since   3.2
     */
    public function setUserState($key, $value)
    {
        $cmsApp = Factory::getApplication();

        return $cmsApp->setUserState($key, $value);
    }

    /**
     * Redirect to another URL.
     *
     * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
     * or "303 See Other" code in the header pointing to the new location. If the headers have already been
     * sent this will be accomplished using a JavaScript statement.
     *
     * @param   string   $url     The URL to redirect to. Can only be http/https URL
     * @param   integer  $status  The HTTP 1.1 status code to be provided. 303 is assumed by default.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function redirect($url, $status = 303)
    {
        $cmsApp = Factory::getApplication();

        $cmsApp->redirect($url, $status);
    }

    /**
     * Get default language code.
     *
     * @return  string
     *
     * @throws \Exception
     */
    public function getDefaultLanguageCode(): string
    {
        $params = ComponentHelper::getParams('com_languages');

        return $params->get('site', 'en-GB');
    }

    /**
     * Get default language code.
     *
     * @return  string
     *
     * @throws \Exception
     */
    public function getLanguageCode(): string
    {
        $language = \Joomla\CMS\Factory::getApplication()->getLanguage();

        return $language->getTag();
    }

    /**
     * Get active language sef.
     *
     * @return  string
     *
     * @throws \Exception
     */
    public function getLanguageSef(): string
    {
        $contentLangs = LanguageHelper::getContentLanguages();
        $langCode     = $this->getLanguageCode();
        if (!key_exists($langCode, $contentLangs)) {
            $langCode = $this->getDefaultLanguageCode();
        }

        return $contentLangs[$langCode]->sef;
    }

    /**
     * Initialize application assets manager.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _initAssets()
    {
        $app = $this;
        $this['assets'] = function () use ($app) {
            $manager = new AssetsManager($app['path'], [
                'debug' => JDEBUG,
                'less'  => [
                    'cache_path' => $app['path']->get('cache:') . '/' . HP_CACHE_ASSETS_GROUP,
                    'autoload'   => $app['path']->get('less:hyperpc.less'),
                    'import_paths' => [
                        $app['path']->get('less:general') => $app['path']->url('less:general')
                    ],
                    'force'      => JDEBUG
                ]
            ]);

            return $manager;
        };
    }

    /**
     * Load hyperpc component global configuration.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadGlobalConfig()
    {
        static $included;

        $data = [];
        if ($included === null) {
            $config = HP_PATH_ADMIN . '/configuration.php';
            if (FS::isFile($config)) {
                /** @noinspection PhpIncludeInspection */
                $data = (array) include $config;
            }

            $included = true;
        }

        $data = new Data($data);
        $this['config'] = function () use ($data) {
            return $data;
        };
    }
}
