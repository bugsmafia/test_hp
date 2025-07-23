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
 * @author      Roman Evsyukov
 */

namespace HYPERPC\Joomla\Fields;

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class JFormFieldRelatedComps
 *
 * @since   2.0
 */
abstract class RelatedComps extends FormField
{
    const CONTROL_TYPE_PARTS            = 'parts';
    const CONTROL_TYPE_MINI             = 'mini';
    const CONTROL_TYPE_DEFAULT          = 'default';
    const CONTROL_TYPE_DEFAULT_PART_OPT = 'default_options';

    const CONFIG_PARAM_PARTS            = 'parts';
    const CONFIG_PARAM_OPTIONS          = 'options';
    const CONFIG_PARAM_DEFAULT          = 'default';
    const CONFIG_PARAM_PARTS_MINI       = 'parts_mini';
    const CONFIG_PARAM_OPTIONS_MINI     = 'options_mini';
    const CONFIG_PARAM_PARTS_OPTIONS    = 'part_options';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.related_comps';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'RelatedComps';

    /**
     * Get group modal url helper
     *
     * @param   int $groupId
     *
     * @return  string
     *
     * @since   2.0
     */
    abstract public function getGroupModalUrl(int $groupId);

    /**
     * Get part helper
     *
     * @return  MoyskladPart
     *
     * @since   2.0
     */
    abstract public function getPartHelper();

    /**
     * Get product list
     *
     * @param   array $notProcessedProducts
     *
     * @return  array
     *
     * @since   2.0
     */
    abstract public function getProductList($notProcessedProducts = []);

    /**
     * Get option
     *
     * @param   $formData
     *
     * @return  MoyskladVariant
     *
     * @since   2.0
     */
    abstract public function getOption($formData);

    /**
     * Get part
     *
     * @param   $partId
     *
     * @return  MoyskladPart
     *
     * @since   2.0
     */
    abstract public function getPart($partId);

    /**
     * Check if variable is option
     *
     * @param   $option
     *
     * @return  bool
     *
     * @since   2.0
     */
    abstract public function isOption($option);

    /**
     * Update product
     *
     * @param   $product
     *
     * @return  mixed
     *
     * @since   2.0
     */
    abstract public function updateProduct($product);

    /**
     * Get category id
     *
     * @param   $product
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getCategoryId($product)
    {
        return $product->getFolderId();
    }

    /**
     * Get configuration parts
     *
     * @param   $product
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getConfigurationParts($product)
    {
        return (array) $product->configuration->get(self::CONFIG_PARAM_PARTS);
    }

    /**
     * Get configuration options
     *
     * @param   $product
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getConfigurationOptions($product)
    {
        return (array) $product->configuration->get(self::CONFIG_PARAM_OPTIONS);
    }

    /**
     * Get configuration default
     *
     * @param   $product
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getConfigurationDefault($product)
    {
        return (array) $product->configuration->get(self::CONFIG_PARAM_DEFAULT);
    }

    /**
     * Get options mini
     *
     * @param   $product
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getOptionsMini($product)
    {
        return (array) $product->configuration->get(self::CONFIG_PARAM_OPTIONS_MINI);
    }

    /**
     * Get parts mini
     *
     * @param   $product
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getPartsMini($product)
    {
        return (array) $product->configuration->get(self::CONFIG_PARAM_PARTS_MINI);
    }

    /**
     * Get part options
     *
     * @param   $product
     *
     * @return  array
     *
     * @since   2.0
     */
    public function getPartOptions($product)
    {
        return (array) $product->configuration->get(self::CONFIG_PARAM_PARTS_OPTIONS);
    }

    /**
     * Set configuration params
     *
     * @param   $key
     * @param   $data
     * @param   $product
     *
     * @since   2.0
     */
    public function setConfigurationParams($key, $data, &$product)
    {
        $product->configuration->set($key, $data);
    }

    /**
     * Get current value.
     *
     * @param   null|string $key
     *
     * @return  JSON|mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getCurrentValue($key = null)
    {
        $return = [
            self::CONTROL_TYPE_PARTS            => [],
            self::CONTROL_TYPE_DEFAULT          => [],
            self::CONTROL_TYPE_MINI             => [],
            self::CONTROL_TYPE_DEFAULT_PART_OPT => []
        ];

        $productList = $this->getProductList();

        if ($partId = (int) $this->form->getData()->get('part_id')) {
            $option = (int) $this->form->getData()->get('id');
        } else {
            $partId = (int) $this->form->getData()->get('id');
        }

        //  Find parts in all product configuration if product not exist in the value list.
        foreach ($productList as $product) {
            $configurationParts   = $this->getConfigurationParts($product);
            $configurationOptions = $this->getConfigurationOptions($product);
            $configurationDefault = $this->getConfigurationDefault($product);
            $optionsMini          = $this->getOptionsMini($product);
            $partsMini            = $this->getPartsMini($product);
            $partOptions          = $this->getPartOptions($product);

            if (!in_array($product->id, $return[self::CONTROL_TYPE_PARTS])) {
                if (isset($option) && in_array($option, $configurationOptions)) {
                    $return[self::CONTROL_TYPE_PARTS][] = $product->id;
                }
                if (in_array($partId, $configurationParts)) {
                    $return[self::CONTROL_TYPE_PARTS][] = $product->id;
                }
            }

            if (!in_array($product->id, $return[self::CONTROL_TYPE_DEFAULT])) {
                if (in_array((string) $partId, $configurationDefault)) {
                    $return[self::CONTROL_TYPE_DEFAULT][] = $product->id;
                }
            }

            if (isset($option) && in_array((string) $partId, $optionsMini)) {
                $return[self::CONTROL_TYPE_MINI][] = $product->id;
            }

            if (in_array((string) $partId, $partsMini)) {
                $return[self::CONTROL_TYPE_MINI][] = $product->id;
            }

            foreach ($partOptions as $optionId => $partOption) {
                if (isset($partOption['is_default']) && $partOption['is_default'] === true && $partId === (int) ($partOption['part_id'])) {
                    $return[self::CONTROL_TYPE_DEFAULT_PART_OPT][(int) $partOption['part_id']] = $optionId;
                    if (!in_array($product->id, $return[self::CONTROL_TYPE_DEFAULT])) {
                        $return[self::CONTROL_TYPE_DEFAULT][] = $product->id;
                    }
                }
            }
        }

        $data = new JSON($return);

        return ($key) ? $data->find($key) : $data;
    }

    /**
     * Field callback.
     *
     * @return  string
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function saveConfigsCallback()
    {
        parse_str($this->hyper['input']->get('form', null, 'string'), $output);

        $formData = new JSON($output);
        $return   = new JSON(['result' => false]);

        $option = $this->getOption($formData);

        $partId  = $formData->find('jform.reload_default_part');
        $part    = $this->getPart($partId);

        $options = [];
        if (!$part->isService()) {
            $options = $part->getOptions();
        }

        $notProcessedProducts = $formData->find('jform.not_processed_comps');
        $productIdsInConfig   = (array) $formData->find('jform.params.related_comps.' . self::CONTROL_TYPE_PARTS);
        $productIdsMini       = (array) $formData->find('jform.params.related_comps.' . self::CONTROL_TYPE_MINI, []);

        $productList = $this->getProductList($notProcessedProducts);

        /** @var ProductMarker $product */
        foreach ($productList as $product) {
            $configurationParts         = $this->getConfigurationParts($product);
            $configurationOptions       = $this->getConfigurationOptions($product);
            $configurationPartOptions   = $this->getPartOptions($product);
            $optionsMini                = $this->getOptionsMini($product);
            $partsMini                  = $this->getPartsMini($product);
            $countOptions               = count($options);

            if (in_array((string) $product->id, $productIdsInConfig)) {
                if ($this->isOption($option) && $option->id) {
                    if (!isset($configurationOptions[$option->id])) {
                        $configurationOptions[$option->id] = (string) $option->part_id;
                    }
                    if (!isset($configurationPartOptions[$option->id])) {
                        $configurationPartOptions[$option->id] = [
                            'is_default' => false,
                            'part_id'    => (string) $option->part_id
                        ];
                    }

                    if (!in_array($option->part_id, $configurationParts)) {
                        $configurationParts[$option->part_id] = (string) $option->part_id;
                    }
                } elseif (!array_key_exists($part->id, $configurationParts)) {
                    //  Add part to configuration list.
                    $configurationParts[$part->id] = (string) $part->id;

                    //  Add options to part.
                    if ($countOptions) {
                        foreach ($options as $setOption) {
                            if (!$setOption->isArchived()) {
                                if (!array_key_exists($setOption->id, $configurationOptions)) {
                                    $configurationOptions[$setOption->id] = (string) $part->id;
                                    $configurationPartOptions[$setOption->id] = [
                                        'is_default' => false,
                                        'part_id'    => (string) $part->id
                                    ];
                                }
                            }
                        }
                    }
                }
            } else {
                    //  Remove part option from configuration list.
                if ($this->isOption($option) && isset($option->id)) {
                    if (isset($configurationOptions[$option->id])) {
                        unset($configurationOptions[$option->id]);
                    }

                    if (isset($configurationPartOptions[$option->id])) {
                        unset($configurationPartOptions[$option->id]);
                    }

                    if (!in_array((string) $option->part_id, $configurationOptions)) {
                        unset($configurationParts[$option->part_id]);
                    }
                } else {
                    //  Remove part from configuration list.
                    if (isset($configurationParts[$part->id])) {
                        unset($configurationParts[$part->id]);
                    }

                    if ($countOptions && !isset($option->id)) {

                        foreach ($options as $removeOption) {
                            if (!$removeOption->isArchived()) {
                                if (isset($configurationOptions[$removeOption->id])) {
                                    unset($configurationOptions[$removeOption->id]);
                                }

                                if (isset($configurationPartOptions[$removeOption->id])) {
                                    unset($configurationPartOptions[$removeOption->id]);
                                }
                            }
                        }
                    }
                }
            }

            if (in_array((string) $product->id, $productIdsMini)) {
                //  Add to mini configurator.
                if ($this->isOption($option) && !isset($optionsMini[$option->id]) && $option->id) {
                    $optionsMini[$option->id] = (string) $option->part_id;
                    $return->set('option', $optionsMini[$option->id]);
                } elseif (!in_array((string) $part->id, $partsMini)) {
                    if (isset($configurationParts[$part->id])) {
                        $partsMini[$part->id] = (string) $part->id;
                    }

                    if ($countOptions) {
                        foreach ($options as $removeOption) {
                            if (!$removeOption->isArchived()) {
                                if (!isset($optionsMini[$removeOption->id])) {
                                    $optionsMini[$removeOption->id] = (string) $part->id;
                                }
                            }
                        }
                    }
                }
            } else {
                if ($this->isOption($option) && isset($option->id)) {
                    unset($optionsMini[$option->id]);

                    if (!in_array((string) $part->id, $optionsMini)) {
                        unset($partsMini[$part->id]);
                    }
                } else {
                    //  Remove from mini configurator.
                    if (isset($partsMini[$part->id])) {
                        unset($partsMini[$part->id]);
                    }

                    if ($countOptions && !isset($option->id)) {
                        foreach ($options as $removeOption) {
                            if (!$removeOption->isArchived()) {
                                if (isset($optionsMini[$removeOption->id])) {
                                    unset($optionsMini[$removeOption->id]);
                                }
                            }
                        }
                    }
                }
            }

            $this->setConfigurationParams(self::CONFIG_PARAM_PARTS_MINI, $partsMini, $product);
            $this->setConfigurationParams(self::CONFIG_PARAM_OPTIONS_MINI, $optionsMini, $product);
            $this->setConfigurationParams(self::CONFIG_PARAM_PARTS, $configurationParts, $product);
            $this->setConfigurationParams(self::CONFIG_PARAM_OPTIONS, $configurationOptions, $product);
            $this->setConfigurationParams(self::CONFIG_PARAM_PARTS_OPTIONS, $configurationPartOptions, $product);

            $return->set('result', $this->updateProduct($product));
        }

        return $return->write();
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['wa']->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/related-comps.js')
            ->widget('.hp-part-related-comps', 'HyperPC.FieldRelatedComps', []);

        return parent::getInput();
    }

    /**
     * Get control input name.
     *
     * @param   null|string  $name
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getControlName($name = null)
    {
        $ns = $this->formControl . '[' . $this->group . '][' . $this->fieldname . ']';
        return ($name) ? $ns . '[' . $name . '][]' : $ns . '[]';
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $categories = [];
        $products   = [];

        $list = $this->getProductList();

        if (count($list)) {
            foreach ($list as $product) {
                $categoryId = $this->getCategoryId($product);
                if (!array_key_exists($categoryId, $categories)) {
                    $category = $product->getFolder();
                    if ($category->id) {
                        $categories[$categoryId] = $category;
                    }
                }

                if (!isset($products[$categoryId][$product->id])) {
                    $products[$categoryId][$product->id] = $product;
                }
            }
        }

        return array_merge(parent::getLayoutData(), [
            'products'   => $products,
            'categories' => $categories,
            'fieldType'  => $this->fieldType
        ]);
    }
}
