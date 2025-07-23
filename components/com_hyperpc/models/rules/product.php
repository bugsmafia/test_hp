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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

/**
 * Class JFormRuleProduct
 *
 * @since 2.0
 */
class JFormRuleProduct extends FormRule
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
     * JFormRuleProduct constructor.
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
        $product = $this->hp['helper']['moyskladProduct']->findById($value);
        if (!$product->id) {
            $element->addAttribute('message', Text::_('COM_HYPERPC_ERROR_PRODUCT_NOT_FOUND'));
            return false;
        }

        return true;
    }
}
