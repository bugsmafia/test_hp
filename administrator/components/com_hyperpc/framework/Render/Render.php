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

namespace HYPERPC\Render;

use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use HYPERPC\Container;
use Cake\Utility\Inflector;
use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * Class Render
 *
 * @package     HYPERPC\Render
 *
 * @since       2.0
 */
abstract class Render extends Container
{
    /**
     * Name of render.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_name;

    /**
     * Hold product entity object.
     *
     * @var     Entity
     *
     * @since   2.0
     */
    protected $_entity;

    /**
     * Container constructor.
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
        $this->initialize();
    }

    /**
     * Initialize hook method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        $this->setName();
    }

    /**
     * Setup entity for render.
     *
     * @param   Entity $entity
     * @return  void
     *
     * @since   2.0
     */
    public function setEntity(Entity $entity)
    {
        $this->_entity = $entity;
    }

    /**
     * Get entity object.
     *
     * @return  Entity
     *
     * @since   2.0
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Set render name.
     *
     * @param   string|null $name
     * @return  $this
     *
     * @since   2.0
     */
    public function setName($name = null)
    {
        if (!$name) {
            $className = explode('\\', get_class($this));
            $name = Inflector::singularize(end($className));
        }

        $this->_name = Str::low(Str::slug($name));
        return $this;
    }

    /**
     * Get render name.
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
     * Render layout.
     *
     * @param   string $tpl
     * @param   array $args
     * @return  string
     *
     * @since   2.0
     */
    public function renderLayout($tpl, array $args = [])
    {
        $layout = FS::clean($this->_name . '/' . $tpl);
        return $this->hyper['helper']['render']->render($layout, $args);
    }
}
