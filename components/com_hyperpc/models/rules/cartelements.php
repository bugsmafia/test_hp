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
use JBZoo\Data\Data;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use HYPERPC\Helper\OrderHelper;
use HYPERPC\Cart\Elements\Manager;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormRuleCartElements
 *
 * @since 2.0
 */
class JFormRuleCartElements extends FormRule
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
     * JFormRuleCartElements constructor.
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
        $data = [];
        if ($input !== null) {
            $data = $input->toArray();
        }

        $data = new Data($data);
        $dataArray = $data->getArrayCopy();

        if (array_key_exists('elements', $dataArray)) {
            $manager = new Manager();
            $manager->build();

            $cartParams = new Data((array) $this->hp['params']->get('cart'));
            $param = (array) $cartParams->get('order');

            if ($data->get('form_type') === OrderHelper::FORM_TYPE_CREDIT) {
                $param = (array) $cartParams->get('credit');
            }

            foreach ((array) $data->get('elements', []) as $identifier => $_value) {
                if (array_key_exists($identifier, $param)) {
                    $elData = (array) $param[$identifier];
                    $elData['identifier'] = $identifier;
                    $elData['value'] = $_value['value'];

                    $_element = $manager->getElement($elData['element'], $elData['type']);
                    if ($_element !== false) {
                        $_element->setData($elData);
                        $result = $_element->validate($dataArray);

                        if ($result instanceof \Exception) {
                            $element->addAttribute('message', $result->getMessage());
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}
