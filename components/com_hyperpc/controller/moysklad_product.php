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
use HYPERPC\Joomla\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoyskladFilterHelper;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\View\Html\Data\Manager;
use MoySklad\Entity\Product\ProductFolder;
use HYPERPC\Elements\ElementProductService;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\ORM\Filter\Manager as FilterManager;
use HYPERPC\Joomla\View\Html\Data\Product\Filter;

/**
 * Class HyperPcControllerMoysklad_Product
 *
 * @since   2.0
 */
class HyperPcControllerMoysklad_Product extends ControllerLegacy
{
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
        $this->registerTask('service', 'service')
             ->registerTask('ajax-filter', 'ajaxFilter')
             ->registerTask('service-save-session', 'serviceSaveSession')
             ->registerTask('get-specification-html', 'getSpecificationHtml')
             ->registerTask('display-group-configurator', 'displayGroupConfigurator');
    }

    /**
     * Service view.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function service()
    {
        $document = Factory::getApplication()->getDocument();
        $viewType = $document->getType();
        $viewName = 'moysklad_product';

        /** @var HyperPcViewMoysklad_Product $view */
        $view = $this->getView($viewName, $viewType, '', [
            'layout'    => 'default',
            'base_path' => $this->basePath
        ]);

        if ($model = $this->getModel($viewName)) {
            $view->setModel($model, true);
        }

        $view->document = $document;

        $view->displayService();
    }

    /**
     * Filter products.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function ajaxFilter()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result'  => false,
            'message' => null,
            'html'    => null
        ]);

        $filter = 'MoyskladProductInStock';

        /** @var Filter $filterData */
        $filterData = Manager::getInstance()->get('Product.Filter', [
            'filter' => FilterManager::getInstance()->get($filter)
        ]);

        $allowedFilterFields = $filterData->getAllowedFilterFields();
        if (count($allowedFilterFields) <= 0) {
            $output->set('output', Text::_('COM_HYPERPC_ERROR_PRODUCT_FILTER_FIELDS_EMPTY'));
            $this->hyper['cms']->close($output->write());
        }

        $manager = FilterManager::getInstance();
        $filter  = $manager->get($this->hyper['input']->get('type'));

        if ($filter === null) {
            $output->set('output', Text::_('COM_HYPERPC_ERROR_PRODUCT_FILTER_TYPE_NOT_FOUND'));
            $this->hyper['cms']->close($output->write());
        }

        $filter->setFilterData($this->hyper['input']->get('filters', [], 'array'));

        $filter
            ->find()
            ->setFieldOptionsCount();

        $this->hyper['input']->set('view', 'products_in_stock');

        $htmlOutput = $filter->render();

        $output
            ->set('result', true)
            ->set('html', $htmlOutput)
            ->set('url', $filter->getUrlQuery())
            ->set('resultCount', $filter->getItemCount())
            ->set('filters', [
                'available' => $filter->getFieldOptionsCount(),
                'current'   => $filter->getCurrentFilters()
            ]);

        if (empty($htmlOutput)) {
            $output
                ->set('result', false)
                ->set('message', Text::_('COM_HYPERPC_FILTERS_RESULT_NOT_FOUND'));
        }

        /** @var MoyskladFilterHelper $filterHelper */
        $filterHelper = $this->hyper['helper']['moyskladFilter'];
        if ($filterHelper->isDebugMode()) {
            $output->set('dbQuery', $filter->getQueryDump());
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Service save session.
     *
     * @return  void
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function serviceSaveSession()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result'  => false,
            'message' => null
        ]);

        $productKey = $this->hyper['input']->get('item-key');

        /** @var MoyskladProduct $product */
        $product = $this->hyper['helper']['moyskladProduct']->findById($this->hyper['input']->get('product-id'));
        if ($product->id === null) {
            $output->set('message', Text::_('COM_HYPERPC_NOT_FOUND_PRODUCT'));
            $this->hyper['cms']->close($output->write());
        }

        $configId = $this->hyper['input']->get('config-id');
        if ($configId) {
            $product->set('saved_configuration', $configId);
        }

        /** @var ProductFolder $group */
        $group = $this->hyper['helper']['productFolder']->findById($this->hyper['input']->get('group-id'));
        if (!$group->id) {
            $output->set('message', Text::_('COM_HYPERPC_NOT_FOUND_GROUP'));
            $this->hyper['cms']->close($output->write());
        }

        /** @var MoyskladService $service */
        $service = $this->hyper['helper']['moyskladService']->findById($this->hyper['input']->get('service-id'));
        if (!$service->id) {
            $output->set('message', Text::_('COM_HYPERPC_NOT_FOUND_SERVICE'));
            $this->hyper['cms']->close($output->write());
        }

        $serviceVal    = null;
        $serviceElType = $service->getType();

        if ($serviceElType instanceof ElementProductService) {
            $serviceVal = $serviceElType->getType();
        }

        $service->list_price->set($this->hyper['input']->get('price'));

        $this->hyper['helper']['cart']->addServiceItem(
            $productKey,
            $group->id,
            $serviceVal,
            $service->list_price->val(),
            $service->id
        );

        $output
            ->set('result', true)
            ->set('type', $serviceVal)
            ->set('name', $service->getConfiguratorName($product->id))
            ->set('price', $service->list_price->val())
            ->set('price_format', $service->list_price->html())
            ->set('price_quantity', $product->getConfigPrice()->val())
            ->set('url', $this->hyper['route']->build([
                'group_id'  => $group->id,
                'item-key'  => $productKey,
                'tmpl'      => 'component',
                'd_pid'     => $service->id,
                'id'        => $product->id,
                'task'      => 'moysklad_product.service',
                'config_id' => $product->saved_configuration
            ]));

        $promoCode = $this->hyper['helper']['promocode']->getSessionData();

        $baseDiscount = $product->getDiscount();
        $price        = $product->getConfigPrice()->getClone();

        $product->setListPrice($price);
        $product->setSalePrice($price);

        $discount = $this->hyper['helper']['cart']->getPositionRate($product);
        $discount = floatval($discount);

        $discount = max($baseDiscount, $discount);

        if (!empty($promoCode->get('type'))) {
            $output->set('promoType', $promoCode->get('type'));
            $output->set('discount', $discount);
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Display group configuration.
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function displayGroupConfigurator()
    {
        $document = Factory::getDocument();
        $viewType = $document->getType();
        $viewName = 'moysklad_product';

        /** @var HyperPcViewMoysklad_Product $view */
        $view = $this->getView($viewName, $viewType, '', [
            'layout'    => 'default',
            'base_path' => $this->basePath
        ]);

        if ($model = $this->getModel($viewName)) {
            $view->setModel($model, true);
        }

        $view->document = $document;

        $view->displayGroupParts();
    }

    /**
     * Find specification by configuration id.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getSpecificationHtml()
    {
        $output = new JSON([
            'result'  => false,
            'message' => null
        ]);

        /** @var MoyskladProductHelper $productHelper */
        $productHelper = $this->hyper['helper']['moyskladProduct'];

        $itemKey = $this->hyper['input']->get('item_key');
        $ids = $productHelper->parseItemkey($itemKey);

        $configuration = null;
        if ($ids->get('configuration', null)) {
            $configuration = $this->hyper['helper']['configuration']->getById($ids->get('configuration'));

            if (empty($configuration->id)) {
                $output->set('message', Text::_('COM_HYPERPC_NOT_FOUND_PRODUCT'));
                $this->hyper['cms']->close($output->write());
            }
        } else {
            $product = $productHelper->findById($ids->get('product'));
        }

        if ($ids->get('type', 'product') === 'position') {
            $product = $product ?? $configuration->getProduct();
            $render = $product->getRender();
            $render->setEntity($product);
            $specsHtml = $render->configuration();
        }

        $output
            ->set('html', $specsHtml)
            ->set('result', true);

        $this->hyper['cms']->close($output->write());
    }
}
