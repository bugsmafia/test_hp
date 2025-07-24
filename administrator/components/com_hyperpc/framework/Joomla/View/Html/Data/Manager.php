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

namespace HYPERPC\Joomla\View\Html\Data;

use HYPERPC\App;
use JBZoo\Utils\FS;
use Cake\Utility\Inflector;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;

use HYPERPC\ORM\Filter\AbstractFilter;

defined('_JEXEC') or die('Restricted access');

/**
 * Class Manager
 *
 * @package HYPERPC\Joomla\View\Html\Data
 *
 * @since   2.0
 */
class Manager
{

    /**
     * Hold HYPERPC application.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Hold instance.
     *
     * @var     Manager
     *
     * @since   2.0
     */
    protected static $__instance;

    /**
     * Map of view data.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_map = [];

    /**
     * Paths of view data group.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_paths = [];

    /**
     * Get HTML Data object.
     *
     * @param   string  $dataName
     * @param   array   $args
     *
     * @return  HtmlData|null
     *
     * @since   2.0
     */
    public function get($dataName, array $args = [])
    {
        try {
            list($group, $name) = explode('.', $dataName);
            $name = strtolower($name);
            $group = strtolower($group);

            if (!isset($this->_map[$group][$name])) {
                Log::add("Класс для {$dataName} не найден в _map: " . print_r($this->_map, true), Log::ERROR, 'com_hyperpc');
                return null;
            }

            $viewData = $this->_map[$group][$name];
            Log::add("Создание объекта для {$dataName}: {$viewData}", Log::DEBUG, 'com_hyperpc');

            /** @var HtmlData $data */
            $data = new $viewData();
            if (method_exists($data, 'initialize')) {
                // Проверяем, что первый аргумент является экземпляром AbstractFilter
                if (!empty($args[0]) && $args[0] instanceof AbstractFilter) {
                    Log::add("Вызов initialize для {$dataName} с аргументами: " . print_r($args, true), Log::DEBUG, 'com_hyperpc');
                    call_user_func_array([$data, 'initialize'], $args);
                } else {
                    Log::add("Первый аргумент для initialize не является AbstractFilter: " . print_r($args, true), Log::ERROR, 'com_hyperpc');
                    return null;
                }
            } else {
                Log::add("Метод initialize отсутствует в классе {$viewData}", Log::WARNING, 'com_hyperpc');
            }

            Log::add("HtmlData создан для {$dataName}: " . get_class($data), Log::DEBUG, 'com_hyperpc');
            return $data;
        } catch (\Throwable $e) {
            Log::add("Ошибка в Manager::get для {$dataName}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), Log::ERROR, 'com_hyperpc');
            return null;
        }
    }

    /**
     * Get instance.
     *
     * @return  Manager
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public static function getInstance()
    {
        if (self::$__instance === null) {
            self::$__instance = new self();
        }

        return self::$__instance;
    }

    /**
     * Get view data name alias.
     *
     * @param   string $dataName
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getNameAlias($dataName)
    {
        return Inflector::underscore($dataName);
    }

    /**
     * Get all paths.
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     * Register all view data.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _register()
    {
        foreach ($this->_paths as $ns => $path) {
            if (!is_dir($path)) {
                Log::add("Путь {$path} не существует для пространства имен {$ns}", Log::ERROR, 'com_hyperpc');
                continue;
            }
            $groups = Folder::folders($path);
            foreach ($groups as $group) {
                $itemPath = FS::clean($path . '/' . $group);
                if (!is_dir($itemPath)) {
                    Log::add("Путь группы {$itemPath} не существует", Log::ERROR, 'com_hyperpc');
                    continue;
                }
                $items = Folder::files($itemPath);
                foreach ($items as $item) {
                    $dataName = FS::filename($item);
                    $itemClass = $ns . '\\' . $group . '\\' . $dataName;
                    if (class_exists($itemClass) && is_subclass_of($itemClass, HtmlData::class)) {
                        $nameAlias = $this->getNameAlias($dataName);
                        $this->_map[strtolower($group)][$nameAlias] = $itemClass;
                        Log::add("Зарегистрирован класс: {$itemClass} для {$group}.{$nameAlias}", Log::DEBUG, 'com_hyperpc');
                    } else {
                        Log::add("Класс {$itemClass} не существует или не является наследником HtmlData", Log::WARNING, 'com_hyperpc');
                    }
                }
            }
        }
        Log::add("Карта классов _map: " . print_r($this->_map, true), Log::DEBUG, 'com_hyperpc');
    }

    /**
     * Setup paths.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setPaths()
    {
        $this->_paths = [
            'HYPERPC\\Html\\Data' => $this->hyper['path']->get('framework:Html/Data'),
            __NAMESPACE__         => $this->hyper['path']->get('framework:Joomla/View/Html/Data')
        ];
        Log::add("Пути зарегистрированы: " . print_r($this->_paths, true), Log::DEBUG, 'com_hyperpc');
    }

    /**
     * Manager constructor.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function __construct()
    {
        $this->hyper = App::getInstance();
        $this->_setPaths();
        $this->_register();
    }
}
