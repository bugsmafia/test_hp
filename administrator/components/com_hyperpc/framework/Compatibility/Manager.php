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

namespace HYPERPC\Compatibility;

use HYPERPC\App;
use JBZoo\Utils\FS;
use HYPERPC\Data\JSON;
use Joomla\CMS\Filesystem\Folder;

/**
 * Class Manager
 *
 * @package     HYPERPC\Compatibility
 *
 * @since       2.0
 */
class Manager
{

    /**
     * Hold manager instance.
     *
     * @var     null|Manager
     *
     * @since   2.0
     */
    private static $__instance;

    /**
     * Hold application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Execute dir files.
     *
     * @var     array
     *
     * @since   2.0
     */
    private $_executeFiles = [
        'Compatibility.php',
        'Manager.php'
    ];

    /**
     * Get manager instance.
     *
     * @return  Manager
     *
     * @throws \Exception
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
     * Get all types instance.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    public function getTypes()
    {
        static $types;

        if (!$types) {
            $files = Folder::files($this->hyper['path']->get('framework:Compatibility'));
            foreach ($files as $file) {
                if (!in_array($file, $this->_executeFiles)) {
                    $typeName = FS::filename($file);
                    $type = __NAMESPACE__ . '\\' . $typeName;
                    if (class_exists($type)) {
                        $type = new $type();
                        if ($type instanceof Compatibility) {
                            $types[$type->getName()] = $type;
                        }
                    }
                }
            };
        }

        return new JSON((array) $types);
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
    }
}
