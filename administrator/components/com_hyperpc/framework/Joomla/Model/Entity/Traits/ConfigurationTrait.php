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

namespace HYPERPC\Joomla\Model\Entity\Traits;

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;
use JBZoo\Data\JSON;
use JBZoo\Image\Image;
use JBZoo\Utils\Filter;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use HYPERPC\ORM\Entity\Field;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Helper\ImageHelper;
use JBZoo\SimpleTypes\Exception;
use HYPERPC\ORM\Entity\ProductInStock;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Helper\MoyskladVariantHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\ProductFolder;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\MoyskladProduct;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Object\MiniConfigurator\ProductPartData;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Object\SavedConfiguration\PartDataCollection;

/**
 * Trait configuration
 *
 * @package     HYPERPC\Joomla\Model\Entity\Traits
 *
 * @since       2.0
 */
trait ConfigurationTrait
{
    /**
     * Hold configurator parts.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected static $_configParts = [];

    /**
     * Get all configurator items.
     *
     * @param   array $params
     *
     * @return  (PartMarker|MoyskladService)[]
     *
     * @since   2.0
     */
    public function getAllConfigParts(array $params = [])
    {
        $params = new Registry(array_replace_recursive([
            'fieldIds'      => [],
            'groupIds'      => [],
            'published'     => true,
            'loadFields'    => false,
            'loadArchive'   => false,
            'selectFields'  => [
                'v.*',
                'f.*',
                'c.category_id'
            ],
            'order' => 'a.ordering ASC'
        ], $params));

        $fieldIds = (array) $params->get('fieldIds');
        $groupIds = (array) $params->get('groupIds');
        $partIds  = array_keys($this->configuration->get('parts'));

        // todo find how empty parts installed to position
        $partIds  = array_diff($partIds, array(''));

        $tableName = HP_TABLE_POSITIONS;

        $db = $this->hyper['db'];
        $query = $db->getQuery(true)
            ->select(['a.id'])
            ->from($db->quoteName($tableName, 'a'))
            ->where($db->quoteName('a.id') . ' IN (' . implode(',', $partIds) . ')');

        if ($params->get('published')) {
            $publishedStatuses = [HP_STATUS_PUBLISHED, HP_STATUS_ARCHIVED];
            $publishProperty = 'a.state';
            $query->where($db->quoteName($publishProperty) . ' IN (' . implode(',', $publishedStatuses) . ')');
        }

        if (count($groupIds)) {
            $query->where($db->quoteName('a.product_folder_id') . ' IN (' . implode(', ', $groupIds) . ')');
        }

        $query
            ->order($params->get('order'))
            ->order('a.list_price ASC');

        $loadArchive = $params->get('loadArchive', false, 'bool');

        $ids = array_keys($db->setQuery($query)->loadAssocList('id'));
        if (!count($ids)) {
            return [];
        }

        $conditions = [
            'order'      => $params->get('order') . ',a.list_price ASC',
            'conditions' => [
                $db->quoteName('a.id') . ' IN (' . implode(', ', $ids) . ')'
            ]
        ];

        $serviceConditions = $conditions;
        if (!$loadArchive) {
            $serviceConditions['conditions'][] = $db->quoteName('a.state') . ' != ' . HP_STATUS_ARCHIVED;
        }
        $services = $this->hyper['helper']['moyskladService']->findAll($serviceConditions);

        $partConditions = $conditions;
        if (!$loadArchive) {
            $conditions['conditions'][] = 'NOT (' . $db->quoteName('a.state') . ' = ' . HP_STATUS_ARCHIVED . ' AND ' . $db->quoteName('a.balance') . ' = 0)';
        }
        $parts = $this->hyper['helper']['moyskladPart']->findAll($partConditions);

        $list = $services + $parts; /** @todo sort by ids array order */

        if ($params->get('loadFields') === true) {
            $fieldsContext = HP_OPTION . '.position';
            $conditions = [
                $db->quoteName('v.item_id') . ' IN (' . implode(', ', $partIds) . ')',
                $db->quoteName('f.state')   . ' = ' . $db->quote(HP_STATUS_PUBLISHED),
                $db->quoteName('f.context') . ' = ' . $db->quote($fieldsContext),
            ];

            if (count($fieldIds) > 0) {
                $conditions[] = $db->quoteName('f.id') . ' IN (' . implode(', ', $fieldIds) . ')';
            }

            $query = $db
                ->getQuery(true)
                ->select((array) $params->get('selectFields'))
                ->from($db->quoteName('#__fields_values', 'v'))
                ->join('LEFT', $db->quoteName('#__fields', 'f') . ' ON v.field_id = f.id')
                ->join('LEFT', $db->quoteName('#__fields_categories', 'c') . ' ON c.field_id = f.id')
                ->where($conditions)
                ->order($db->quoteName('f.ordering') . ' ASC');

            $_fields = $db->setQuery($query)->loadAssocList();
            $class   = Field::class;
            $fields  = [];
            foreach ($_fields as $id => $item) {
                $fields[$id] = new $class($item);
            }

            if (count($fields)) {
                /** @var Field $field */
                foreach ($fields as $field) {
                    $partId = (int) $field->item_id;

                    if (key_exists($partId, $list)) {
                        $fields    = $list[$partId]->get('fields');
                        $fValues   = (isset($fields)) ? $fields : [];
                        $fValues[] = $field;

                        $list[$partId]->set('fields', $fValues);
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Get part list by config.
     *
     * @param   bool    $compactByGroup        Compact part by group.
     * @param   string  $partOrder             Part SQL query order.
     * @param   bool    $reOrder               Flag of reorder result
     * @param   bool    $partFormConfig        Flag of load parts from saved configuration or product.
     * @param   bool    $loadUnavailableParts  Load parts which availability is outofstock or uncontinued.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  Exception
     *
     * @todo    $partFormConfig param actually doesn't work
     * @todo    g.lft order for positions
     *
     * @since   2.0
     */
    public function getConfigParts($compactByGroup = true, $partOrder = 'a.product_folder_id ASC', $reOrder = false, $partFormConfig = false, $loadUnavailableParts = false)
    {
        $partHelper = $this->hyper['helper']['moyskladPart'];

        $conditions = [];
        $pOptions   = [];
        $db         = $this->hyper['db'];
        $partIds    = (array) $this->configuration->get('default', []);

        //  TODO remove legacy old default saved data.
        if (in_array('on', $partIds)) {
            $partIds = [];
            foreach ($this->configuration->get('default') as $id => $val) {
                $partIds[] = (string) $id;
            }
        }

        if (isset($this->params->get('stock')->id)) {
            $loadUnavailableParts = true;
        }

        //  Get parts from personal configuration.
        $configuration = $this->getConfiguration($reOrder);

        $hash = [$this->id, (int) $compactByGroup, $partOrder, (int) $reOrder, (int) $partFormConfig, (int) $loadUnavailableParts];
        if ($configuration->id > 0) {
            $hash[] = $configuration->id;
        }

        if ($this->params->get('stock') instanceof ProductInStock) {
            $hash[] = $this->params->get('stock')->id;
        }

        $hash = implode('|||', $hash);
        if (!array_key_exists($hash, self::$_configParts) || $reOrder === true) {
            if ($configuration->id > 0) {
                $partIds = $configuration->getPartIds();
            }

            $partIdFromOption = array_keys($pOptions);
            $partIds = array_merge($partIdFromOption, $partIds);

            if (!$configuration->id) {
                $defaultOptions = (array) $this->configuration->get('option', []);
                $partOptions    = (array) $this->configuration->get('part_options', []);
                if (count($partOptions) > 0) {
                    foreach ($partOptions as $index => $data) {
                        if (in_array((string) $index, $defaultOptions)) {
                            $data = new JSON($data);
                            if (!in_array($data->get('part_id'), $partIds)) {
                                array_push($partIds, $data->get('part_id'));
                            }
                        }
                    }
                }
            }

            $options = $this->findDefaultPartsOptions(true)->getArrayCopy();

            asort($partIds);
            if (count($partIds) > 0) {
                if ($partFormConfig === true) {
                    $output = [];
                    $parts  = $configuration->getParts(false, $partOrder);
                    if ($compactByGroup) {
                        foreach ((array) $parts as $part) {
                            $group_id = $part->product_folder_id;

                            $output[$group_id][] = $part;
                        }
                    } else {
                        $output = $parts;
                    }

                    self::$_configParts[$hash] = $output;
                    return self::$_configParts[$hash];
                } else {
                    if ($loadUnavailableParts === false) {
                        $publishField = 'a.state';

                        $conditions = [
                            $db->quoteName($publishField) . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
                        ];
                    }

                    $parts = $partHelper->getByIds($partIds, ['a.*'], $partOrder, null, $conditions, false);

                    $output = [];
                    if (count($parts)) {
                        /** @var PartMarker $part */
                        foreach ($parts as $part) {
                            if (!$loadUnavailableParts && !in_array($part->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
                                continue;
                            }

                            $part->set('quantity', 1);
                            if ($configuration->id) {
                                $pQuantity = $configuration->parts->find($part->id . '.quantity');
                                $part->set('quantity', (int) $pQuantity);
                            } else {
                                $quantity = (array) $this->configuration->get('quantity', []);
                                if (count($quantity)) {
                                    foreach ($quantity as $partId => $q) {
                                        if ($part->id === $partId) {
                                            $part->set('quantity', (int) $q);
                                        }
                                    }
                                }
                            }

                            if (array_key_exists($part->id, $options)) {
                                if (is_array($options[$part->id])) {
                                    /** @var OptionMarker */
                                    $option = array_shift($options[$part->id]);
                                    if (!in_array($option->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER]) && !$loadUnavailableParts) {
                                        continue;
                                    }

                                    $part->set('option', $option);
                                }
                            }

                            if ($compactByGroup) {
                                $group_id = $part->product_folder_id;

                                $output[$group_id][] = $part;
                            } else {
                                $output[] = $part;
                            }
                        }
                    }

                    if ($this instanceof MoyskladProduct) {
                        $services = $this->hyper['helper']['moyskladService']->getByIds($partIds, ['a.*'], $partOrder, null, $conditions, false);

                        if (count($services)) {
                            /** @var MoyskladService $service */
                            foreach ($services as $service) {
                                $service->set('quantity', 1);
                                if ($compactByGroup) {
                                    $output[$service->product_folder_id][] = $service;
                                } else {
                                    $output[] = $service;
                                }
                            }
                        }
                    }

                    if (count($output)) {
                        foreach ($output as $groupId => $groupParts) {
                            if (is_array($groupParts) && count($groupParts) > 1) {
                                $group = current($groupParts)->getGroup();
                                list($sort, $direction) = explode(' ', $group->getPartOrder());

                                if ($sort === 'a.ordering') {
                                    usort($output[$groupId], function ($a, $b) {
                                        return ($a->ordering <=> $b->ordering);
                                    });

                                    if ($direction === 'DESC') {
                                        $output[$groupId] = array_reverse($output[$groupId]);
                                    }
                                }
                            }
                        }

                        self::$_configParts[$hash] = $output;
                        return self::$_configParts[$hash];
                    }
                }
            }
        } else {
            return self::$_configParts[$hash];
        }

        return [];
    }

    /**
     * Get product saved configuration.
     *
     * @param   bool  $isNew
     *
     * @return  SaveConfiguration
     *
     * @since   2.0
     */
    public function getConfiguration($isNew = false)
    {
        /** @var SaveConfiguration $configuration */
        $configuration = $this->hyper['helper']['configuration']->findById($this->saved_configuration, [
            'new' => Filter::bool($isNew)
        ]);

        $configuration->set('order_id', $this->get('order_id'));
        return $configuration;
    }

    /**
     * Get configuration price.
     *
     * @param   bool $includeAccessories Include part price from group of single part params.
     *
     * @return  \JBZoo\SimpleTypes\Type\Money
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     *
     * @todo    $includeAccessories for ProductInStock
     */
    public function getConfigPrice($includeAccessories = false)
    {
        static $configPrices = [];

        $itemKey = $this->getItemKey();

        $hash = md5(json_encode([$itemKey, $includeAccessories, $this->get('order_id')]));
        if (array_key_exists($hash, $configPrices)) {
            return $configPrices[$hash];
        }

        $productServiceItems = $this->hyper['helper']['cart']->getServiceItems();
        $sessionServiceData  = (array) $productServiceItems->find($itemKey);

        $groupKey = 'a.product_folder_id ASC';

        if ($this->params->get('stock') instanceof ProductInStock) {
            $productPice = clone $this->params->get('stock')->price;
            if (empty($sessionServiceData)) {
                $configPrices[$hash] = $productPice;
                return $productPice;
            }

            $parts = $this->getConfigParts(true, $groupKey, false, false, true);
            foreach ($parts as $groupId => $groupParts) {
                if (array_key_exists($groupId, $sessionServiceData)) {
                    $part = array_shift($groupParts);
                    $servicePartData = $sessionServiceData[$groupId];
                    if ($part->id === $servicePartData['id']) {
                        continue;
                    }

                    $servicePrice = $servicePartData['price'];
                    $partPrice    = $part->getListPrice()->val();

                    $priceDifference = $servicePrice - $partPrice;

                    $productPice->add($priceDifference);
                }
            }

            $configPrices[$hash] = $productPice;
            return $productPice;
        }

        $price = clone $this->getListPrice();
        $configuration = $this->getConfiguration();

        $partFromConfig = false;
        if ($configuration->id > 0) {
            $partFromConfig = true;
            $price = clone $configuration->price;
        }

        /** @var Order $order */
        $order = $this->hyper['helper']['order']->findById($this->get('order_id'));
        $items = $this->getConfigParts(false, $groupKey, true, $partFromConfig, true);

        $isInStock = $this->isInStock();

        /** @var PartMarker|MoyskladService $item */
        foreach ($items as $item) {
            $itemPrice = (int) $item->getQuantityPrice(false)->val();
            if (!$isInStock && !$item instanceof MoyskladService) {
                if ($item->option instanceof OptionMarker && $item->option->id) {
                    if (!in_array($item->option->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
                        $itemPrice = (int) $item->option->getQuantityPrice(false)->val();
                        $price->add('-' . $itemPrice);
                        continue;
                    }
                } elseif (!in_array($item->getAvailability(), [Stockable::AVAILABILITY_INSTOCK, Stockable::AVAILABILITY_PREORDER])) {
                    $price->add('-' . $itemPrice);
                    continue;
                }
            }

            $rate = isset($item->rate) ? $item->getRate() : 0;
            $priceReduction = $itemPrice * ($rate / 100);
            if (!$item instanceof MoyskladService && $item->option instanceof OptionMarker) {
                $itemPrice = (int) $item->option->getQuantityPrice(false)->val();
                $priceReduction = $itemPrice * ($rate / 100);
            }

            if ($includeAccessories || !$item->isDetached()) {
                $price->add('-' . $priceReduction);
            } else {
                $price->add('-' . $itemPrice);
            }

            //  Check session service data.
            if (count($sessionServiceData) && !$order->id) {
                foreach ($sessionServiceData as $serviceGroupId => $serviceData) {
                    if ((int) $serviceGroupId === $item->getGroupId() && ($this instanceof MoyskladProduct || !$item->isForRetailSale())) {
                        $price->add($serviceData['price']);
                    }
                }
            }
        }

        $configPrices[$hash] = $price;
        return $price;
    }

    /**
     * Get product configuration url.
     *
     * @param   int     $configId   Saved configuration id.
     * @param   string  $configuratorType
     * @param   array   $query
     *
     * @return  string
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getConfigUrl($configId = 0, $configuratorType = null, array $query = [])
    {
        if (empty($configuratorType)) {
            $configuratorType = $this->getFolder()->params->get('configurator_type', 'default');
        }

        $configuratorView = 'configurator_moysklad';
        if (empty($configId) && $configuratorType === 'step') {
            $configuratorView = 'step_configurator';
        }

        $attrs = [
            'option' => HP_OPTION,
            'view' => $configuratorView,
            'product_id' => $this->id,
        ];

        $attrs['product_folder_id'] = $this->getFolderId();

        if ($configuratorView === 'configurator_moysklad') {
            $attrs['id'] = $configId;
        }

        $queryString = Uri::buildQuery(\array_replace($query, $attrs));

        return Route::_('index.php?' . $queryString);
    }

    /**
     * Get case group id
     *
     * @return  int
     *
     * @since   2.0
     */
    abstract public function getCaseGroupId();

    /**
     * Get customization group ids
     *
     * @return  int[]
     *
     * @since   2.0
     */
    abstract public function getCustomizationGroupIds();

    /**
     * Get min and max days to build
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getDaysToBuild()
    {
        $folder = $this->getFolder();

        $folderHelper = $this->getFolderHelper();

        $minDays = 7;
        $maxDays = 10;
        if ($folder->id) {
            $minDays = $folder->params->get('days_to_build_min', $minDays, 'int');
            $maxDays = $folder->params->get('days_to_build_max', $maxDays, 'int');
        }

        $folders   = $folderHelper->findAll();
        $extraDays = ['min' => 0, 'max' => 0];
        $parts     = $this->getConfigParts(false, 'a.product_folder_id ASC', false, (bool) $this->saved_configuration);

        /** @var PartMarker|MoyskladService $part */
        foreach ($parts as $part) {
            if ($part->isDetached() || !isset($folders[$part->getFolderId()])) {
                continue;
            }

            if ($part instanceof PartMarker) {
                $folder = $folders[$part->getFolderId()];
                if ($folder->params->get('configurator_divide_by_availability', false, 'bool')) {
                    $daysToWarhouse = $this->_getDaysToWarehouse($part);
                    $extraDays['min'] = max($daysToWarhouse['min'], $extraDays['min']);
                    $extraDays['max'] = max($daysToWarhouse['max'], $extraDays['max']);
                }
            }

            $extraDays['min'] = max($part->params->get('increase_days_to_build', 0, 'int'), $extraDays['min']);
            $extraDays['max'] = max($part->params->get('increase_days_to_build', 0, 'int'), $extraDays['max']);
        }

        $result = [
            'min' => $minDays + $extraDays['min'],
            'max' => $maxDays + $extraDays['max'],
            'minForCategory' => $minDays,
            'maxForCategory' => $maxDays,
        ];

        return $result;
    }

    /**
     * Get default part option.
     *
     * @param   MoyskladPart       $part
     * @param   MoyskladVariant[]  $options
     * @param   bool               $checkSession
     *
     * @return  OptionMarker
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getDefaultPartOption(PartMarker $part, array $options = [])
    {
        $defaultOptions = (array) $this->configuration->get('option');
        $partOptions    = (array) $this->configuration->get('part_options');

        if (count($options) === 0) {
            $options = $part->getOptions();
        }

        $configuration = $this->getConfiguration();
        if ($configuration->id > 0) {
            $parts = $configuration->get('parts');
            foreach ($parts as $part) {
                $part = new Data($part);
                if ($part->id === (int) $part->get('id') && array_key_exists($part->get('option_id'), $options)) {
                    return $options[$part->get('option_id')];
                }
            }
        }

        $defaultOptions = array_values($defaultOptions);

        foreach ($defaultOptions as $optionsId) {
            $optionsId = (int) $optionsId;
            if (array_key_exists($optionsId, $partOptions)) {
                $optionData = new JSON($partOptions[$optionsId]);
                if ($optionData->get('part_id', 0, 'int') === $part->id) {
                    if (array_key_exists($optionsId, $options)) {
                        return $options[$optionsId];
                    }
                }
            }
        }

        return new MoyskladVariant([]);
    }

    /**
     * Get configuration image object
     *
     * @var     int $imageMaxWidth
     * @var     int $imageMaxHeight
     *
     * @return  Image
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \LogicException
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function getConfigurationImage($imageMaxWidth = 0, $imageMaxHeight = 0)
    {
        /** @var ImageHelper $imageHelper */
        $imageHelper = $this->hyper['helper']['image'];

        if (!$this->hasNonDefaultImageParts()) {
            goto imageFromTeaser;
        }

        $caseGroup = $this->getCaseGroupId();
        $customizationGroups = $this->getCustomizationGroupIds();

        $partsFromConfig = (bool) $this->saved_configuration;

        $configParts = $this->getConfigParts(true, 'a.product_folder_id ASC', false, $partsFromConfig);

        //  Image from product customization options.
        $customizationGroupPart = null;
        foreach (array_reverse($customizationGroups) as $groupId) {
            if (array_key_exists($groupId, $configParts)) {
                $customizationGroupPart = array_shift($configParts[$groupId]);

                if (!$partsFromConfig && !$customizationGroupPart->option) {
                    $this->setOptionFromConfigInPart($customizationGroupPart);
                }

                $image = $this->getConfigurationImageFromPart($customizationGroupPart, $imageMaxWidth, $imageMaxHeight);
                if (key_exists('thumb', $image) &&
                    $image['thumb'] instanceof Image &&
                    !$imageHelper->isPlaceholder($image['thumb']->getPath())
                    ) {
                    return $image['thumb'];
                }
            }
        }

        //  Image from case part.
        if (array_key_exists($caseGroup, $configParts)) {
            $casePart = array_shift($configParts[$caseGroup]);

            if (!$partsFromConfig && !$casePart->option) {
                $this->setOptionFromConfigInPart($casePart);
            }

            if ($casePart->option) {
                $image = $this->getConfigurationImageFromPart($casePart, $imageMaxWidth, $imageMaxHeight);
            } elseif ($partsFromConfig && !in_array((string) $casePart->id, $this->get('default_configuration', (new JSON([])))->get('default', []))) {
                $image = $this->getConfigurationImageFromPart($casePart, $imageMaxWidth, $imageMaxHeight);
            }

            if (key_exists('thumb', $image) &&
                $image['thumb'] instanceof Image &&
                !$imageHelper->isPlaceholder($image['thumb']->getPath())
                ) {
                return $image['thumb'];
            }
        }

        imageFromTeaser:

        //  Image from teaser.
        $imageList = $this->getImages(true);
        $teaserImagePath = array_shift($imageList);

        return $teaserImagePath ?
            $this->getRender()->customSizeImage($teaserImagePath, $imageMaxWidth, $imageMaxHeight) :
            new Image(JPATH_ROOT . $imageHelper->getPlaceholderPath($imageMaxWidth, $imageMaxHeight));
    }

    /**
     * Does the product have non-default parts in the groups used to display product image
     *
     * @return  bool
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function hasNonDefaultImageParts()
    {
        if (!$this->saved_configuration) {
            return false;
        }

        $casesGroupId = $this->getCaseGroupId();
        $customizationGroupIds = array_reverse($this->getCustomizationGroupIds());
        $groupsForImage = array_merge($customizationGroupIds, [$casesGroupId]);

        $defaultConfiguration = $this->getHelper()->findById($this->id, ['new' => true])->configuration;

        $configuration = $this->getConfiguration();
        $partsData = PartDataCollection::create($configuration->parts->getArrayCopy());

        $hasNotDefaultPart = false;
        $i = 0;

        foreach ($partsData as $id => $partData) {
            if (in_array($partData->group_id, $groupsForImage)) {
                $i++;

                if ($partData->option_id) { // check part with option
                    if (!isset($defaultConfiguration['option'][$id]) ||
                        $defaultConfiguration['option'][$id] !== (string) $partData->option_id) {
                        $hasNotDefaultPart = true;
                        break; // exit from loop if at least 1 meaning group has has a non-default part
                    }
                } else { // check part
                    if (!in_array($id, $defaultConfiguration['default'])) {
                        $hasNotDefaultPart = true;
                        break; // exit from loop if at least 1 meaning group has has a non-default part
                    }
                }
            }

            if ($i === count($groupsForImage)) {
                break; // exit from loop if all meaning groups have been checked
            }
        }

        if ($hasNotDefaultPart) {
            $this->set('default_configuration', $defaultConfiguration);
        }

        return $hasNotDefaultPart;
    }

    /**
     * Get configuration image path
     *
     * @var     int $imageMaxWidth
     * @var     int $imageMaxHeight
     *
     * @return  string path to image
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\Image\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getConfigurationImagePath($imageMaxWidth = 0, $imageMaxHeight = 0)
    {
        $imageSrc = '';

        $image = $this->getConfigurationImage($imageMaxWidth, $imageMaxHeight);
        if ($image instanceof Image) {
            $imageSrc = Uri::getInstance($image->getUrl())->getPath();
        }

        return !empty($imageSrc) ? $imageSrc : $this->hyper['helper']['image']->getPlaceholderPath($imageMaxWidth, $imageMaxHeight);
    }

    /**
     * Get configuration image from certain part
     *
     * @param   PartMarker $part
     * @param   int        $imageMaxWidth
     * @param   int        $imageMaxHeight
     *
     * @return  array|null
     *
     * @throws  \JBZoo\Image\Exception
     *
     * @since   2.0
     */
    public function getConfigurationImageFromPart(PartMarker $part, $imageMaxWidth = 0, $imageMaxHeight = 0)
    {
        $image = null;
        if ($part->option instanceof OptionMarker && $part->option->id) {
            $assembledImage = $part->option->getImageAssembled();
        } else {
            $assembledImage = $part->getImageAssembled();
        }

        if (!empty($assembledImage)) {
            $image = $part->render()->image($imageMaxWidth, $imageMaxHeight, 'hp_part_img', $assembledImage);
        }

        if ($image === null) {
            $image = $part->getItemImage($imageMaxWidth, $imageMaxHeight);
        }

        return $image;
    }

    /**
     * Set option from product configuration to the part object
     *
     * @param   PartMarker $part
     *
     * @since   2.0
     */
    public function setOptionFromConfigInPart(PartMarker &$part)
    {
        $pickedOptions = $this->configuration->get('option', []);

        /** @var MoyskladVariantHelper $optionsHelper */
        $optionsHelper = $this->hyper['helper']['moyskladVariant'];

        if (isset($pickedOptions[$part->id])) {
            $option = $optionsHelper->getById($pickedOptions[$part->id]);
            if ($option instanceof OptionMarker && $option->id) {
                $part->set('option', $option);
            }
        }
    }

    /**
     * Get part options
     *
     * @param   PartMarker $part
     *
     * @return  OptionMarker[]
     *
     * @since   2.0
     */
    public function getPartOptions(PartMarker $part)
    {
        $options = (array) $part->get('options', []);

        if (count($options)) {
            $configOptions = $this->configuration->get('options', []);

            $boundOptions = array_intersect_key($options, $configOptions);
            if (count($boundOptions)) {
                $options = $boundOptions;
            }

            $optionsBuffer = [];

            /** @var OptionMarker $option */
            foreach ($options as $option) {
                if ($option->isDiscontinued()) {
                    continue;
                }

                $optionsBuffer[$option->id] = $option;
            }

            $options = $optionsBuffer;
        }

        return $options;
    }

    /**
     * Get data for render group parts in quick configurator.
     *
     * @param   ProductFolder $folder
     *
     * @return  ProductPartData[]
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getGroupPartsData($folder)
    {
        $fields = $folder->getAllowedMiniPartFields();
        $values = $folder->getAllowedMiniPartFieldValues();

        $defPartId   = $this->hyper['input']->get('d_pid', 0, 'int');
        $defOptionId = $this->hyper['input']->get('d_oid', 0, 'int');
        $items       = $folder->getMiniConfiguratorParts($this);

        if (!isset($items[$defPartId])) {
            throw new \Exception(Text::_('COM_HYPERPC_NOT_FOUND_DEFAULT_PART'), 404);
        }

        $defaultPrice = $items[$defPartId]->getQuantityPrice(false);

        if ($defOptionId) {
            $options = (array) $items[$defPartId]->get('options');

            if (!isset($options[$defOptionId])) {
                throw new \Exception(Text::_('COM_HYPERPC_NOT_FOUND_DEFAULT_PART'), 404);
            }

            $defaultPrice = $options[$defOptionId]->getPrice();
        }

        $entityKey = 'moysklad_product';

        $itemsData = [];
        /** @var PartMarker|MoyskladService $item */
        foreach ($items as $item) {
            $itemKey    = $item->getItemKey();
            $advantages = $item->getAdvantages();
            $options    = $item->options ?? [];

            if (!count($options)) {
                $itemName = $item->name;
                if ($item->get('quantity', 1) > 1) {
                    $itemName = $item->get('quantity') . ' x ' . $itemName;
                }

                $shortDesc  = strip_tags($item->getParams()->get('short_desc'));
                $image      = (array) $item->getRender()->image(self::PART_IMG_WIDTH, self::PART_IMG_HEIGHT);

                $priceDifferrence = clone $item->getQuantityPrice(false);
                $priceDifferrence->add('-' . $defaultPrice->val());

                $changeLinkHref = $this->hyper['route']->build([
                    'd_oid'     => null,
                    'd_pid'     => $item->id,
                    'tmpl'      => 'component',
                    'folder_id' => $folder->id,
                    'id'        => $this->id,
                    'task'      => $entityKey . '.display-group-configurator',
                ]);

                $isContentOverriden = $item->isReloadContentForProduct($this->id);
                if ($isContentOverriden) {
                    $itemName = ($item->getParams()->get('reload_content_name')) ? $item->getParams()->get('reload_content_name') : $itemName;
                    if ($item->getParams()->get('reload_content_short_desc')) {
                        $shortDesc = $item->getParams()->get('reload_content_short_desc');
                    }
                }

                $itemData = new ProductPartData([
                    'itemKey'   => $itemKey,
                    'image'     => $image,
                    'name'      => $this->hyper['helper']['string']->stripSquareBracketContent($itemName),
                    'isDefault' => (!$defOptionId && $defPartId === $item->id),
                    'fields'    => [],
                    'jsData'    => [
                        'option_id'  => 0,
                        'name'       => $itemName,
                        'part_id'    => $item->id,
                        'desc'       => $shortDesc,
                        'url_change' => $changeLinkHref,
                        'folder_id'  => $folder->id,
                        'url_view'   => $item->getViewUrl(),
                        'image'      => $image['thumb']->getUrl(),
                        'advantages' => $advantages,
                    ],
                    'advantages'         => $advantages,
                    'isContentOverriden' => $isContentOverriden,
                    'priceDifference'    => $priceDifferrence,
                ]);

                if (count($fields)) {
                    $itemFields = [];
                    foreach ($fields as $field) {
                        $itemFields[] = [
                            'title' => $field->label,
                            'value' => $values->find($item->id . '.' . $field->id . '.value')
                        ];
                    }

                    $itemData->fields = $itemFields;
                }

                $itemsData[] = $itemData;
            } else {

                /** @var OptionMarker $option */
                foreach ($options as $option) {
                    $itemOptionKey   = $itemKey . '-' . $option->id;
                    $optionName      = $item->name  . ' ' . $option->name;
                    $optionShortDesc = strip_tags($option->getParams()->get('short_desc'));
                    $image           = (array) $option->getRender()->image(self::PART_IMG_WIDTH, self::PART_IMG_HEIGHT);

                    $priceDifferrence = clone $option->getSalePrice();
                    $priceDifferrence->add('-' . $defaultPrice->val());

                    $changeLinkHref = $this->hyper['route']->build([
                        'd_pid'     => $item->id,
                        'd_oid'     => $option->id,
                        'tmpl'      => 'component',
                        'folder_id' => $folder->id,
                        'id'        => $this->id,
                        'task'      => $entityKey . '.display-group-configurator'
                    ]);

                    $optionData = new ProductPartData([
                        'fields'             => [],
                        'isContentOverriden' => false,
                        'image'              => $image,
                        'name'               => $this->hyper['helper']['string']->stripSquareBracketContent($optionName),
                        'itemKey'            => $itemOptionKey,
                        'isDefault'          => ($defOptionId === $option->id && $defPartId === $item->id),
                        'jsData' => [
                            'part_id'    => $item->id,
                            'option_id'  => $option->id,
                            'name'       => $optionName,
                            'url_change' => $changeLinkHref,
                            'folder_id'  => $folder->id,
                            'url_view'   => $option->getViewUrl(),
                            'image'      => $image['thumb']->getUrl(),
                            'desc'       => !empty($optionShortDesc) ? $optionShortDesc : strip_tags($item->getParams()->get('short_desc')),
                            'advantages' => $advantages
                        ],
                        'advantages'      => $advantages,
                        'priceDifference' => $priceDifferrence
                    ]);

                    if (count($fields)) {
                        $optionFields = [];
                        foreach ($fields as $field) {
                            $optionValue = $option->params->find('options.' . $field->name);
                            $optionFields[] = [
                                'title' => $field->label,
                                'value' => $optionValue ?: $values->find($item->id . '.' . $field->id . '.value')
                            ];
                        }

                        $optionData->fields = $optionFields;
                    }

                    $itemsData[] = $optionData;
                }
            }
        }

        usort($itemsData, function ($item1, $item2) {
            return ($item1->priceDifference->val() <=> $item2->priceDifference->val());
        });

        return $itemsData;
    }

    /**
     * Is mini configurator available in group
     *
     * @param   ProductFolder $group
     * @param   Position      $defaultPart
     *
     * @return  bool
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function isMiniConfiguratorAvailableInGroup($group, $defaultPart)
    {
        // todo multiply

        $miniConfiguratorParts = $group->getMiniConfiguratorParts($this);
        if (empty($miniConfiguratorParts) || !isset($miniConfiguratorParts[$defaultPart->id])) {
            return false;
        }

        if (isset($defaultPart->option) && $defaultPart->option instanceof OptionMarker && $defaultPart->option->id) {
            if (!array_key_exists($defaultPart->option->id, $miniConfiguratorParts[$defaultPart->id]->options)) {
                return false;
            }
        }

        if (count($miniConfiguratorParts) >= 2) {
            return true;
        } else { // only one part in mini configurator
            $miniConfiguratorPart = array_shift($miniConfiguratorParts);
            if (isset($miniConfiguratorPart->options) && count($miniConfiguratorPart->options) >= 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check mini part and options.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function hasPartsMini()
    {
        $partsMini   = (array) $this->configuration->get('parts_mini');
        $optionsMini = (array) $this->configuration->get('options_mini');

        foreach ((array) $this->configuration->get('default') as $defaultPartId) {
            $defaultPartId = (int) $defaultPartId;
            if (isset($partsMini[$defaultPartId])) {
                unset($partsMini[$defaultPartId]);
            }
        }

        foreach ($this->configuration->get('option', []) as $optionPartId => $optionId) {
            $optionId     = (int) $optionId;
            $optionPartId = (int) $optionPartId;

            if (isset($partsMini[$optionPartId])) {
                unset($partsMini[$optionPartId]);
            }

            if (isset($optionsMini[$optionId])) {
                unset($optionsMini[$optionId]);
            }

            if (count($optionsMini)) {
                foreach ($optionsMini as $oOptionId => $oPartId) {
                    if ((int) $oPartId === $optionPartId) {
                        unset($optionsMini[$oOptionId]);
                    }
                }
            }
        }

        return (count($partsMini) + count($optionsMini));
    }

    /**
     * Get default parts option list.
     *
     * @param   bool $compactByPart     Compact options by part.
     *
     * @return  JSON
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function findDefaultPartsOptions($compactByPart = false)
    {
        $options = (array) $this->configuration->get('part_options');
        $defaultOptions = (array) $this->configuration->get('option');

        $return = [];
        if (count($options)) {
            $optionList = $this->hyper['helper']['moyskladVariant']->getVariants();
            foreach ($options as $id => $data) {
                $data = new JSON($data);
                if ($data->get('is_default') && in_array((string) $id, $defaultOptions)) {
                    if (array_key_exists($data->get('part_id'), $optionList)) {
                        $partOptions = $optionList[$data->get('part_id')];
                        if (count($partOptions)) {
                            /** @var OptionMarker $option */
                            foreach ($partOptions as $option) {
                                if (!isset($return[$option->part_id])) {
                                    if (!isset($return[$option->part_id][$option->id]) && $id === $option->id && $compactByPart === true) {
                                        $return[$option->part_id][$option->id] = $option;
                                    }
                                }

                                if (!array_key_exists($id, $return) && $id === $option->id && $compactByPart === false) {
                                    $return[$option->id] = $option;
                                }
                            }
                        }
                    }
                }
            }
        }

        return new JSON($return);
    }

    /**
     * Convert product data to save configuration data.
     *
     * @return  Data
     *
     * @throws  \Exception
     * @throws  Exception
     *
     * @since   2.0
     */
    public function toSaveConfiguration()
    {
        $data = [
            'parts'         => [],
            'options'       => [],
            'part_quantity' => []
        ];

        $parts = $this->getConfigParts(true, 'a.product_folder_id ASC');

        if (count($parts)) {
            foreach ($parts as $groupId => $_items) {
                $countItem = count($_items);
                if ($countItem) {
                    /** @var MoyskladPart|MoyskladService $item */
                    foreach ($_items as $item) {
                        if ($countItem > 1) {
                            $data['parts'][$groupId][] = $item->id;
                        } else {
                            if (!isset($data['parts'][$groupId])) {
                                $data['parts'][$groupId] = $item->id;
                            }
                        }

                        if (isset($item->option) && $item->option instanceof MoyskladVariant) {
                            if (!isset($data['options'][$item->id])) {
                                $data['options'][$item->id] = $item->option->id;
                            }
                        }

                        if (!isset($data['part_quantity'][$item->id])) {
                            $quantity = ($item->quantity === 0) ? 1 : $item->quantity;
                            $data['part_quantity'][$item->id] = $quantity;
                        }
                    }
                }
            }
        }

        return new Data($data);
    }

    /**
     * Get min and max days to delivery to the warehouse
     *
     * @todo find a better place for this method and for getDaysToPreorder
     *
     * @param   PartMarker $part
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function _getDaysToWarehouse(PartMarker $part)
    {
        $result = [
            'min' => 0,
            'max' => 0
        ];
        $entity = $part;
        $option = $part->option;
        if ($option instanceof OptionMarker && $option->id) {
            $entity = $option;
        }

        $availability = $entity->getAvailability();
        if ($availability === Stockable::AVAILABILITY_PREORDER) {
            $daysToPreorder = $this->hyper['helper']['moyskladPart']->getDaysToPreorder($entity);
            $result['min'] = $daysToPreorder['min'];
            $result['max'] = $daysToPreorder['max'];
        } elseif ($availability !== Stockable::AVAILABILITY_INSTOCK) {
            $result['min'] = null;
            $result['max'] = null;
        }

        return $result;
    }
}
