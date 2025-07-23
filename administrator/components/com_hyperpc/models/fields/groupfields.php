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

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\Form;
use HYPERPC\Joomla\Form\FormFieldList;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldGroupFields
 *
 * @property    Form $form
 *
 * @since       2.0
 */
class JFormFieldGroupFields extends FormFieldList
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'GroupFields';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $data = $this->form->getData();

        $id  = $data->get('id');
        $db  = $this->hyper['db'];

        $query = $db
            ->getQuery(true)
            ->select(['c.category_id', 'f.*'])
            ->from($db->qn('#__fields_categories', 'c'))
            ->join('LEFT', $db->quoteName('#__fields', 'f') . ' ON c.field_id = f.id')
            ->where([
                $db->qn('c.category_id') . ' = ' . $db->q($id),
                $db->qn('f.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED)
            ])
            ->order($db->qn('f.ordering') . ' ASC');

        $list = $db->setQuery($query)->loadObjectList();

        $options = [[
            'value' => 0,
            'text'  => Text::_('JGLOBAL_ROOT_PARENT')
        ]];

        if (count($list)) {
            foreach ($list as $data) {
                $data = new JSON($data);
                $options[$data->get('id')]['value'] = $data->get('id');
                $options[$data->get('id')]['text']  = $data->get('label');
            }
        }

        return $options;
    }
}
