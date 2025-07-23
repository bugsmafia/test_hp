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

use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Helper\Context\EntityContext;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcViewConfigurator_Step_Model
 *
 * @property    int              $currentStep
 * @property    array            $categoriesTree
 * @property    ProductFolder[]  $categories
 * @property    EntityContext    $categoryHelper
 * @property    int              $activeCategoryId
 * @property    int              $pageContentModuleId
 * @property    int              $categoryThumbSize
 * @property    int              $categoryImgSize
 *
 * @since    2.0
 */
class HyperPcViewConfigurator_Step_Model extends ViewLegacy
{
    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->currentStep = 2;

        $activeMenuItem = $this->hyper['app']->getMenu()->getActive();
        $this->categoriesTree = $activeMenuItem->getParams()->get('categories');

        $this->pageContentModuleId = $activeMenuItem->getParams()->get('description', 0, 'int');

        $categoryIds = [];
        foreach ($this->categoriesTree as $root) {
            $categoryIds = array_merge((array) $root->child, $categoryIds);
        }

        $categoryHelperName = $activeMenuItem->getParams()->get('category_helper', 'category');
        $this->categoryHelper = $this->hyper['helper'][$categoryHelperName];
        /** @var (Category|ProductFolder)[] */
        $this->categories = $this->categoryHelper->findById($categoryIds, [
            'conditions' => [
                $this->hyper['db']->qn('a.published') . ' = ' . HP_STATUS_PUBLISHED
            ]
        ]);

        foreach ($this->categoriesTree as $root) {
            foreach ((array) $root->child as $i => $categoryId) {
                if (!array_key_exists($categoryId, $this->categories)) {
                    unset($root->child[$i]);
                }
            }
        }

        /** @todo check it */
        $this->activeCategoryId = $this->hyper['input']->get('category_id', 0, 'int');
        if (empty($this->activeCategoryId) || !array_key_exists($this->activeCategoryId, $this->categories)) {
            $firstCategoryGroup = current($this->categoriesTree);
            $this->activeCategoryId = current($firstCategoryGroup->child);
        }

        $this->categoryThumbSize = 133;
        $this->categoryImgSize = 450;

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
            ->js('js:widget/site/configurator-step-model.js')
            ->widget('.hp-step-configurator', 'HyperPCConfiguratorStepModel');

        $this->hyper['helper']['assets']
            ->js('js:widget/site/configurator-sticky-bottom.js')
            ->widget('.hp-step-configurator', 'HyperPCConfiguratorStickyBottom');
    }
}
