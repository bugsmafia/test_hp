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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Delivery\DeliveryFactory;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Object\Variant\Characteristics\CharacteristicDataCollection as Characteristics;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Class HyperPcViewMoysklad_Variant
 *
 * @property    MoyskladPart        $part
 * @property    ProductFolder       $folder
 * @property    array               $properties
 * @property    bool                $showPurchaseBlock
 * @property    bool                $retail
 * @property    MoyskladVariant     $variant
 * @property    MoyskladVariant[]   $variants
 * @property    Characteristics     $characteristics
 */
class HyperPcViewMoysklad_Variant extends ViewLegacy
{
    /**
     * Display action.
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

        $variantId = $app->getInput()->getInt('id');
        $partId = $app->getInput()->getInt('part_id');

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        /** @var MoyskladVariant */
        $this->variant = $this->hyper['helper']['moyskladVariant']->findById($variantId, [
            'conditions' => [
                $db->quoteName('a.part_id') . ' = ' . $db->quote($partId),
                $db->quoteName('a.state') . ' IN (' .
                    \implode(
                        ', ',
                        \array_map(
                            fn($state) => $db->quote($state),
                            [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED])
                    ) .
                ')'
            ]
        ]);

        if (empty($this->variant->id)) {
            throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        $this->part = clone $this->variant->getPart();

        $variants = $this->part->getOptions();
        $this->variants = \array_filter(
            $variants,
            fn($variant) =>
                $variant->id === $this->variant->id ||
                (($variant->isPublished() || $variant->isArchived()) && !$variant->isDiscontinued())
        );

        $this->characteristics = $this->hyper['helper']['moyskladVariant']->getCharacteristics(
            $this->variants,
            $this->variant
        );

        $this->folder     = $this->part->getFolder();
        $this->properties = $this->folder->getPartFields($this->part->id);

        $this->part->set('option', $this->variant);
        $this->retail = $this->part->isForRetailSale();

        $this->showPurchaseBlock =
            $app->getInput()->get('tmpl') !== 'component' &&
            $this->retail &&
            \in_array($this->variant->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER]);

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

        $this->part->set(
            'name',
            $this->part->getConfiguratorName(
                $app->getInput()->getInt('product_id'),
                true
            )
        );

        $pathway = $app->getPathway();
        $pathway->addItem($this->part->name);

        $menu = $app->getMenu()->getActive();
        if (!\is_object($menu)) {
            $this->getDocument()->setMetaData('robots', 'noindex, nofollow');
        }

        $this->getDocument()->addHeadLink(Uri::current(), 'canonical', 'rel');

        $this->hyper['helper']['meta']->setup($this->part);
        $this->hyper['helper']['opengraph']
            ->setUrl($this->part->getViewUrl(isFull:true))
            ->setTitle($this->part->getPageTitle())
            ->setImage($this->part->getExportImage());

        if ($this->retail) {
            $this->hyper['helper']['google']
                 ->setDataLayerViewProduct($this->part);

            if ($this->showPurchaseBlock) {
                $this->hyper['helper']['google']
                    ->setJsViewItems([$this->variant], true, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_PRODUCT_PAGE'), 'product_page')
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
        if (\in_array($this->variant->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
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
