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
 * @author      Artem Vyshnevskiy
 */

use HYPERPC\Data\JSON;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Compare\Product\CompareFactory;
use HYPERPC\Object\Compare\CategoryTree\CategoryTree;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewCompare_Products
 *
 * @property    JSON            $items
 * @property    CategoryTree    $categoriesTree
 *
 * @since       2.0
 */
class HyperPcViewCompare_Products extends ViewLegacy
{

    /**
     * Display view action.
     *
     * @param   null $tpl
     * @return  mixed|void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $menuParams = new Registry();
        if ($menu = $this->hyper['app']->getMenu()->getActive()) {
            $menuParams->loadString($menu->getParams());
        }

        $compareType = $menuParams->get('compare_type', 'Product');
        $categoryTreeData = json_decode(json_encode($menuParams->get('compare_tree', [])), true); // convert stdclass to array recursevly
        $descriptionGroupIds = $menuParams->get('compare_descriptions', []);

        $compare = (new CompareFactory)::createCompare($compareType);
        $compare->setCategoryTreeData($categoryTreeData);
        $compare->setDescriptionGroupIds($descriptionGroupIds);

        $this->items = $compare->getComparedProducts();
        $this->categoriesTree = $compare->getCategoriesTree();

        parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();

        $this->hyper['helper']['assets']
            ->js('js:widget/item-buttons.js')->widget('body', 'HyperPC.SiteItemButtons', [
                'cartUrl'                => $this->hyper['helper']['cart']->getUrl(),
                'msgAlertError'          => Text::_('COM_HYPERPC_ALERT_ERROR'),
                'msgTryAgain'            => Text::_('COM_HYPERPC_ALERT_TRY_AGAIN'),
                'msgWantRemove'          => Text::_('COM_HYPERPC_ALERT_WANT_TO_REMOVE'),
                'langAddedToCart'        => Text::_('COM_HYPERPC_ADDED_TO_CART'),
                'langContinueShopping'   => Text::_('COM_HYPERPC_CONTINUE_SHOPPING'),
                'langGoToCart'           => Text::_('COM_HYPERPC_GO_TO_CART')
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/compare-buttons.js')
            ->js('js:widget/compare-products-buttons.js')
            ->widget('body', 'HyperPC.SiteCompareButtons.CompareProducts', [
                'compareUrl'             => '',
                'compareBtn'             => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_BTN'),
                'addToCompareTitle'      => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_ADD'),
                'removeFromCompareTitle' => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE'),
                'addToCompareText'       => Text::_('COM_HYPERPC_COMPARE_ADD_BTN_TEXT'),
                'removeFromCompareText'  => Text::_('COM_HYPERPC_COMPARE_REMOVE_BTN_TEXT'),
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/compare.js')
            ->js('js:widget/compare-products.js')
            ->widget('.hp-compare', 'HyperPC.SiteCompare.Products', []);
    }
}
