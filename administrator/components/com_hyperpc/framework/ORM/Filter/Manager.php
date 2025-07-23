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

namespace HYPERPC\ORM\Filter;

use HYPERPC\App;
use JBZoo\Utils\FS;
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use Joomla\CMS\Filesystem\Folder;

defined('_JEXEC') or die('Restricted access');

/**
 * Class Manager
 *
 * @property    JSON    $_filters
 * @property    App     $hyper
 *
 * @package     HYPERPC\ORM\Table
 *
 * @since       2.0
 */
class Manager
{

    /**
     * Hold instance.
     *
     * @var     $this
     *
     * @since   2.0
     */
    protected static $__instance;

    /**
     * Get filter object.
     *
     * @param   string  $name
     *
     * @return  null|AbstractFilter
     *
     * @since   2.0
     */
    public function get($name)
    {
        $name = $this->getNameAlias($name);
        if ($this->_filters->has($name)) {
            $filter = $this->_filters->get($name);
            return new $filter();
        }

        return null;
    }

    /**
     * Get instance.
     *
     * @return  $this
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
     * Manager constructor.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function __construct()
    {
        $this->__initialize();
    }

    /**
     * Initialize manager.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function __initialize()
    {
        $this->_filters = new JSON();
        $this->hyper    = App::getInstance();

        $this->_register();
    }

    /**
     * Register filters.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _register()
    {
        $files = Folder::files($this->hyper['path']->get('framework:ORM/Filter'), '[^Abstract]Filter.php');
        foreach ($files as $file) {
            $dataName    = FS::filename($file);
            $filterClass = __NAMESPACE__ . '\\' . $dataName;
            if (class_exists($filterClass) && is_subclass_of($filterClass, AbstractFilter::class)) {
                $nameAlias = $this->getNameAlias(str_replace('Filter', '', $dataName));
                $this->_filters->set($nameAlias, $filterClass);
            }
        }
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
}

