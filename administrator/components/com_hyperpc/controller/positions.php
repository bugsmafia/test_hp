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

use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use HYPERPC\ORM\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Helper\MoyskladFilterHelper;
use HYPERPC\Joomla\Controller\ControllerForm;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;

/**
 * Class HyperPcControllerPositions
 *
 * @property    array                   $cid
 * @property    int                     $folderId
 * @property    string                  $task
 * @property    HyperPcModelPosition    $model
 * @property    HyperPcTablePositions   $table
 *
 * @since   2.0
 */
class HyperPcControllerPositions extends ControllerForm
{

    const INDEX_RECOUNT_STEP = 10;

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_POSITION';

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
        $this->folderId = $this->hyper['input']->get('folder_id');
        $this->cid      = $this->hyper['input']->get('cid', [], 'array');

        $this->model    = $this->getModel('position');
        $this->table    = $this->model->getTable();

        $this
            ->registerTask('on_sale', 'canBuy')
            ->registerTask('from_sale', 'canBuy')
            ->registerTask('delete', 'delete')
            ->registerTask('publish', 'state')
            ->registerTask('unpublish', 'state')
            ->registerTask('ajax-recount-index', 'ajaxRecountIndex');
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
                $position = $this->model->getItem($id);
                if ($position->id > 0) {
                    $value = ($this->task === 'publish') ? 1 : 0;
                    $position->set('state', $value);
                    if ($this->table->save($position->getArray())) {
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

    /**
     * Change part can buy.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function canBuy()
    {
        //  Check for request forgeries.
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        try {
            $count = 0;
            foreach ($this->cid as $id) {
                $position = $this->model->getItem($id);
                if ($position->id > 0) {
                    $value = ($this->task === 'on_sale') ? 1 : 0;

                    if ($position instanceof MoyskladPart) {
                        $table = Table::getInstance('Moysklad_Parts');
                        $table->load($position->id);
                        $position->set('retail', $value);
                    } elseif ($position instanceof MoyskladProduct) {
                        $table = Table::getInstance('Moysklad_Products');
                        $table->load($position->id);
                        $position->set('on_sale', $value);
                    }

                    if ($table->save($position->getArray())) {
                        $count++;
                    }
                }
            }

            $msg = ($this->task === 'on_sale') ? 'COM_HYPERPC_CAN_BY_ON_SALE' : 'COM_HYPERPC_CAN_BY_FROM_SALE';

            $this->setMessage(Text::sprintf($msg, $count));
        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->redirect = $this->hyper['route']->build(['folder_id' => $this->folderId, 'view' => $this->view_list]);
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
     * Ajax action recount index for all products.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function ajaxRecountIndex()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'stop' => false
        ]);

        /** @var MoyskladFilterHelper $helper */
        $helper  = $this->hyper['helper']['moyskladFilter'];

        $page    = $this->hyper['input']->get('page', 0);
        $getFrom = $this->hyper['input']->get('get_from', $helper::RECOUNT_INDEX_FROM_IN_STOCK);
        $limit   = self::INDEX_RECOUNT_STEP;
        $offset  = $limit * $page;

        if (empty($this->hyper['params']->get($helper::PRODUCT_INDEX_FIELD))) {
            $helper->dropTable();

            $output
                ->set('stop', true)
                ->set('get_from', $helper::RECOUNT_INDEX_FROM_CATALOG)
                ->set('error', Text::_('COM_HYPERPC_ERROR_INDEX_PRODUCTS_NOT_FIND'));

            $this->hyper['cms']->close($output->write());
        }

        $dropTable = Filter::bool($this->hyper['input']->get('drop_table', 1));
        if ($dropTable === true) {
            $dropTable = false;
            $helper
                ->dropTable()
                ->createTable($helper->getTableProps());
        }

        $totalProcess = $helper->getCountInStockProducts();
        $products     = $helper->getAllRecountInStockProducts($offset, $limit);

        if ($getFrom === $helper::RECOUNT_INDEX_FROM_CATALOG) {
            $totalProcess = $helper->getCountProductsFromCategories();
            $products     = $helper->getAllRecountProductsFromCatalog($offset, $limit);
        }

        $current = $limit * ($page + 1);

        if ($current > $totalProcess) {
            $current = $totalProcess;
        }

        if ($totalProcess > 0) {
            $progress = round($current * 100 / $totalProcess, 2);
        } else {
            $progress = 100;
        }

        /** @var MoyskladProduct $product */
        foreach ($products as $product) {
            $helper->updateProductIndex($product);
        }

        $newPage = $page + 1;

        if ($getFrom === $helper::RECOUNT_INDEX_FROM_CATALOG && Filter::int($progress) === 100) {
            $output->set('stop', true);
        }

        $output
            ->set('limit', $limit)
            ->set('page', $newPage)
            ->set('getFrom', $getFrom)
            ->set('progress', $progress)
            ->set('total', $totalProcess)
            ->set('dropTable', $dropTable)
            ->set('current', $current);

        $this->hyper['cms']->close($output->write());
    }
}
