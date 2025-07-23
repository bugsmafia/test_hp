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

use HYPERPC\Delivery\DeliveryFactory;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Class HyperPcViewMoysklad_Part
 *
 * @property    MoyskladPart    $part
 * @property    ProductFolder   $folder
 * @property    array           $properties
 * @property    bool            $showPurchaseBlock
 * @property    bool            $retail
 */
class HyperPcViewMoysklad_Part extends ViewLegacy
{
    /**
     * Default display view action.
     *
     * @param   ?string $tpl
     *
     * @return  void
     *
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        $partId = $app->getInput()->getInt('id');

        /** @var MoyskladPart */
        $this->part = $this->hyper['helper']['moyskladPart']->findById($partId);
        if ($this->part->id === 0 || \in_array($this->part->state, [HP_STATUS_UNPUBLISHED, HP_STATUS_TRASHED])) {
            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        $hasModifications = (bool) $this->part->options_count;
        if ($hasModifications) {
            $mod = $app->getInput()->get('mod');
            if (empty($mod)) {
                $defaultOption = $this->part->getDefaultOption();
                $app->redirect($defaultOption->getViewUrl(), 301);
            }

            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $option = $this->hyper['helper']['moyskladVariant']->findBy('alias', $mod, [
                'select'     => ['a.id', 'a.part_id', 'a.state'],
                'conditions' => [
                    $db->quoteName('part_id') . ' = ' . $db->quote($partId)
                ],
            ]);

            if ($option->id && \in_array($option->state, [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED])) {
                $app->redirect($option->getViewUrl(), 301);
            }

            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        $this->folder = $this->part->getFolder();
        $this->properties = $this->folder->getPartFields($this->part->id);
        $this->retail = $this->part->isForRetailSale();

        $this->showPurchaseBlock =
            $this->retail &&
            $app->getInput()->get('tmpl') !== 'component' &&
            \in_array($this->part->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER]);

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     *
     * @return  void
     */
    protected function _prepareDocument(): void
    {
        $app = Factory::getApplication();

        $part = clone $this->part;
        $part->set('name', $this->part->getConfiguratorName($app->getInput()->getInt('product_id')));

        $pathway = $app->getPathway();
        $pathway->addItem($part->name);

        $menu = $app->getMenu()->getActive();
        if (!\is_object($menu)) {
            $this->getDocument()->setMetaData('robots', 'noindex, nofollow');
        }

        $this->getDocument()->addHeadLink(Uri::current(), 'canonical', 'rel');

        $this->hyper['helper']['meta']->setup($part);
        $this->hyper['helper']['opengraph']
            ->setUrl($part->getViewUrl(isFull:true))
            ->setTitle($this->part->getPageTitle())
            ->setImage($this->part->getExportImage());

        if ($this->retail) {
            $this->hyper['helper']['google']
                 ->setDataLayerViewProduct($part);

            if ($this->showPurchaseBlock) {
                $this->hyper['helper']['google']
                    ->setJsViewItems([$part], true, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_PRODUCT_PAGE'), 'product_page')
                    ->setDataLayerAddToCart();
            }
        }
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     */
    protected function _loadAssets(): void
    {
        if (\in_array($this->part->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
            $deliveryType = $this->hyper['params']->get('delivery_type', 'Yandex');
            $delivery     = DeliveryFactory::createDelivery($deliveryType);

            $langParam = [
                'free' => Text::_('COM_HYPERPC_FOR_FREE'),
                'startsFrom' => Text::_('COM_HYPERPC_STARTS_FROM'),
                'methodName' => [
                    'todoor' => Text::_('COM_HYPERPC_DELIVERY_STANDARD'),
                    'connection' => Text::_('COM_HYPERPC_DELIVERY_WITH_CONNECTION'),
                    'pickup' => Text::_('COM_HYPERPC_DELIVERY_GET_IN_PICKUP_POINT'),
                    'post' => Text::_('COM_HYPERPC_DELIVERY_METHOD_POST'),
                    'express' => Text::_('COM_HYPERPC_DELIVERY_EXPRESS')
                ]
            ];

            $this->hyper['helper']['assets']
                ->js('js:widget/site/geo-yandex-delivery.js')
                ->js('js:widget/site/geo-yandex-delivery-card.js')
                ->widget('.jsGeoDelivery', 'HyperPC.Geo.YandexDelivery.Card', [
                    'connectionCost'    => $this->hyper['params']->get('connection_cost', 750, 'int'),
                    'cityIdentifier'    => $delivery->getCityIdentifireType(),
                    'orderPickingDates' => $this->part->getPickingDates(),
                    'langTag'           => $this->hyper->getLanguageCode(),
                    'lang'              => $langParam
                ]);
        }
    }
}
