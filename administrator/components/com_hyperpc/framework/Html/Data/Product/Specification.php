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
 */

namespace HYPERPC\Html\Data\Product;

use JBZoo\Utils\Exception;
use HYPERPC\Joomla\Model\ModelList;
use HYPERPC\Helper\ProductFolderHelper;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\Joomla\View\Html\Data\HtmlData;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class Specification
 *
 * @package HYPERPC\Html\Data\Specification
 *
 * @since   2.0
 */
class Specification extends HtmlData
{
    /**
     * Product item
     *
     * @var   ProductMarker
     *
     * @since 2.0
     */
    protected $product;

    /**
     * Option receive mode
     *
     * @var   string
     *
     * @since 2.0
     */
    protected $optionsMode = 'default';

    /**
     * Load empty groups
     *
     * @var   string
     *
     * @since 2.0
     */
    protected $loadEmptyGroups = false;

    /**
     * Hold clear external parts parameter
     *
     * @var   string
     *
     * @since 2.0
     */
    protected $clearExternalParts = false;

    /**
     * Product specification
     *
     * @var MoyskladVariantHelper
     *
     * @since 2.0
     */
    protected $optionHelper;

    /**
     * Product specification
     *
     * @var ProductFolderHelper
     *
     * @since 2.0
     */
    protected $folderHelper;

    /**
     * Product specification
     *
     * @var array
     *
     * @since 2.0
     */
    protected $specification = [];

    /**
     * Container constructor.
     *
     * @param ProductMarker $product
     * @param bool          $loadEmptyGroups
     * @param bool          $clearExternalParts
     * @param string        $optionsMode
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function __construct($product, $loadEmptyGroups = false, $clearExternalParts = false, $optionsMode = 'default')
    {
        parent::__construct();

        $this->product            = $product;
        $this->optionsMode        = $optionsMode;
        $this->loadEmptyGroups    = $loadEmptyGroups;
        $this->clearExternalParts = $clearExternalParts;

        $this->folderHelper = $this->hyper['helper']['productFolder'];
        $this->optionHelper = $this->hyper['helper']['moyskladVariant'];

        $this->build();
    }

    /**
     * Get product specification
     *
     * @return array
     *
     * @since 2.0
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * Initialize data.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
    }

    /**
     * Build specification.
     *
     * @return  void
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws Exception
     *
     * @since   2.0
     */
    protected function build()
    {
        $this->setSpecification();
    }

    /**
     * Set product specification
     *
     * @throws \JBZoo\SimpleTypes\Exception
     * @throws Exception
     *
     * @since 2.0
     */
    protected function setSpecification()
    {
        // if the product has a saved configuration, assume the product is from an order
        $allowArchive = $this->product->isInStock() || $this->product->saved_configuration ? true : false;

        $order = 'a.product_folder_id ASC';
        $rootCaregory = $this->hyper['params']->get('configurator_root_category', 1);

        $getFromConfig = (bool) $this->product->saved_configuration;

        $parts = $this->product->getConfigParts(true, $order, false, $getFromConfig, $allowArchive);

        if ($this->clearExternalParts) {
            $parts = $this->hyper['helper']['moyskladService']->clearExternalParts($parts);
        }

        $groups = $this->folderHelper->getList();

        $model = ModelList::getInstance('Product_folder');
        if (!method_exists($model, 'buildTree')) {
            $model = ModelList::getInstance('Product_folders');
        }

        /** @var \HyperPcModelProduct_Folder|\HyperPcModelProduct_Folders $model */
        $groupTree = $model->buildTree($groups, (int) $rootCaregory);

        foreach ($groupTree as $parentGroup) {
            if (isset($parentGroup->children)) {
                $childGroups = [];
                foreach ($parentGroup->children as $key => $childGroup) {
                    if (!$childGroup->showInConfig()) {
                        continue;
                    }

                    if (array_key_exists($key, $parts)) {
                        $_parts     = [];
                        $groupParts = $parts[$key];
                        foreach ($groupParts as $part) {
                            $optionName = '';
                            $option     = false;
                            $showUrl    = $part->isPublished() || $part->isArchived();
                            $viewUrl    = $showUrl ? $part->getViewUrl() : '';

                            if ($part instanceof PartMarker) {
                                if (!$part->isReloadContentForProduct($this->product->id)) {
                                    switch ($this->optionsMode) {
                                        case 'fromPart':
                                            if ($part->option?->id) {
                                                $option     = $part->option;
                                                $optionName = $option->getConfigurationName();
                                            }
                                            break;
                                        default:
                                            $partOptions = $this->optionHelper->getPartVariants($part->id, []);
                                            if (count($partOptions) > 0) {
                                                $option = $this->product->getDefaultPartOption($part, $partOptions);
                                                if ($option->id) {
                                                    $optionName = $option->getConfigurationName();
                                                }
                                            }
                                            break;
                                    }
                                } else {
                                    if ($showUrl && !empty($part->getParams()->get('reload_content_desc'))) {
                                        $viewUrl = $part->getViewUrl(['product_id' => $this->product->id]);
                                    } else {
                                        $viewUrl = '';
                                    }
                                }
                            }

                            $_parts[$part->id] = [
                                'partName'   => $part->getConfiguratorName($this->product->id),
                                'quantity'   => $part->quantity,
                                'viewUrl'    => $viewUrl,
                            ];

                            if ($option) {
                                $_parts[$part->id]['optionName'] = $optionName;
                                $_parts[$part->id]['viewUrl']    = $option->getViewUrl();
                            }

                            if ($part->getAdvantages()) {
                                $_parts[$part->id]['advantages'] = $part->getAdvantages();
                            }
                        }

                        $childGroups[$key] = [
                            'alias' => $childGroup->alias,
                            'title' => $childGroup->title,
                            'parts' => $_parts,
                        ];
                    } elseif ($this->loadEmptyGroups) {
                        $childGroups[$key] = [
                            'alias' => $childGroup->alias,
                            'title' => $childGroup->title,
                        ];
                    }
                }

                if ($childGroups) {
                    $this->specification['rootGroups'][$parentGroup->id] = [
                        'alias'  => $parentGroup->alias,
                        'title'  => $parentGroup->title,
                        'groups' => $childGroups
                    ];
                }
            }
        }
    }
}
