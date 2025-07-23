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

use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Joomla\Controller\ControllerForm;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcControllerCompatibility
 *
 * @since   2.0
 */
class HyperPcControllerCompatibility extends ControllerForm
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_COMPATIBILITY';

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
        $this->registerTask('ajax-load-group-fields', 'ajaxLoadGroupFields');
    }

    /**
     * Ajax action load group fields.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function ajaxLoadGroupFields()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new Registry([
            'result'  => 'error',
            'message' => null,
            'output'  => '',
        ]);

        $fieldsContext = $this->hyper['input']->get('fields_context', 'part');

        /** @var ProductFolder $group */
        $group = $this->hyper['helper']['productFolder']->findById($this->hyper['input']->get('group_id', 0, 'int'));
        if (!$group->id) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_GROUP_NOT_FOUND'));
        }

        $html   = [];
        $fields = $this->hyper['helper']['fields']->getGroupFields($group->id, $fieldsContext);

        $html[] = '<option value="">' . Text::_('COM_HYPERPC_SELECT_CHOOSE_FIELD') . '</option>';
        /**@var Field $field */
        foreach ($fields as $field) {
            $html[] = '<option value="' . $field->id . '">' . $field->label . '</option>';
        }

        $output->set('result', 'success');
        $output->set('output', implode(PHP_EOL, $html));

        $this->hyper['cms']->close($output->toString());
    }
}
