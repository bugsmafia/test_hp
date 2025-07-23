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

use JBZoo\Utils\Str;
use HYPERPC\Container;
use Cake\Utility\Inflector;

/**
 * Class Compatibility
 *
 * @package     HYPERPC\Compatibility
 *
 * @since       2.0
 */
abstract class Compatibility extends Container
{

    /**
     * Compatibility name.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name;

    /**
     * Compatibility constructor.
     *
     * @param   array  $values
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this->initialize();
    }

    /**
     * Get name.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Initialize class.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->_setName();
    }

    /**
     * Setup name.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _setName()
    {
        $details = explode('\\', get_class($this));
        $name    = str_replace('Compatibility', '', Inflector::underscore(end($details)));

        $this->_name = Str::low($name);
    }
}
