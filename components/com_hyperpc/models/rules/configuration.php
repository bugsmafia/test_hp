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

use HYPERPC\App;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormRuleConfiguration
 *
 * @since 2.0
 */
class JFormRuleConfiguration extends FormRule
{

    /**
     * Hold HYPERPC application jbject.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hp;

    /**
     * JFormRuleConfiguration constructor.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct()
    {
        $this->hp = App::getInstance();
    }

    /**
     * Method to test the value.
     *
     * @param   SimpleXMLElement $element
     * @param   mixed $value
     * @param   null $group
     * @param   Registry|null $input
     * @param   Form|null $form
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        if (!empty(trim($value))) {
            $configuration = $this->hp['helper']['configuration']->getById($value);
            if (!$configuration->id) {
                $element->addAttribute('message', Text::_('COM_HYPERPC_ERROR_CONFIGURATION_NOT_FOUND'));
                return false;
            }
        }

        return true;
    }
}
