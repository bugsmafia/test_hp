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

use Cake\Utility\Inflector;
use Joomla\CMS\Form\FormHelper;
use HYPERPC\Joomla\Controller\ControllerAdmin;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcControllerField
 *
 * @since   2.0
 */
class HyperPcControllerField extends ControllerAdmin
{

    /**
     * Call field ajax callback.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function fieldCallback()
    {
        $method    = Inflector::variable($this->hyper['input']->get('method')) . 'Callback';
        $fieldName = Inflector::camelize($this->hyper['input']->get('field'));

        FormHelper::addFieldPath($this->hyper['path']->get('admin:models/fields'));

        $fieldClass = FormHelper::loadFieldClass($fieldName);
        $field = new $fieldClass();

        if (method_exists($field, $method)) {
            echo $field->$method();
        } else {
            echo '{"result" : false}';
        }

        $this->hyper['cms']->close();
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
        parent::initialize($config);
        $this->registerTask('callback', 'fieldCallback');
    }
}
