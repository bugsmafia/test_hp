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

namespace HYPERPC\Joomla\Router;

use HYPERPC\App;
use JBZoo\Utils\Filter;
use Joomla\CMS\Menu\AbstractMenu;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Categories\Categories;
use Joomla\CMS\Application\CMSApplication;
use HYPERPC\Joomla\Router\Rules\OrderRules;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Router\Rules\ConfiguratorRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use HYPERPC\Joomla\Router\Rules\ProductInstockRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\RouterView as BaseRouterView;

/**
 * Class RouterView
 *
 * @since 2.0
 */
class Router extends BaseRouterView
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    protected $_hp;

    /**
     * Flag of remove id from url.
     *
     * @var     bool
     *
     * @since   2.0
     */
    protected $noIDs = true;

    /**
     * HyperPcRouter constructor.
     *
     * @param   CMSApplication|null $app
     * @param   AbstractMenu|null $menu
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(CMSApplication $app = null, AbstractMenu $menu = null)
    {
        $this->_hp = App::getInstance();

        $cart = new RouterViewConfiguration('cart');
        $cart->setKey(false);
        $this->registerView($cart);

        $compare = new RouterViewConfiguration('compare');
        $compare->setKey(false);
        $this->registerView($compare);

        $status = new RouterViewConfiguration('status');
        $status->setKey(false);
        $this->registerView($status);

        $configuratorStepModel = new RouterViewConfiguration('configurator_step_model');
        $configuratorStepModel->setKey(false);
        $this->registerView($configuratorStepModel);

        $stepConfigurator = new RouterViewConfiguration('step_configurator');
        $stepConfigurator->setKey('category_id');
        $this->registerView($stepConfigurator);

        $newCompare = new RouterViewConfiguration('compare_products');
        $newCompare->setKey(false);
        $this->registerView($newCompare);

        $credit = new RouterViewConfiguration('credit');
        $credit->setKey(false);
        $this->registerView($credit);

        $order = new RouterViewConfiguration('order');
        $order->setKey('id');
        $this->registerView($order);

        $dashboard = new RouterViewConfiguration('dashboard');
        $this->registerView($dashboard);

        $productsInStock = new RouterViewConfiguration('products_in_stock');
        $productsInStock->setKey('context');
        $this->registerView($productsInStock);

        $productInStock = new RouterViewConfiguration('product_in_stock');
        $productInStock->setKey('id')->setParent($productsInStock, 'context');
        $this->registerView($productInStock);

        $creditCalculate = new RouterViewConfiguration('credit_calculator');
        $this->registerView($creditCalculate);

        $profileMenu = new RouterViewConfiguration('profile_menu');
        $this->registerView($profileMenu);

        $profileOrders = new RouterViewConfiguration('profile_orders');
        $profileOrders->setKey(false);
        $this->registerView($profileOrders);

        $profileOrder = new RouterViewConfiguration('profile_order');
        $profileOrder->setKey('id')->setParent($profileOrders);
        $this->registerView($profileOrder);

        $profileConfigurations = new RouterViewConfiguration('profile_configurations');
        $profileConfigurations->setKey(false);
        $this->registerView($profileConfigurations);

        $profileReviews = new RouterViewConfiguration('profile_reviews');
        $profileReviews->setKey(false);
        $this->registerView($profileReviews);

        $productFolder = new RouterViewConfiguration('product_folder');
        $productFolder->setKey('id')->setNestable();
        $this->registerView($productFolder);

        $moyskladPart = new RouterViewConfiguration('moysklad_part');
        $moyskladPart->setKey('id')->setParent($productFolder, 'product_folder_id');
        $this->registerView($moyskladPart);

        $moyskladVariant = new RouterViewConfiguration('moysklad_variant');
        $moyskladVariant->setKey('id')->setParent($moyskladPart, 'part_id');
        $this->registerView($moyskladVariant);

        $moyskladService = new RouterViewConfiguration('moysklad_service');
        $moyskladService->setKey('id')->setParent($productFolder, 'product_folder_id');
        $this->registerView($moyskladService);

        $moyskladProduct = new RouterViewConfiguration('moysklad_product');
        $moyskladProduct->setKey('id')->setParent($productFolder, 'product_folder_id');
        $this->registerView($moyskladProduct);

        $configuratorMoysklad = new RouterViewConfiguration('configurator_moysklad');
        $configuratorMoysklad->setKey('id')->setParent($moyskladProduct, 'product_id');
        $this->registerView($configuratorMoysklad);

        parent::__construct($app, $menu);

        $this->attachRule(new ProductInstockRules($this));
        $this->attachRule(new ConfiguratorRules($this));
        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
        $this->attachRule(new OrderRules($this));
    }

    /**
     * Method to get the segment(s) for a cart.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCartSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the segment(s) for a profile order list.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProfile_orderSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the id for a profile order.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  mixed   The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProfile_orderId($segment, $query)
    {
        return $segment;
    }

    /**
     * Method to get the segment(s) for a compare.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCompareSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the segment(s) for a credit.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getCreditSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the id for a configuration.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getConfigurator_MoyskladId($segment, $query)
    {
        $query['view'] = 'configurator_moysklad';
        return $segment;
    }

    /**
     * Method to get the segment(s) for a configurator.
     *
     * @param   string  $id     ID of the configurator to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getConfigurator_MoyskladSegment($id, $query)
    {
        $id = Filter::int($id);
        $categoryId = null;

        $alias    = 'config';
        $category = Categories::getInstance($this->getName(), ['helper' => 'product_folder'])->get($categoryId);

        if ($id > 0) {
            $alias .= '-' . $id;
        }

        if (!strpos($id, ':')) {
            $id .= ':' . $alias;
        }

        if ($category) {
            $path     = array_reverse($category->getPath(), true);
            $path[0]  = '1:root';

            array_unshift($path, $id);

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list(, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return [];
    }

    /**
     * Method to get the id for a option.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getOrderId($segment, $query)
    {
        if ($this->noIDs) {
            /** @var Order $entity */
            $entity = $this->_hp['helper']['order']->getBy('id', $segment, ['a.id'], [], false);
            return $entity->id;
        }

        return (int) $segment;
    }

    /**
     * Method to get the segment(s) for a cart.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getOrderSegment($id, $query)
    {
        return [
            (int) $id => $id . ':' . $id,
            0 => '0:order',
        ];
    }

    /**
     * Method to get the id for a part.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_partId($segment, $query)
    {
        $db = $this->_hp['db'];
        /** @var MoyskladPart $entity */
        $entity = $this->_hp['helper']['moyskladPart']->getBy('alias', $segment, ['a.id'], [
            $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($query['id'])
        ], false);

        if ($this->noIDs && $entity->id !== 0) {
            return $entity->id;
        }

        return $segment;
    }

    /**
     * Method to get the segment(s) for a part.
     *
     * @param   string  $id     ID of the part to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_partSegment($id, $query)
    {
        $entity = $this->_hp['helper']['moyskladPart']->getById($id, ['a.alias'], [], false);

        if (!strpos($id, ':')) {
            /** @var MoyskladPart $entity */
            $id .= ':' . $entity->alias;
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);
            return [$void => $segment];
        }

        return [(int) $id => $id];
    }

    /**
     * Method to get the id for a variant.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_variantId($segment, $query)
    {
        if ($this->noIDs) {
            $db = $this->_hp['db'];

            $query = $db
                ->getQuery(true)
                ->select(['a.id'])
                ->from($db->quoteName(HP_TABLE_MOYSKLAD_VARIANTS, 'a'))
                ->where([
                    $db->quoteName('a.alias')   . ' = ' . $db->quote($segment),
                    $db->quoteName('a.part_id') . ' = ' . $db->quote($query['id'])
                ]);

            $result = $db->setQuery($query)->loadAssoc();
            if (is_array($result)) {
                return $result['id'];
            }
        }

        return $segment;
    }

    /**
     * Method to get the segment(s) for a variant.
     *
     * @param   string  $id     ID of the part to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_variantSegment($id, $query)
    {
        $option = $this->_hp['helper']['moyskladVariant']->findById($id, ['select' => 'a.alias']);

        if (!strpos($id, ':')) {
            $id .= ':' . $option->alias;
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);
            return [$void => $segment];
        }

        return [];
    }

    /**
     * Method to get the id for a product.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_productId($segment, $query)
    {
        $db = $this->_hp['db'];
        if ($this->noIDs) {
            /** @var MoyskladProduct $entity */
            $entity = $this->_hp['helper']['MoyskladProduct']->findBy('alias', $segment, [
                'select' => ['a.id'],
                'conditions' => [
                    $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($query['id'])
                ],
            ]);

            if (!empty($entity->id)) {
                return $entity->id;
            }
        }

        return $segment;
    }

    /**
     * Method to get the segment(s) for a product.
     *
     * @param   string  $id     ID of the product to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_productSegment($id, $query)
    {
        $entity = $this->_hp['helper']['MoyskladProduct']->getById($id, ['a.alias'], [], false);

        if (!strpos($id, ':')) {
            /** @var MoyskladProduct $entity */
            $id .= ':' . $entity->alias;
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);
            return [$void => $segment];
        }

        return [(int) $id => $id];
    }

    /**
     * Method to get the id for a part.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_serviceId($segment, $query)
    {
        $db = $this->_hp['db'];
        /** @var MoyskladService $entity */
        $entity = $this->_hp['helper']['moyskladService']->getBy('alias', $segment, ['a.id'], [
            $db->quoteName('a.product_folder_id') . ' = ' . $db->quote($query['id'])
        ], false);

        if ($this->noIDs && $entity->id !== 0) {
            return $entity->id;
        }

        return $segment;
    }

    /**
     * Method to get the segment(s) for a part.
     *
     * @param   string  $id     ID of the part to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getMoysklad_serviceSegment($id, $query)
    {
        $entity = $this->_hp['helper']['moyskladService']->getById($id, ['a.alias'], [], false);

        if (!strpos($id, ':')) {
            /** @var MoyskladService $entity */
            $id .= ':' . $entity->alias;
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);
            return [$void => $segment];
        }

        return [(int) $id => $id];
    }

    /**
     * Method to get the id for a product folder.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  mixed   The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProduct_folderId($segment, $query)
    {
        if (isset($query['id'])) {
            $folder = Categories::getInstance($this->getName(), ['helper' => 'product_folder'])->get($query['id']);
            if ($folder) {
                foreach ($folder->getChildren() as $child) {
                    if ($this->noIDs) {
                        if ($child->alias == $segment) {
                            return $child->id;
                        }
                    } else {
                        if ($child->id == (int) $segment) {
                            return $child->id;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Method to get the segment(s) for a product folder.
     *
     * @param   string  $id     ID of the group to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProduct_folderSegment($id, $query)
    {
        $folder = Categories::getInstance($this->getName(), ['helper' => 'product_folder'])->get($id);
        if ($folder) {
            $path    = array_reverse($folder->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list(, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return [];
    }

    /**
     * Method to get the segment(s) for a products_in_stock view.
     *
     * @param   string  $id
     * @param   array   $query  The request that is built right now.
     *
     * @since   2.0
     */
    public function getProducts_in_stockSegment($id, $query)
    {
        return [];
    }

    /**
     * Method to get the id for a stock product.
     *
     * @param   string  $segment  Segment to retrieve the ID for.
     * @param   array   $query    The request that is parsed right now.
     *
     * @return  int     The id of this item or false.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProduct_in_stockId($segment, $query)
    {
        $db = $this->_hp['db'];
        if ($this->noIDs) {
            $context = $query['context'] ?? HP_OPTION . '.product';
            if ($context === HP_OPTION . '.product') {
                $query = $db
                    ->getQuery(true)
                    ->select(['a.id'])
                    ->from($db->quoteName(HP_TABLE_PRODUCTS_IN_STOCK, 'a'))
                    ->where([
                        $db->quoteName('a.configuration_id')   . ' LIKE ' . $db->q('%' . $segment),
                    ]);

                $result = $db->setQuery($query)->loadAssoc();

                if (is_array($result)) {
                    return $result['id'];
                }
            }
        }

        return $segment;
    }

    /**
     * Method to get the id for a stock product.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getProduct_in_stockSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the segment(s) for a step configurator.
     *
     * @param   string  $id     ID of the category to retrieve the segments for.
     * @param   array   $query  The request that is built right now.
     *
     * @return  array|string  The segments of this item.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getStep_configuratorSegment($id, $query)
    {
        return [(int) $id => $id];
    }
}
