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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormRuleMobile
 *
 * @since 2.0
 */
class JFormRuleMobile extends FormRule
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
     * @todo Test not only Russian phone numbers
     *
     * @param   \SimpleXMLElement   $element
     * @param   mixed               $value
     * @param   null                $group
     * @param   Registry|null       $input
     * @param   Form|null           $form
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $result = preg_match('/^(8|\+\d{1,3})[\-\s]?(\(?\d{2,4}\)?)[\-\s]?([\d\-\s]{7,10})$/', $value);
        if ($result === 0) {
            $element->addAttribute('message', Text::sprintf('COM_HYPERPC_RULE_MOBILE_MESSAGE_ERROR', HP_MOBILE_MASK));
            return false;
        }

        return true;
    }
}
