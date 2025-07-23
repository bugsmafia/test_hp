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

use JBZoo\Data\Data;
use Joomla\CMS\Form\Form;
use HYPERPC\Elements\Element;
use HYPERPC\Elements\Manager;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormRuleCartElements
 *
 * @since 2.0
 */
class JFormRuleElements extends FormRule
{

    /**
     * Method to test the value.
     *
     * @param   SimpleXMLElement $element
     * @param   mixed            $value
     * @param   null             $group
     * @param   Registry|null    $input
     * @param   Form|null $form
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $data = [];
        if ($input !== null) {
            $data = $input->toArray();
        }

        $data = new Data($data);
        $dataArray = $data->getArrayCopy();

        if (array_key_exists('elements', $dataArray)) {
            $position = ($data->get('form', 0, 'int') === HP_ORDER_FORM) ? 'order_form' : 'credit_form';
            $manager  = Manager::getInstance();
            $elements = $manager->getByPosition($position);

            /** @var Element $_element */
            foreach ($elements as $_element) {
                $result = $_element->validate((array) $data->find('elements.' . $_element->getIdentifier()));
                if ($result instanceof \Exception) {
                    $element->addAttribute('message', $result->getMessage());
                    return false;
                }
            }
        }

        return true;
    }
}
