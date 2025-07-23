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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\NoteHelper;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcControllerNote
 *
 * @property    NoteHelper $_helper
 *
 * @since       2.0
 */
class HyperPcControllerNote extends ControllerLegacy
{
    /**
     * Hold NoteHelper object.
     *
     * @var     NoteHelper
     *
     * @since   2.0
     */
    public $helper;

    /**
     * Ajax remove note.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function ajaxRemove()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'message' => '',
            'result'  => false
        ]);

        $data = new JSON((array) $this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close($output->write());
        };

        $noteId = $data->get('id');

        if ($noteId) {
            /** @var HyperPcModelNote $model */
            $model = $this->getModel();
            if ($model->getTable()->delete($noteId)) {
                $output
                    ->set('result', true)
                    ->set('message', Text::_('COM_HYPERPC_NOTE_SUCCESS_REMOVE_MSG'));

                $this->hyper['cms']->close($output->write());
            } else {
                $output->set('message', Text::_('COM_HYPERPC_NOTE_ERROR_REMOVE_MSG'));
            }
        } else {
            $output->set('message', Text::_('COM_HYPERPC_NOTE_ERROR_NOT_FIND_MSG'));
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Ajax save note.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function ajaxSave()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'id'      => 0,
            'message' => '',
            'note'    => '',
            'result'  => false
        ]);

        $data = new JSON((array) $this->hyper['input']->get(JOOMLA_FORM_CONTROL, [], 'array'));

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN'));
            $this->hyper['cms']->close($output->write());
        };

        $data->set('created_user_id', $this->hyper['user']->id);
        if (!$data->get('created_user_id')) {
            $output->set('message', Text::_('COM_HYPERPC_NOTES_ERROR_USER_NOT_AUTH_IN'));
            $this->hyper['cms']->close($output->write());
        }

        /** @var HyperPcModelNote $model */
        $model = $this->getModel();
        $model->setContext($data->get('context'));

        /** @var HyperPcTableNotes $table */
        $table = $model->getTable();
        $form  = $model->getForm();

        $arrayData = $data->getArrayCopy();

        $form->bind($arrayData);
        $filterData = $form->filter($arrayData);

        if (is_array($filterData) && $form->validate($filterData)) {
            $table->bind($filterData);
            if ($table->store()) {
                $output
                    ->set('result', true)
                    ->set('note', $table->note)
                    ->set('id', ($table->id) ? $table->id : $table->getDbo()->insertid())
                    ->set('message', Text::_('COM_HYPERPC_NOTE_SUCCESS_SEND_MSG'));

                $this->hyper['cms']->close($output->write());
            }

            $output->set('message', Text::_('COM_HYPERPC_CONFIGURATOR_SAVE_ERROR'));
            $this->hyper['cms']->close($output->write());
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \JModelLegacy|boolean  Model object on success; otherwise false on failure.
     *
     * @since   2.0
     */
    public function getModel($name = 'Note', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

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
        $this
            ->registerTask('ajax-save', 'ajaxSave')
            ->registerTask('ajax-remove', 'ajaxRemove');

        $this->helper = $this->hyper['helper']['note'];
    }
}
