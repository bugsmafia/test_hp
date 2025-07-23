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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Str;
use Cake\Utility\Inflector;
use HYPERPC\Elements\Element;
use HYPERPC\Elements\Manager;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerElements
 *
 * @since   2.0
 */
class HyperPcControllerElements extends ControllerLegacy
{

    /**
     * Element manager.
     *
     * @var     Manager
     *
     * @since   2.0
     */
    protected $_manager;

    /**
     * Call element action for render.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function call()
    {
        $identifier = Str::low($this->hyper['input']->get('identifier'));
        $eGroup     = Str::low($this->hyper['input']->get('group'));
        $eAction    = Inflector::camelize(Str::low($this->hyper['input']->get('action')));
        $element    = $this->_manager->getElement($eGroup, $identifier);

        if ($element instanceof Element) {
            $elAction = $element->hasAction($eAction);
            if ($elAction) {
                echo $element->$elAction();
            } else {
                throw new Exception('Not allowed action - ' . $eAction, 404);
            }
        }
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this
            ->registerTask('call', 'call')
            ->registerTask('ajax-call', 'ajaxCall');

        $this->_manager = Manager::getInstance();
    }
}