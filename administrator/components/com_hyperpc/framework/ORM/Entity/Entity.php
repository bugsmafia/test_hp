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

namespace HYPERPC\ORM\Entity;

use HYPERPC\App;
use HYPERPC\Helper\AppHelper;

/**
 * Class Entity
 *
 * @since   2.0
 */
abstract class Entity implements EntityInterface
{

    use EntityTrait;

    /**
     * Hold helper object.
     *
     * @var     null|
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Hold Realty application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Entity constructor.
     *
     * @param   array $properties
     * @param   array $options
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $properties = [], array $options = [])
    {
        $this->hyper = App::getInstance();

        if (isset($properties['hyper'])) {
            unset($properties['hyper']);
        }

        $helperName = $this->_getHelperName();
        if ($this->hyper['helper']->loaded($helperName) === true) {
            $this->_helper = $this->hyper['helper'][$helperName];
        }

        $this->initialize();

        if (!empty($properties)) {
            $this->set($properties, $options);
        }
    }

    /**
     * Get entity helper.
     *
     * @return  AppHelper|null
     *
     * @since   2.0
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Get admin (backend) edit url.
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getAdminEditUrl();
}
