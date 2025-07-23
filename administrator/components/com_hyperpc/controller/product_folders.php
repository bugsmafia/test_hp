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
 * @author      Roman Evsyukov
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Controller\ControllerAdmin;

/**
 * Class HyperPcControllerProduct_Folders
 *
 * @property    array                       $cid
 * @property    int                         $folderId
 * @property    string                      $task
 * @property    HyperPcModelProduct_Folder  $model
 * @property    HyperPcTableProduct_Folders $table
 *
 * @since 2.0
 */
class HyperPcControllerProduct_Folders extends ControllerAdmin
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_PRODUCT_FOLDER';

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
        $this->task     = $this->getTask();
        $this->cid      = $this->hyper['input']->get('cid', [], 'array');

        $this->model    = $this->getModel('product_folder');
        $this->table    = $this->model->getTable();

        $this
            ->registerTask('delete', 'delete')
            ->registerTask('publish', 'state')
            ->registerTask('unpublish', 'state');
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name
     * @param   string  $prefix
     * @param   array   $config
     *
     * @return  bool|JModelLegacy
     *
     * @since   2.0
     */
    public function getModel($name = 'Product_folder', $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function delete()
    {
        //  Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        try {
            $count = 0;
            $this->model->delete($this->cid);
            $msg = Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($this->cid));

            $this->setMessage(Text::sprintf($msg, $count));
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->redirect = $this->hyper['route']->build(['folder_id' => $this->folderId, 'view' => $this->view_list]);
    }

    /**
     * Change position state.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function state()
    {
        //  Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        try {
            $count = 0;
            foreach ($this->cid as $id) {
                $productFolder = $this->model->getItem($id);
                if ($productFolder->id > 0) {
                    $value = ($this->task === 'publish') ? 1 : 0;
                    $productFolder->set('published', $value);
                    if ($this->table->save($productFolder->getArray())) {
                        $count++;
                    }
                }
            }

            $msg = ($this->task === 'publish') ? 'COM_HYPERPC_POSITION_PUBLISH' : 'COM_HYPERPC_POSITION_UNPUBLISH';

            $this->setMessage(Text::sprintf($msg, $count));
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->redirect = $this->hyper['route']->build(['folder_id' => $this->folderId, 'view' => $this->view_list]);
    }
}
