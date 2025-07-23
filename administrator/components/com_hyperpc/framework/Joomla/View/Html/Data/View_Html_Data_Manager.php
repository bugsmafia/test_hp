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
        list ($group, $name) = explode('.', $dataName);

        $name  = strtolower($name);
        $group = strtolower($group);

        if (isset($this->_map[$group][$name])) {
            $viewData = $this->_map[$group][$name];
            /** @var HtmlData $data */
            $data = new $viewData();
            call_user_func_array([$data, 'initialize'], $args);
            return $data;
        }

        return null;
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
            $groups = Folder::folders($path);
            foreach ($groups as $group) {
                $itemPath = FS::clean($path . '/' . $group);
                $items    = Folder::files($itemPath);
                foreach ($items as $item) {
                    $dataName  = FS::filename($item);
                    $itemClass = $ns . '\\' . $group . '\\' . $dataName;
                    if (class_exists($itemClass) && is_subclass_of($itemClass,  HtmlData::class)) {
                        $nameAlias = $this->getNameAlias($dataName);
                        $this->_map[strtolower($group)][$nameAlias] = $itemClass;
                    }
                }
            }
        }
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
        $this->__initialize();
    }

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    private function __initialize()
    {
        $this->_register();
    }
}
