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
 * @desc        This class overrides the Joomla! AdminController standard class.
 */

namespace HYPERPC\Joomla\Controller;

use HYPERPC\App;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Class ControllerAdmin
 *
 * @package HYPERPC\Joomla\Controller
 *
 * @since   2.0
 */
class ControllerAdmin extends AdminController
{

    /**
     * Hold framework application.
     *
     * @var     App
     *
     * @since   2.0
     *
     * @deprecated Use only $this->hyper
     */
    public $app;

    /**
     * Hold framework application.
     *
     * @var     App
     *
     * @since   2.0
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
        $this->app = $this->hyper = App::getInstance();
        $this->initialize($config);
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
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
     * @param   string $task The task.
     * @param   string $method The name of the method in the derived class to perform for this task.
     * @return  ControllerAdmin
     *
     * @since   2.0
     */
    public function registerTask($task, $method)
    {
        parent::registerTask($task, $method);
        return $this;
    }

    /**
     * Set a URL for browser redirection.
     *
     * @param   string|array    $url   URL to redirect to.
     * @param   string          $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
     * @param   string          $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
     *
     * @return  $this  This object to support chaining.
     *
     * @since   1.0
     */
    public function setRedirect($url, $msg = null, $type = null)
    {
        if (is_array($url)) {
            $url = $this->hyper['route']->build($url);
        }

        $this->redirect = $url;

        if ($msg !== null) {
            //  Controller may have set this directly.
            $this->message = $msg;
        }

        //  Ensure the type is not overwritten by a previous call to setMessage.
        if (empty($type)) {
            if (empty($this->messageType)){
                $this->messageType = 'message';
            }
        } else{
            $this->messageType = $type;
        }

        return $this;
    }
}
