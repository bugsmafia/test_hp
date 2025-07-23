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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Table\Table;
use Cake\Utility\Inflector;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Controller\ControllerForm;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

/**
 * Class HyperPcControllerStore_Item
 *
 * @since   2.0
 */
class HyperPcControllerStore_Item extends ControllerForm
{

    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $text_prefix = 'COM_HYPERPC_STORE_ITEM';

    /**
     * Ajax action set item.
     *
     * @return  void
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function ajaxSetItem()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result' => false,
            'message' => null
        ]);

        if (!Session::checkToken()) {
            $output->set('message', Text::_('JINVALID_TOKEN_NOTICE'));
            $this->hyper['cms']->close($output->write());
        }

        $store = $this->hyper['helper']['store']->findById((int) $this->hyper['input']->get('store_id'));
        if (!$store->id) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_STORE_NOT_FOUNT'));
            $this->hyper['cms']->close($output->write());
        }

        list (, $context) = explode('.', $this->hyper['input']->get('context'));
        $itemId = $this->hyper['input']->get('item_id');

        if (!$this->hyper['helper']->loaded($context)) {
            $output->set('message', Text::sprintf(
                'COM_HYPERPC_ERROR_HELPER_NOT_FOUND'
                , Inflector::camelize($context) . 'Helper')
            );

            $this->hyper['cms']->close($output->write());
        }

        /** @var PartMarker $item */
        $item = $this->hyper['helper'][$context]->findById($itemId);
        if (!$item->id) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_ITEM_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $optionId = (int) $this->hyper['input']->get('option_id');
        if (!array_key_exists($optionId, $item->getOptions()) && $optionId > 0) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_ITEM_OPTION_NOT_FOUNT'));
            $this->hyper['cms']->close($output->write());
        }

        $_context  = Str::low(HP_OPTION . '.' . $context);
        $storeItem = $this->hyper['helper']['store']->getItem($store->id, $item->id, $optionId, $_context);

        /** @var HyperPcTableStore_Items $table */
        $table = Table::getInstance('Store_Items', HP_TABLE_CLASS_PREFIX);

        $saveResult = $table->save([
            'id'        => $storeItem->id,
            'store_id'  => $store->id,
            'item_id'   => $item->id,
            'option_id' => $optionId,
            'balance'   => $this->hyper['input']->get('balance'),
            'context'   => $_context
        ]);

        if (!$saveResult) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_ITEM_NOT_WRITE_IN_DB'));
            $this->hyper['cms']->close($output->write());
        }

        $output
            ->set('result', $saveResult)
            ->set('message', Text::_('COM_HYPERPC_STORE_ITEM_SUCCESS_SET'));

        $this->hyper['cms']->close($output->write());
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
        parent::initialize($config);
        $this->registerTask('ajax-set-item', 'ajaxSetItem');
    }
}
