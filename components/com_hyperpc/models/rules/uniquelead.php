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
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\Rule\EmailRule;
use HYPERPC\Joomla\Model\Entity\Lead;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormRuleUniquelead
 *
 * @since 2.0
 */
class JFormRuleUniquelead extends FormRule
{

    /**
     * Hold HYPERPC application jbject.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * JFormRuleProduct constructor.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct()
    {
        $this->hyper = App::getInstance();
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
        $emailRule = new EmailRule();

        /** @var \JBZoo\Data\Data $module */
        $module = $this->hyper['helper']['module']->findById($input->get('module_id'));

        if (!$module->get('id')) {
            return false;
        }

        $params   = new JSON($module->get('params'));
        $leadType = Filter::int($params->get('leads_type', 1));

        if ($emailRule->test($element, $value, $group, $input, $form)) {
            /** @var Lead $lead */
            $lead = $this->hyper['helper']['lead']->getBy('email', $value);
            //  Check subscription type.
            if ($lead->id && $leadType === 1) {
                $element->addAttribute('message', Text::sprintf('COM_HYPERPC_ERROR_LEAD_EXISTS', $lead->username));
                return false;
            }

            return true;
        }

        return false;
    }
}
