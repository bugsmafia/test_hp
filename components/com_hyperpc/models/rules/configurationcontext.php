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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

/**
 * Class JFormRuleConfigurationContext
 *
 * @since 2.0
 */
class JFormRuleConfigurationContext extends FormRule
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hp;

    /**
     * JFormRuleConfigurationContext constructor.
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
     * @param   string $value
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
        if (!is_string($value)) {
            $element->addAttribute('message', Text::_('COM_HYPERPC_ERROR_CONFIGURATION_CONTEXT_FORMAT'));
            return false;
        }

        $context = strtolower(trim($value));
        if ($context !== SaveConfiguration::CONTEXT_MOYSKLAD) {
            $element->addAttribute('message', Text::_('COM_HYPERPC_ERROR_CONFIGURATION_CONTEXT_NOT_FOUND'));
            return false;
        }

        return true;
    }
}
