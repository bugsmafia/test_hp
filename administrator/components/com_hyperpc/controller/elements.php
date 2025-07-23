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
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use HYPERPC\Elements\Element;
use HYPERPC\Elements\Manager;
use HYPERPC\Joomla\Controller\ControllerAdmin;

/**
 * Class HyperPcControllerElements
 *
 * @since   2.0
 */
class HyperPcControllerElements extends ControllerAdmin
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
     * Add element action.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function add()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $eType   = Str::low($this->hyper['input']->get('type'));
        $eGroup  = Str::low($this->hyper['input']->get('group'));
        $element = $this->_manager->create($eType, $eGroup);

        $element->setFormControl($this->hyper['input']->get('control', null, 'string'));

        $this->hyper['cms']->close((new JSON([
            'element' => $this->hyper['helper']['render']->render('elements/edit_element', ['element' => $element])
        ])));
    }

    /**
     * Call element action.
     *
     * @return  mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function call()
    {
        $eType   = Str::low($this->hyper['input']->get('type'));
        $eGroup  = Str::low($this->hyper['input']->get('group'));
        $eAction = Inflector::camelize(Str::low($this->hyper['input']->get('action')));
        $element = $this->_manager->getElement($eGroup, $eType);

        if ($element instanceof Element) {
            $elAction = $element->hasAction($eAction);
            if ($elAction) {
                return $element->$elAction();
            }
        }

        return null;
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
            ->registerTask('add', 'add');


        $this->_manager = Manager::getInstance();
    }
}