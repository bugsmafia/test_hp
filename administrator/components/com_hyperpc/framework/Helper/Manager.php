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

namespace HYPERPC\Helper;

use JBZoo\Utils\Str;
use HYPERPC\Container;
use Joomla\CMS\Filesystem\Folder;

/**
 * Class Manager
 *
 * @package HYPERPC\Helper
 *
 * @since 2.0
 */
class Manager extends Container
{

    /**
     * Helper class name suffix.
     *
     * @since   2.0
     */
    const HELPER_SUFFIX = 'Helper';

    /**
     * List of loaded helper.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_loaded = [];

    /**
     * Manager constructor.
     *
     * @param   array $values
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this->_loadHelpers();
    }

    /**
     * Add new helper.
     *
     * @param   string $name
     *
     * @return  $this
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function add($name)
    {
        $id = Str::low($name);

        if (!array_key_exists($id, $this->_loaded)) {
            $className = __NAMESPACE__ . '\\' . $this->_getClassName($name);
            $this->_register($id, $className);
        } else {
            throw new Exception("Helper \"{{$name}}\" already defined!");
        }

        return $this;
    }

    /**
     * Check loaded helper by name.
     *
     * @param   string $name   Name of helper.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function loaded($name)
    {
        return (array_key_exists($name, $this->_loaded));
    }

    /**
     * Get helper object.
     *
     * @param   string $id
     *
     * @return  mixed
     *
     * @throws  Exception
     * @throws  \Exception
     * @throws  \InvalidArgumentException
     * @throws  \Pimple\Exception\UnknownIdentifierException
     *
     * @since   2.0
     */
    public function offsetGet($id)
    {
        $id = Str::low($id);
        if (!array_key_exists($id, $this->_loaded)) {
            $className = __NAMESPACE__ . '\\' . $this->_getClassName($id);
            $this->_register($id, $className);
        }

        return parent::offsetGet($id);
    }

    /**
     * Get current helper class name.
     *
     * @param   string $class
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getClassName($class)
    {
        $class = Str::low($class);
        return ucfirst($class) . self::HELPER_SUFFIX;
    }

    /**
     * Autoload helper class.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _loadHelpers()
    {
        $files = Folder::files($this->hyper['path']->get('framework:Helper'));
        foreach ($files as $file) {
            $name      = str_replace('Helper.php', '', $file);
            $className = __NAMESPACE__ . '\\' . $name . self::HELPER_SUFFIX;

            if (class_exists($className) && is_subclass_of($className, AppHelper::class)) {
                $this->add($name);
            }
        }
    }

    /**
     * Register helper.
     *
     * @param   string|int  $id
     * @param   string      $className
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _register($id, $className)
    {
        $id = (string) $id;
        if (class_exists($className)) {
            $this->_loaded[$id] = $className;
            $this[$id] = function () use ($className) {
                return new $className();
            };
        } else {
            throw new Exception("Helper \"{{$className}}\" not found!");
        }
    }
}
