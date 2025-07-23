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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use JBZoo\Utils\Url;
use Joomla\CMS\Menu\MenuItem;
use HYPERPC\Money\Type\Money;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoneyHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * Class HyperPcViewStep_Configurator
 *
 * @property ProductFolder  $category
 * @property array          $complectations
 * @property int[]          $availableComplectations
 * @property int            $activeComplectation
 * @property string[]       $platform
 * @property array          $platformState
 * @property int            $currentStep
 * @property string         $prevStep
 * @property Registry       $params
 * @property MenuItem       $menuItem
 * @property Money          $minPrice
 *
 * @since    2.0
 */
class HyperPcViewStep_Configurator extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->menuItem = $this->hyper['app']->getMenu()->getActive();
        $this->params = $this->menuItem->getParams();

        if ($this->getLayout() === 'exclude-platform-moysklad') {
            $this->setLayout('exclude-platform');
            $this->setModel(ModelAdmin::getInstance('Step_Configurator_Moysklad'), true);
        }

        $this->category = $this->get('Category');
        $this->state    = $this->get('Complectations');

        $this->complectations = $this->state->getComplectations();

        if (!count($this->complectations)) {
            $redirectUrl = $this->hyper['route']->build([
                    'view' => 'category',
                    'id'   => $this->hyper['input']->get('category_id'),
                ]);

            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_NOT_SALE_PRODUCT'), 'error');
            $this->hyper['cms']->redirect($redirectUrl, 403);
        }

        $this->availableComplectations = $this->state->getAvailableComplectations();
        $this->activeComplectation = $this->state->getActiveComplectation();
        $this->platform = $this->state->getPlatform();
        $this->platformState = $this->state->getPlatformState();

        $this->currentStep = 3;
        if ($this->getLayout() === 'exclude-platform') {
            $this->currentStep = 4;
            $this->availableComplectations = array_keys($this->complectations);
        }

        $this->prevStep = $this->_getPrevStep();

        $this->minPrice = $this->_getMinPrice();

        if ($this->hyper['input']->get->count()) {
            $this->hyper['doc']->addHeadLink(Url::pathToUrl($this->hyper['route']->build()), 'canonical', 'rel');
        }

        parent::display($tpl);
    }

    /**
     * Get active complectation name
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getActiveComplectationName()
    {
        return $this->complectations[$this->activeComplectation]['name'];
    }

    /**
     * Get active complectation price
     *
     * @return  Money
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getActiveComplectationPrice()
    {
        return $this->_getComplectationPrice($this->activeComplectation);
    }

    /**
     * Get complectation price
     *
     * @param   int $id
     *
     * @return  Money
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _getComplectationPrice($id)
    {
        $priceInt = $this->complectations[$id]['price'];
        return $this->hyper['helper']['money']->get($priceInt);
    }

    /**
     * Get minimal complectation price
     *
     * @return Money
     *
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since  2.0
     */
    protected function _getMinPrice()
    {
        $availableComplectations = array_intersect_key($this->complectations, array_flip($this->availableComplectations));

        $priceInt = array_shift($availableComplectations)['price'];

        /** @var MoneyHelper */
        $moneyHelper = $this->hyper['helper']['money'];

        return $moneyHelper->get($priceInt);
    }

    /**
     * Get prev step url
     *
     * @return string
     *
     * @since  2.0
     */
    protected function _getPrevStep()
    {
        $activeMenuItem = $this->hyper['app']->getMenu()->getActive();
        $prevStepMenuItemId = $activeMenuItem->getParams()->get('prev_step');

        if (empty($prevStepMenuItemId)) {
            return '';
        }

        $prevStepMenuItem = $this->hyper['app']->getMenu()->getItem($prevStepMenuItemId);

        return $prevStepMenuItem->route;
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
            ->js('js:widget/site/configurator-steps.js')
            ->widget('.hp-step-configurator', 'HyperPC.ConfiguratorSteps', [
                'complectations' => $this->complectations,
                'availableComplectations' => $this->availableComplectations,
                'activeComplectation' => $this->activeComplectation,
                'platform' => $this->platform,
                'platformState' => $this->platformState,
                'currentStep' => $this->currentStep,
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/site/configurator-sticky-bottom.js')
            ->widget('.hp-step-configurator', 'HyperPCConfiguratorStickyBottom');

        $this->hyper['helper']['assets']
            ->js('js:widget/site/product-specification.js')
            ->widget('body', 'HyperPC.ProductSpecification');
    }
}
