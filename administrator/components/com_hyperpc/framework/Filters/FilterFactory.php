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

namespace HYPERPC\Filters;
use HYPERPC\App;
use Cake\Utility\Inflector;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\Folder;
use HYPERPC\ORM\Filter\AbstractFilter;

class FilterFactory
{
    /**
     * Hold application
     *
     * @var     App
     *
     * @since   2.0
     */
    protected $hyper;

    /**
     * Hold all instance
     *
     * @var     FilterFactory[]
     *
     * @since   2.0
     */
    protected static $_instance = [];

    /**
     * Hold all filters
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_filters = [];

    /**
     * FilterFactory constructor.
     *
     * @param   App|null    $app
     *
     * @since   2.0
     */
    /*
    protected function __construct(App $app = null)
    {
        $this->hyper = $app ?: App::getInstance();
        $this->register();
    }
        */
    
    /**
     * Create filter instance
     *
     * @param   string $type
     *
     * @return  Filter
     *
     * @throws  \Exception
     */
    public static function createFilter(string $type): Filter
    {
        $className = 'HYPERPC\\Filters\\' . ucfirst($type) . 'Filter';

        if (!class_exists($className)) {
            throw new \Exception('Unknown filter type');
        }

        return new $className;
    }

    /**
     * Static array of filter classes
     *
     * @var array
     */
    protected static $filters = [
        'ProductFolderParts' => 'HYPERPC\\Filters\\ProductFolderPartsFilter',
        'MoyskladProductIndex' => 'HYPERPC\\Filters\\MoyskladProductIndex'
    ];

    /**
     * Get filter instance
     *
     * @param   string|mixed  $key
     * @param   App           $app
     *
     * @return  FilterFactory
     *
     * @since   2.0
     */
    public static function getInstance($key, App $app = null)
    {
        // Преобразуем ключ в строку для md5
        $keyString = is_string($key) ? $key : (is_object($key) ? get_class($key) . spl_object_hash($key) : (string)$key);
        $keyHash = md5($keyString);
        Log::add("Creating new FilterFactory instance for key: {$keyString}", Log::DEBUG, 'com_hyperpc');

        if (!isset(self::$_instance[$keyHash])) {
            self::$_instance[$keyHash] = new self($app);
        }

        return self::$_instance[$keyHash];
    }



    /**
     * Get filter instance
     *
     * @param   string  $key
     * @param   App     $app
     *
     * @return  FilterFactory
     *
     * @since   2.0
     */
    /*
    public static function getInstance($key, App $app = null)
    {
        $key = md5($key);
        Log::add("Creating new FilterFactory instance for key: {$key}", Log::DEBUG, 'com_hyperpc');

        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self($app);
        }

        return self::$_instance[$key];
    }
    */
    /*
    public function get($type, $hyper)
    {
        $class = __NAMESPACE__ . '\\' . $type . 'Filter';
        if (class_exists($class)) {
            Log::add('Creating filter: ' . $class, Log::DEBUG, 'com_hyperpc');
            return new $class($hyper);
        }
        Log::add('Filter class ' . $class . ' not found', Log::ERROR, 'com_hyperpc');
        return null;
    }
    */

    /**
     * Get filter
     *
     * @param   string  $name
     * @param   App     $app
     *
     * @return  AbstractFilter|null
     *
     * @since   2.0
     */
    public function get($name, App $app = null)
    {
        try {
            $name = Inflector::camelize($name);
            Log::add("Creating filter: HYPERPC\\Filters\\{$name}Filter", Log::DEBUG, 'com_hyperpc');

            if (!isset($this->_filters[$name])) {
                $class = "\\HYPERPC\\Filters\\{$name}Filter";
                if (!class_exists($class)) {
                    Log::add("Filter class {$class} does not exist", Log::ERROR, 'com_hyperpc');
                    return null;
                }

                if (!is_subclass_of($class, AbstractFilter::class)) {
                    Log::add("Filter class {$class} is not a subclass of AbstractFilter", Log::ERROR, 'com_hyperpc');
                    return null;
                }

                $app = $app ?: $this->hyper;
                if (!$app instanceof App) {
                    Log::add("Invalid App object: " . print_r($app, true), Log::ERROR, 'com_hyperpc');
                    return null;
                }

                // Проверяем наличие необходимых сервисов и устанавливаем заглушки
                if (!isset($app['helper']['productFolder'])) {
                    Log::add("Missing productFolder helper in FilterFactory", Log::WARNING, 'com_hyperpc');
                    $app['helper']['productFolder'] = null;
                }
                if (!isset($app['helper']['options'])) {
                    Log::add("Missing options helper in FilterFactory", Log::WARNING, 'com_hyperpc');
                    $app['helper']['options'] = null;
                }

                $filter = new $class($app);
                $this->_filters[$name] = $filter;
            }

            return $this->_filters[$name];
        } catch (\Throwable $e) {
            Log::add("Error creating filter {$name}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), Log::ERROR, 'com_hyperpc');
            return null;
        }
    }

    /**
     * Register all filters
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function register()
    {
        $path = $this->hyper['path']->get('framework:Filters');

        if (is_dir($path)) {
            $files = Folder::files($path, '\.php$');

            foreach ($files as $file) {
                $name = str_replace('Filter.php', '', $file);
                $this->_filters[Inflector::camelize($name)] = null;
            }
        }

        Log::add("Registered filters: " . print_r($this->_filters, true), Log::DEBUG, 'com_hyperpc');
    }

    

    public function create($context, $options)
    {
        // Simplified implementation; replace with actual logic if available
        return new Filter($this->hyper);
    }


}
