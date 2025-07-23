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

use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\Form;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Pathway\Pathway;
use HYPERPC\Helper\MacrosHelper;
use HYPERPC\Helper\CompareHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Joomla\Model\ModelAdmin;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Object\SavedConfiguration\CheckData;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Object\Compatibility\CompatibilityDataCollection;

/**
 * Class HyperPcViewConfiguratorMoysklad
 *
 * @property    (PartMarker|MoyskladService)[]  $parts
 * @property    array                           $groups
 * @property    ProductMarker                   $product
 * @property    ProductFolder                   $category
 * @property    string                          $leadForm
 * @property    array                           $groupList
 * @property    array                           $groupParts
 * @property    array                           $productParts
 * @property    array                           $compareItems
 * @property    Money                           $productPrice
 * @property    array                           $defaultGroup
 * @property    array                           $configGroups
 * @property    CompatibilityDataCollection     $compabilities
 * @property    SaveConfiguration               $configuration
 * @property    ProductMarker[]                 $complectations
 * @property    CheckData|null                  $configurationCheckData
 *
 * @since       2.0
 */
class HyperPcViewConfigurator_Moysklad extends ViewLegacy
{

    /**
     * Hook on initialize view.
     *
     * @param   array $config
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->configuration = new SaveConfiguration();
    }

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->hyper['input']->set('tmpl', 'configurator');

        $isCustom = false;
        $configId = $this->hyper['input']->get('id');

        $redirectAttrs = $this->_getRedirectAttrs();

        if (preg_match('/^config-[0-9]/', $configId)) {
            list(, $configurationId) = explode('-', $configId);

            if ($configurationId !== '0') {
                /** @var SaveConfiguration $configuration */
                $this->configuration = $this->hyper['helper']['configuration']->getById($configurationId, ['a.*'], [], false);
                if ($this->configuration->id) {
                    $isCustom = true;
                } else {
                    $redirectAttrs['id'] = 'config';
                    $this->hyper['cms']->redirect($this->hyper['route']->build($redirectAttrs));
                }
            }
        } elseif ($configId !== 'config') {
            throw new Exception(Text::_('COM_HYPERPC_ERROR_PRODUCT_NOT_FOUND'), 404);
        }

        $productId = $this->hyper['input']->get('product_id', 1, 'int');
        $this->product = $this->_getProduct($productId);

        if (empty($this->product->id)) {
            throw new Exception(Text::_('COM_HYPERPC_PART_NOT_EXIST'), 404);
        }

        if (!$this->product->isPublished()) {
            throw new Exception(Text::_('COM_HYPERPC_ERROR_PRODUCT_NOT_FOUND'), 404);
        } elseif ($this->product->isOutOfStock()) {
            $this->hyper['cms']->redirect($this->product->getViewUrl(), 301);
        }

        if ($isCustom === true) {
            $this->product = $this->configuration->prepareProductConfiguration();
        }

        $this->groupList = $this->_getGroupList();
        $this->groups    = $this->_getGroups($this->groupList);

        $filterFieldIds = [];
        foreach ($this->groupList as $group) {
            $filterFiledId = $group->getParams()->get('configurator_filters', 1, 'int');
            if ($filterFiledId > 1) {
                $filterFieldIds[$filterFiledId] = $filterFiledId;
            }
        }

        $this->compabilities = $this->_getCompabilities();
        foreach ($this->compabilities as $compability) {
            $filterFieldIds[$compability->leftField] = $compability->leftField;
            $filterFieldIds[$compability->rightField] = $compability->rightField;
        }

        $this->parts = $this->product->getAllConfigParts([
            'loadFields' => !empty($filterFieldIds),
            'fieldIds'   => $filterFieldIds,
            'selectFields'  => [
                'v.*',
                'f.id',
                'f.name',
                'f.label',
                'c.category_id'
            ]
        ]);

        $groupIds   = [];
        $groupParts = [];
        $options    = $this->_getOptions();

        foreach ($this->parts as $part) {
            if ($part instanceof PartMarker) {
                if ($part->isDiscontinued() || $part->isOutOfStock()) {
                    continue;
                }

                $partOptions = $options[$part->id] ?? [];
                $part->set('options', $partOptions);

                if (!empty($partOptions)) {
                    $defaultOption = $this->hyper['helper']['configurator']->getDefaultOption($this->product, $part);

                    if (!$defaultOption) {
                        continue;
                    }

                    $part->set('option', $defaultOption);
                    $part->setListPrice($defaultOption->getSalePrice());
                } else {
                    $part->setListPrice($part->getSalePrice());
                }
            }

            if (!in_array($part->getFolderId(), $groupIds)) {
                $groupIds[] = $part->getFolderId();
            }

            $groupParts[$part->getFolderId()][$part->id] = $part;
        }

        $includeAccessInConfigPrice = true;

        $this->groupParts     = $this->_sortGroupPartsByPrice($groupParts);
        $this->configGroups   = $this->_getConfiguratorGroupWithFields($groupIds);
        $this->category       = $this->product->getFolder();
        $this->productPrice   = $this->product->getConfigPrice($includeAccessInConfigPrice);
        $this->productParts   = $this->hyper['helper']['configurator']->preparePartsByProducer($this->product, $this->parts);
        $this->compareItems   = $this->_getCompareItems();
        $this->complectations = $this->_getComplectations($this->category, $productId);

        $this->configurationCheckData = $this->configuration->getConfigurationCheckData();

        if ($this->hyper['input']->get('tmpl') !== 'configurator') {
            /** @var Pathway $pathway */
            $pathway = $this->hyper['cms']->getPathway();
            $pathway->addItem($this->product->name, $this->product->getViewUrl());
            $pathway->addItem(Text::_('COM_HYPERPC_CONFIGURATOR_TITLE'));
        }

        $pageTitle = Text::_('COM_HYPERPC_CONFIGURATOR_TITLE') . ' ' . $this->product->getName();

        $this->hyper['helper']['google']
            ->setDataLayerViewProduct(
                $this->product,
                Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_CONFIGURATOR_PAGE'),
                'configurator_page'
            )
            ->setJsViewItems(
                [$this->product],
                false,
                Text::_('COM_HYPERPC_ECOMMERCE_ITEM_LIST_NAME_CONFIGURATOR_PAGE'),
                'configurator_page'
            );

        /** @var MacrosHelper */
        $macrosHelper = $this->hyper['helper']['macros'];
        $macrosHelper->setData($this->product->getArray());

        $metaDescription = HTMLHelper::_('content.prepare', $macrosHelper->text(strip_tags($this->product->metadata->get('meta_desc'))));

        $this->hyper['doc']
            ->setTitle($pageTitle)
            ->setDescription($metaDescription);

        $this->hyper['helper']['opengraph']
            ->setImage($this->product->getOGImage());

        if ($this->hyper['params']->get('conf_save_email', 1)) {
            $this->_setupSaveForm();
        }

        parent::display();
    }

    /**
     * Check if user is owner of the configuration.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function userIsOwner()
    {
        $userId = Filter::int($this->hyper['user']->id);
        return ($userId !== 0 && $this->configuration->created_user_id === $userId);
    }

    /**
     * Get compabilities
     *
     * @return  CompatibilityDataCollection
     *
     * @since   2.0
     */
    protected function _getCompabilities()
    {
        $context = $this->_getContext();
        $compabilities = $this->hyper['helper']['compatibility']->getPublished();
        return CompatibilityDataCollection::fromCompatibilitiesArray(
            array_map(function ($compability) use ($context) {
                $compability->setContext($context);
                return $compability;
            }, $compabilities)
        );
    }

    /**
     * Get compare items
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getCompareItems()
    {
        return $this->hyper['helper']['compare']->getItems(CompareHelper::TYPE_POSITION);
    }

    /**
     * Get complectations
     *
     * @param   ProductFolder $category
     * @param   int           $productId current product id
     *
     * @return  ProductMarker[]
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function _getComplectations($category, $productId)
    {
        if (!$category->params->get('configurator_complectations', false, 'bool')) {
            return [];
        }

        $db = $this->hyper['db'];

        return $category->getProducts([
            $db->qn('a.state') . ' = ' . $db->q(HP_STATUS_PUBLISHED),
            $db->qn('a.on_sale') . ' = 1',
            $db->qn('a.id') . ' != ' . $productId
        ], 'a.list_price ASC', false);
    }

    /**
     * Get groups by ids with com_field field
     *
     * @param   array $groupIds
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getConfiguratorGroupWithFields($groupIds)
    {
        return $this->hyper['helper']['productFolder']->getConfiguratorFields($groupIds, true);
    }

    /**
     * Configuration context
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function _getContext()
    {
        return SaveConfiguration::CONTEXT_MOYSKLAD;
    }

    /**
     * Get group list
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getGroupList()
    {
        /** @var ProductFolderHelper */
        $productFolderHelper = $this->hyper['helper']['productFolder'];

        $db = $productFolderHelper->getDbo();
        $conditions = ['NOT ' . $db->qn('a.alias') . ' = ' . $db->q('root')];

        $rootFolderId = $this->hyper['params']->get('configurator_root_category', 1);
        $rootFolder = $productFolderHelper->findById($rootFolderId);

        if ($rootFolder->id) {
            $conditions[] = $db->qn('a.lft') . ' > ' . $db->q($rootFolder->lft);
            $conditions[] = $db->qn('a.rgt') . ' < ' . $db->q($rootFolder->rgt);
        }

        return $productFolderHelper->findAll([
            'conditions' => $conditions,
            'order' => $db->qn('a.lft') . ' ASC'
        ]);
    }

    /**
     * Get groups tree.
     *
     * @param   array $groupList
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getGroups(array $groupList)
    {
        /** @var HyperPcModelProduct_folder $productFolderModel */
        $productFolderModel = ModelAdmin::getInstance('Product_Folder');

        $rootFolderId = $this->hyper['params']->get('configurator_root_category', 1, 'int');

        return $productFolderModel->buildTree($groupList, $rootFolderId);
    }

    /**
     * Get options
     *
     * @return  OptionMarker[]
     *
     * @since   2.0
     */
    protected function _getOptions()
    {
        return $this->hyper['helper']['moyskladVariant']->getVariants(true, array_keys($this->parts), false);
    }

    /**
     * Get product
     *
     * @param   $productId
     *
     * @return  ProductMarker
     *
     * @since   2.0
     */
    protected function _getProduct($productId)
    {
        return clone $this->hyper['helper']['moyskladProduct']->findById($productId);
    }

    /**
     * Get redirect attrs
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getRedirectAttrs()
    {
        return [
            'view'              => $this->hyper['input']->get('view'),
            'Itemid'            => $this->hyper['input']->get('Itemid'),
            'product_id'        => $this->hyper['input']->get('product_id'),
            'product_folder_id' => $this->hyper['input']->get('product_folder_id')
        ];
    }

    /**
     * Get picked parts in group
     *
     * @param   int $groupId
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getGroupPickedParts($groupId)
    {
        $pickedParts = [];
        $groupParts = $this->groupParts[$groupId];

        foreach ($this->productParts as $partId => $part) {
            if (isset($groupParts[$partId])) {
                $pickedParts[] = $groupParts[$partId];
            }
        }

        return $pickedParts;
    }

    /**
     * Get group total price
     *
     * @param   int $groupId
     *
     * @return  int
     *
     * @since   2.0
     */
    protected function _getGroupTotal($groupId)
    {
        $groupPickedParts = $this->_getGroupPickedParts($groupId);
        $groupTotal = 0;
        $quantityList = new JSON($this->product->configuration->get('quantity', []));
        foreach ($groupPickedParts as $partId => $part) {
            $quantity = $quantityList->get($part->id, 1, 'int');
            $partOptions = $part->get('options', []);
            if (!empty($partOptions)) {
                $pickedOption = $this->product->getDefaultPartOption($part, $partOptions);
                $isOptionInConfigurator = $this->hyper['helper']['configurator']->isOptionInConfigurator($this->product, $pickedOption);
                if ($isOptionInConfigurator) {
                    $groupTotal += $pickedOption->getSalePrice()->val() * $quantity;
                }
            } else {
                $groupTotal += $part->getSalePrice()->val() * $quantity;
            }
        }

        return (int) $groupTotal;
    }

    /**
     * Setup save configuration email form.
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _setupSaveForm()
    {
        Form::addFormPath(JPATH_COMPONENT . '/models/forms');

        $form = Form::getInstance(HP_OPTION . '.configurator_email_form', 'configurator_email_form', [
            'control' => 'jform'
        ]);

        $data = [
            'context'          => $this->_getContext(),
            'product_id'       => $this->product->id,
        ];

        $currentUser = Factory::getUser();
        if ($currentUser->id) {
            $form->removeField('captcha');
        } elseif ($this->hyper['helper']['configurator']->isDebugForm()) {
            $data['username'] = HP_DEV_USERNAME;
            $data['email']    = HP_DEV_EMAIL;
        }

        $form->bind($data);

        $this->leadForm = $form;
    }

    /**
     * Sort parts by price in groups
     *
     * @param   array $groupParts
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _sortGroupPartsByPrice($groupParts)
    {
        foreach ($groupParts as $groupId => $parts) {
            uasort($parts, function ($part1, $part2) {
                return ($part1->getListPrice()->val() <=> $part2->getListPrice()->val());
            });

            foreach ($parts as $part) {
                if (!empty($part->options)) {
                    uasort($part->options, function ($option1, $option2) {
                        return ($option1->getListPrice()->val() <=> $option2->getListPrice()->val());
                    });
                }
            }

            $groupParts[$groupId] = $parts;
        }

        return $groupParts;
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
            ->js('js:widget/configurator.js')
            ->widget('.hp-configurator', 'HyperPC.SiteConfigurator', [
                'context'                       => $this->_getContext(),
                'productId'                     => $this->product->id,
                'productName'                   => $this->product->name,
                'msgTryAgain'                   => Text::_('COM_HYPERPC_AJAX_ERROR_TRY_AGAIN'),
                'compareUrl'                    => $this->hyper['route']->getCompareUrl(),
                'resetConfirmMsg'               => Text::_('COM_HYPERPC_CONFIGURATOR_ALERT_RESET_MSG'),
                'leaveConfirmMsg'               => Text::_('COM_HYPERPC_CONFIGURATOR_ALERT_LEAVE_MSG'),
                'changeComlectationMsg'         => Text::_('COM_HYPERPC_CONFIGURATOR_ALERT_CHANGECOMPLECTATION_MSG'),
                'nothingSelectedImg'            => $this->hyper['helper']['configurator']->getNothingSelectedImageSrc(),
                'hasWarnings'                   => (!empty($this->configurationCheckData) ? $this->configurationCheckData->hasWarnings : false),
                'isInCart'                      => $this->product->isInCart(),
                'txtSave'                       => Text::_('COM_HYPERPC_SAVE'),
                'txtContinue'                   => Text::_('COM_HYPERPC_CONTINUE'),
                'txtLeave'                      => Text::_('COM_HYPERPC_LEAVE'),
                'instockTooltipText'            => Text::_('COM_HYPERPC_CONFIGURATOR_PART_AVAILABILITY_TEXT_INSTOCK'),
                'preorderTooltipText'           => Text::_('COM_HYPERPC_CONFIGURATOR_PART_AVAILABILITY_TEXT_PREORDER'),
                'vat'                           => $this->hyper['params']->get('vat', 20, 'int'),
                'compatibilitiesData'           => $this->compabilities->toArray(),
                'langUpdateInCart'              => Text::_('COM_HYPERPC_CONFIGURATOR_UPDATE_IN_CART'),
                'langSaveNew'                   => Text::_('COM_HYPERPC_CONFIGURATOR_SAVE_NEW'),
                'langConfigInCartModalTitle'    => Text::_('COM_HYPERPC_CONFIGURATOR_CONFIG_IN_CART'),
                'langConfigInCartModalSub'      => Text::_('COM_HYPERPC_CONFIGURATOR_UPDATE_OR_SAVE_NEW'),
                'langNum'                       => Text::_('COM_HYPERPC_NUM'),
                'langSpecification'             => Text::_('COM_HYPERPC_SPECIFICATION'),
                'langConfigurationChanged'      => Text::_('COM_HYPERPC_CONFIGURATION_CHANGED'),
                'langLoginBeforeSaveAlert'      => Text::_('COM_HYPERPC_CONFIGURATOR_ALERT_LOGIN_BEFORE_SAVE'),

                'langCompatibilityModalTitle'                       => Text::_('COM_HYPERPC_COMPATIBILITY_MODAL_TITLE'),
                'langCompatibilityIncompatibleWith'                 => Text::_('COM_HYPERPC_COMPATIBILITY_INCOMPATIBLE_WITH'),
                'langCompatibilityIncompatibleWithCurrentConfig'    => Text::_('COM_HYPERPC_COMPATIBILITY_INCOMPATIBLE_WITH_CURRENT_CONFIG'),
                'langCompatibilityIncompatibleTitle'                => Text::_('COM_HYPERPC_COMPATIBILITY_INCOMPATIBLE_TITLE'),
                'langCompatibilityAutoreplaceTitle'                 => Text::_('COM_HYPERPC_COMPATIBILITY_AUTOREPLACE_TITLE'),
                'langCompatibilityAutoremoveTitle'                  => Text::_('COM_HYPERPC_COMPATIBILITY_AUTOREMOVE_TITLE'),
                'langCompatibilitySubmitButton'                     => Text::_('COM_HYPERPC_COMPATIBILITY_SUBMIT_BUTTON'),
                'langCompatibilityCancelButton'                     => Text::_('COM_HYPERPC_COMPATIBILITY_CANCEL_BUTTON')
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/compare-buttons.js')->widget('body', 'HyperPC.SiteCompareButtons', [
                'compareUrl'             => $this->hyper['route']->getCompareUrl(),
                'addToCompareText'       => Text::_('COM_HYPERPC_COMPARE_ADD_BTN_TEXT'),
                'compareBtn'             => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_BTN'),
                'addToCompareTitle'      => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_ADD'),
                'removeFromCompareTitle' => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE'),
                'removeFromCompareText'  => Text::_('COM_HYPERPC_CONFIGURATOR_COMPARE_REMOVE')
            ]);

        $this->hyper['helper']['assets']
            ->js('js:widget/scrollable-filter.js')
            ->widget('.jsScrollableFilter', 'HyperPC.ScrollableFilter');

        $this->hyper['wa']->useScript('jquery-sticky-sidebar');

        if (!$this->hyper['detect']->isMobile()) {
            $this->hyper['helper']['assets']
                ->js('js:widget/site/configurator-group-nav.js')
                ->widget('.hp-group-nav', 'HyperPC.ConfiguratorGroupNav');
        }

        if ($this->category->params->get('configurator_complectations', false, 'bool')) {
            $this->hyper['helper']['assets']
                ->js('js:widget/site/product-specification.js')
                ->widget('body', 'HyperPC.ProductSpecification');
        }
    }
}
