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
 * @desc        This class overrides the Joomla! ControllerForm standard class.
 */

namespace HYPERPC\Joomla\Controller;

use HYPERPC\App;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * Class ControllerForm
 *
 * @package HYPERPC\Joomla\Controller
 *
 * @since   2.0
 */
class ControllerForm extends FormController
{

    /**
     * Hold framework application.
     *
     * @var App
     *
     * @since 2.0
     */
    public $hyper;

    /**
     * ControllerLegacy constructor.
     *
     * @param   array $config
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->hyper = App::getInstance();
        $this->initialize($config);
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
    }

    /**
     * Register (map) a task to a method in the class.
     *
     * @param   string $task    The task.
     * @param   string $method  The name of the method in the derived class to perform for this task.
     *
     * @return  ControllerForm
     *
     * @since   2.0
     */
    public function registerTask($task, $method)
    {
        parent::registerTask($task, $method);
        return $this;
    }
}
