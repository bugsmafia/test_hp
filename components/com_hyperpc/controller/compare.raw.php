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

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CompareHelper;
use HYPERPC\Helper\PositionHelper;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * Class HyperPcControllerCompare
 *
 * @since 2.0
 */
class HyperPcControllerCompare extends ControllerLegacy
{

    /**
     * Hold CompareHelper object.
     *
     * @var     CompareHelper
     *
     * @since   2.0
     */
    public $helper;

    /**
     * Add item to compare.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function add()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $args = new JSON($this->hyper['input']->get('args', [], 'array'));
        $type = $args->get('type', CompareHelper::TYPE_PART);

        $result = new JSON([
            'result' => false,
            'msg'    => Text::_('COM_HYPERPC_COMPARE_ADD_ITEM_ERROR')
        ]);

        $countItem  = 0;
        $totalCount = $this->helper->countItems();

        $allowedMax = 4;

        switch ($type) {
            case CompareHelper::TYPE_POSITION:
                /** @var PositionHelper $positionHelper */
                $positionHelper = $this->hyper['helper']['position'];

                $item       = $positionHelper->expandToSubtype($this->hyper['helper']['position']->findById($args->get('itemId')));
                $countItem  = $this->helper->countPositionByGroup($item->product_folder_id);
                $allowedMax = Filter::int($this->hyper['params']->get('compare_max', 4));
                break;
        }

        $result
            ->set('count', $countItem)
            ->set('total', $totalCount);
        //$result->set('allowed', $allowedMax);

        if ($countItem > ($allowedMax - 1)) {
            $result->set('msg', Text::sprintf('COM_HYPERPC_COMPARE_MAX_ITEM_ERROR_ERROR', $allowedMax));
            $this->hyper['cms']->close($result->write());
        }

        $added   = $this->helper->addItem($args);
        $itemKey = $this->helper->getItemKey($args);
        $items   = $this->helper->getItems($args->get('type'));

        if ($item->id) {
            $itemName = $item->name;
            if ($type === CompareHelper::TYPE_POSITION && $item instanceof MoyskladPart) {
                /** @var MoyskladVariant $option */
                $option = $this->hyper['helper']['moyskladVariant']->findById($args->get('optionId'));

                if ($option->id) {
                    $itemName .= ' ' . $option->name;
                }
            }

            if (array_key_exists($itemKey, $items) && $added === false) {
                $hasAddedLangKey = 'COM_HYPERPC_COMPARE_ITEM_PART_WAS_ADDED_EARLY'; /** @todo add messeges for product */
                $result
                    ->set('result', true)
                    ->set('msg', Text::sprintf($hasAddedLangKey, $itemName));
            } else {
                if ($type === CompareHelper::TYPE_POSITION) {
                    $addedSuccessLangKey = 'COM_HYPERPC_COMPARE_ITEM_HAS_BEEN_ADDED';
                    if ($item instanceof MoyskladProduct) {
                        $addedSuccessLangKey = 'COM_HYPERPC_COMPARE_ITEM_PRODUCT_HAS_BEEN_ADDED';
                        if ($item->getFolder()->getItemsType() === 'notebook') {
                            $addedSuccessLangKey = 'COM_HYPERPC_COMPARE_ITEM_NOTEBOOK_HAS_BEEN_ADDED';
                        }
                    }

                    $groupMsg = sprintf(
                        '<div class="uk-text-small uk-text-muted">%s</div>',
                        Text::sprintf(
                            'COM_HYPERPC_COMPARE_GROUP_ITEM_TEXT',
                            $item->getFolder()->title,
                            ++$countItem
                        )
                    );

                    $result
                        ->set('result', true)
                        ->set('msg', Text::sprintf($addedSuccessLangKey, $itemName, $groupMsg))
                        ->set('count', $countItem);
                }

                $updateGroup = $args->get('updateGroup', null);
                if ($updateGroup) {
                    $html = $this->hyper['helper']['compare']->getGroupHtml($updateGroup, $type);

                    $result->set('html', $html);
                }

                $result
                    ->set('total', ++$totalCount)
                    ->set('items', $this->helper->getItems());
            }
        }

        $this->hyper['cms']->close($result->write());
    }

    /**
     * Clear all compare items.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function clear()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');
        $output = new JSON(['result' => $this->helper->clear()]);
        $this->hyper['cms']->close($output->write());
    }

    /**
     * Get compare items list.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getList()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $itemsList = $this->helper->getItems();
        $result    = new JSON(['result' => true, 'items' => $itemsList]);

        $this->hyper['cms']->close($result->write());
    }

    /**
     * Remove item from compare.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function remove()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $args    = new Data($this->hyper['input']->get('args', [], 'array'));
        $process = $this->helper->removeItem($args);

        $result  = new JSON([
            'result' => $process,
            'count'  => $this->helper->countItems(),
            'items'  => $this->helper->getItems()
        ]);

        $this->hyper['cms']->close($result->write());
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('add', 'add')
            ->registerTask('remove', 'remove')
            ->registerTask('clear', 'clear')
            ->registerTask('get-list', 'getList');

        $this->helper = $this->hyper['helper']['compare'];
    }
}
