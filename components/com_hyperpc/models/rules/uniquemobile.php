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
use HYPERPC\ORM\Entity\Plugin;
use Joomla\CMS\Plugin\PluginHelper;
use HYPERPC\Joomla\Model\Entity\Field;

defined('_JEXEC') or die('Restricted access');

JLoader::register('JFormRuleMobile', JPATH_ROOT . '/components/' . HP_OPTION . '/models/rules/mobile.php');

/**
 * Class JFormRuleUniquemobile
 *
 * @since 2.0
 */
class JFormRuleUniquemobile extends FormRule
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
     * @param   \SimpleXMLElement   $element
     * @param   mixed               $value
     * @param   null                $group
     * @param   Registry|null       $input
     * @param   Form|null           $form
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $db     = $this->hyper['db'];
        $plugin = new Plugin((array) PluginHelper::getPlugin('user', 'hyperpc'));

        $where = [
            $db->qn('f.context') . ' = ' . $db->q('com_users.user'),
            $db->qn('f.name')    . ' = ' . $db->q($plugin->params->get('phone_field')),
        ];

        $unmaskedMValue = str_replace([' ', '-', '(', ')'], '', $value);

        if (strlen($unmaskedMValue) === 12) {
            $maskedMValue = preg_replace('/([+]7)(\d{3})(\d{3})(\d{2})(\d{2})/i', '$1 ($2) $3-$4-$5', $unmaskedMValue);
            $where[]      = $db->qn('v.value') . ' IN (' . $db->q($maskedMValue) . ',' . $db->q($unmaskedMValue) . ')';
        } else {
            $where[] = $db->qn('v.value') . ' = ' . $db->q($value);
        }

        $query = $db
            ->getQuery(true)
            ->select(['v.value','v.item_id', 'f.id'])
            ->from($db->qn(JOOMLA_TABLE_FIELDS_VALUES, 'v'))
            ->join('LEFT', $db->qn('#__fields', 'f') . ' ON v.field_id = f.id')
            ->where($where);

        $field = new Field((array) $db->setQuery($query)->loadObject());
        if ($field->id) {
            $element->addAttribute('item_id', $field->item_id);
            $element->addAttribute('message', Text::sprintf('COM_HYPERPC_ERROR_USER_PHONE_EXIST'));
            return false;
        }

        return true;
    }
}
