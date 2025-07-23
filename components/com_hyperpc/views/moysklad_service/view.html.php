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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pathway\Pathway;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladService;

/**
 * Class HyperPcViewMoysklad_Service
 *
 * @property    MoyskladService $service
 * @property    ProductFolder $folder
 * @property    array   $properties
 * @property    bool    $showPurchaseBlock
 * @property    bool    $retail
 *
 * @since       2.0
 */
class HyperPcViewMoysklad_Service extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $part_id    = $this->hyper['input']->get('id');
        $folder_id  = $this->hyper['input']->get('product_folder_id');

        $this->service = $this->hyper['helper']['moyskladService']->getById($part_id);

        if ($this->service->id === 0 || $this->service->isTrashed()) {
            $this->hyper['cms']->redirect(Route::_('index.php?option=com_hyperpc&view=product_folder&id=' . $folder_id), 301);
        }

        $this->folder = $this->service->getFolder();
        if (!in_array($this->folder->published, [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED])) {
            throw new Exception(Text::_('COM_HYPERPC_ERROR_PAGE_NOT_FOUND'), 404);
        }

        $this->properties = $this->folder->getPartFields($this->service->id, ['context' => 'position']);

        $service = clone $this->service;

        $this->showPurchaseBlock = (
            $this->service->isPublished() &&
            $this->service->isForRetailSale() &&
            $this->hyper['input']->get('tmpl') !== 'component'
        );

        $this->hyper['helper']['meta']->setup($service);

        $this->hyper['helper']['opengraph']
            ->setImage($this->service->images->get('image', '', 'hpimagepath'));

        /** @var Pathway $pathway */
        $pathway = $this->hyper['cms']->getPathway();
        $pathway->addItem($service->name);

        $app  = $this->hyper['app'];
        $menu = $app->getMenu()->getActive();
        if (!is_object($menu)) {
            $this->hyper['doc']->setMetaData('robots', 'noindex, nofollow');
        }

        $this->hyper['helper']['google']
             ->setDataLayerViewProduct($service);

        if ($this->showPurchaseBlock) {
            $this->hyper['helper']['google']
                ->setJsViewItems([$service], false, Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_PRODUCT_PAGE'), 'product_page')
                ->setDataLayerAddToCart();
        }

        parent::display($tpl);
    }
}
