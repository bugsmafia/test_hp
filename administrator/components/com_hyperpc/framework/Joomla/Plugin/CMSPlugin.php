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

namespace HYPERPC\Joomla\Plugin;

use HYPERPC\App;
use Cake\Utility\Inflector;
use Joomla\CMS\Plugin\CMSPlugin as JoomlaCMSPlugin;

jimport('joomla.plugin.plugin');

/**
 * CMSPlugin class
 *
 * @package HYPERPC\Joomla\Plugin
 *
 * @since   2.0
 */
class CMSPlugin extends JoomlaCMSPlugin
{

    /**
     * Hold HYPERPC Application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * PlgContentHyperPC constructor.
     *
     * @param   object $subject
     * @param   array $config
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct($subject, array $config = [])
    {
        parent::__construct($subject, $config);
        $this->hyper = App::getInstance();
    }

    /**
     * Call framework event trigger by joomla context event name.
     *
     * @param   string $context
     * @param   string $jEventName
     * @param   array $arguments
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Event\Exception
     *
     * @since   2.0
     */
    protected function _callEventTrigger($context, $jEventName, array $arguments = [])
    {
        list(, $contextName) = explode('.', $context, 2);
        $eventClassName = 'HYPERPC\\Event\\' . Inflector::camelize($contextName) . 'EventHandler';

        if (class_exists($eventClassName)) {
            $callbackName = str_replace('Content', '', $jEventName);
            $triggerName  = str_replace('Content', Inflector::camelize($contextName), $jEventName);
            if (method_exists($eventClassName, $callbackName)) {
                return $this->hyper['event']
                    ->on($triggerName, [
                        $eventClassName,
                        $callbackName
                    ])
                    ->trigger($triggerName, $arguments);
            }
        }
    }
}
