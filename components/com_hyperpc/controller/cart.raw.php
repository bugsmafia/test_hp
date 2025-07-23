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

defined('_JEXEC') or die('Restricted access');

use JBZoo\Data\Data;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use HYPERPC\Helper\CartHelper;
use HYPERPC\Helper\FacebookHelper;
use HYPERPC\Helper\PromocodeHelper;
use HYPERPC\Joomla\Model\ModelList;
use Joomla\CMS\Date\Date as JoomlaDate;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Helper\MoyskladProductHelper;
use HYPERPC\Joomla\Model\Entity\Position;
use HYPERPC\Joomla\Model\Entity\PromoCode;
use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class HyperPcControllerCart
 *
 * @since 2.0
 */
class HyperPcControllerCart extends ControllerLegacy
{

    const SAVE_CONF_METHOD_TOGGLE_OPTION = 'toggle-option';
    const SAVE_CONF_METHOD_TOGGLE_PARTS  = 'toggle-parts';

    /**
     * Create product custom configuration and add in to the cart.
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function addProductAndCreateConfig()
    {
        $output = new JSON(['result' => false]);

        /** @var MoyskladProductHelper $productHelper */
        $productHelper = $this->hyper['helper']['moyskladProduct'];

        $product = $productHelper->getById($this->hyper['input']->get('id'));
        $method  = $this->hyper['input']->get('method', self::SAVE_CONF_METHOD_TOGGLE_OPTION);

        if ($product->id) {
            $args = $this->hyper['input']->get('args', [], 'array');

            $defaultParts      = (array) $product->configuration->get('default');
            $defaultOptionList = (array) $product->configuration->get('option');
            $configPartOptions = (array) $product->configuration->get('part_options');

            if (count($args)) {
                foreach ($args as $data) {
                    $data      = new JSON($data);
                    $optionId  = $data->get('option-id');
                    $partId    = $data->get('part-id', 0, 'int');
                    $defPartId = $data->find('default.part', 0, 'int');
                    $defOptId  = $data->find('default.option', 0, 'int');

                    $defaultOptionId  = 0;
                    $partInDefOptList = array_key_exists($partId, $defaultOptionList);
                    if ($method === self::SAVE_CONF_METHOD_TOGGLE_OPTION) {
                        if ($partInDefOptList) {
                            $defaultOptionId = (int) $defaultOptionList[$partId];
                            $defaultOptionList[$partId] = $optionId;
                        }

                        if (array_key_exists($defaultOptionId, $configPartOptions)) {
                            $optionData = $configPartOptions[$defaultOptionId];
                            unset($configPartOptions[$defaultOptionId]);
                            $configPartOptions[(int) $optionId] = $optionData;
                        }
                    } elseif ($method === self::SAVE_CONF_METHOD_TOGGLE_PARTS) {
                        if ($optionId && isset($defaultOptionList[$defPartId]) && isset($configPartOptions[$defOptId])) {
                            unset($defaultOptionList[$defPartId]);
                            $defaultOptionList[$partId] = $optionId;

                            $configPartOptions[$defOptId]['is_default'] = false;

                            $configPartOptions[$optionId] = [
                                'is_default' => true,
                                'part_id'    => (string) $partId
                            ];
                        } elseif ($optionId && !isset($defaultOptionList[$defPartId]) && !isset($configPartOptions[$defOptId])) {
                            if ($partInDefOptList) {
                                $defaultOptionId = (int) $defaultOptionList[$partId];
                                $defaultOptionList[$partId] = $optionId;
                            }

                            if (array_key_exists($defaultOptionId, $configPartOptions)) {
                                $optionData = $configPartOptions[$defaultOptionId];
                                unset($configPartOptions[$defaultOptionId]);
                                $configPartOptions[(int) $optionId] = $optionData;
                            }
                        } elseif (!$optionId) {
                            unset(
                                $defaultOptionList[$defPartId],
                                $configPartOptions[$defOptId]
                            );

                            foreach ((array) $defaultParts as $i => $defaultPart) {
                                if ($defaultPart === (string) $defPartId) {
                                    unset($defaultParts[$i]);
                                }
                            }

                            if (!in_array((string) $partId, $defaultParts)) {
                                $defaultParts[] = (string) $partId;
                            }
                        }
                    }
                }

                $product->configuration
                    ->set('default', $defaultParts)
                    ->set('option', $defaultOptionList)
                    ->set('part_options', $configPartOptions);

                $configData = $product->toSaveConfiguration();

                $result = $this->hyper['helper']['configuration']->save(
                    $product,
                    $configData->get('parts'),
                    $configData->get('options'),
                    $configData->get('part_quantity')
                );

                if ($result !== false) {
                    /** @todo create object type for add cart item */
                    $itemData = [
                        'quantity'           => 1,
                        'savedConfiguration' => $result,
                        'id'                 => $product->id,
                        'type'               => CartHelper::TYPE_CONFIGURATION
                    ];

                    $this->hyper['helper']['cart']->addItem($itemData, CartHelper::ADD_ITEM_TYPE_SUM);

                    $output->set('result', true);
                    $output->set('savedConfiguration', $result);

                    /** @var FacebookHelper */
                    $facebookHelper = $this->hyper['helper']['facebook'];
                    try {
                        $facebookHelper->addToCartEvent($itemData);
                    } catch (\Throwable $th) {
                        $output->set('warning', $th->getMessage());
                    }
                }
            }
        }

        $items = $this->hyper['helper']['cart']->getItemsShortList();
        $output->set('items', $items);
        $output->set('count', count($items));

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Add item into the cart.
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function addToCart()
    {
        $data       = new Data($this->hyper['input']->get('args', [], 'array'));
        $itemId     = $data->get('id');
        $type       = $data->get('type', 'part');
        $addType    = $this->hyper['input']->get('addType', CartHelper::ADD_ITEM_TYPE_SUM);

        $result = new JSON([
            'result' => false,
            'msg'    => Text::_('COM_HYPERPC_CART_ADD_ITEM_ERROR')
        ]);

        /** @var CartHelper */
        $cartHelper = $this->hyper['helper']['cart'];

        if ($type === CartHelper::TYPE_CONFIGURATION) {
            $configId = $data->get('savedConfiguration', 0);
            $item = $this->hyper['helper']['configuration']->findById($configId);
        } else {
            $modelType = 'Position';

            /** @var HyperPcModelPosition */
            $model = ModelList::getInstance($modelType);
            $item = $model->getItem($itemId);
        }

        if (!empty($item->id)) {
            $cartHelper->addItem($data, $addType);
            $result
                ->set('result', true)
                ->set('msg', null);
        } else {
            $message = Text::_(sprintf('COM_HYPERPC_%s_NOT_EXIST', strtoupper($modelType)));
            $result->set('msg', $message);
        }

        $items = $cartHelper->getItemsShortList();

        /** @var PromocodeHelper */
        $promocodeHelper = $this->hyper['helper']['promocode'];

        if ($promoCode = $promocodeHelper->getSessionData()) {
            $result->set('promoType', $promoCode->get('type') === 2 ? $promoCode->get('type') : 0);
        }

        $result
            ->set('items', $items)
            ->set('count', count($items));

        if ($result->get('result')) {
            /** @var FacebookHelper */
            $facebookHelper = $this->hyper['helper']['facebook'];
            try {
                $facebookHelper->addToCartEvent((array) $data);
            } catch (\Throwable $th) {
                $result->set('warning', $th->getMessage());
            }
        }

        $this->hyper['cms']->close($result->write());
    }

    /**
     * Check promo code.
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function checkPromoCode()
    {
        $this->hyper['cms']->setHeader('Content-Type', 'application/json');

        $output = new JSON([
            'result'  => false,
            'rate'    => 0,
            'items'   => [],
            'message' => Text::_('COM_HYPERPC_ERROR_NOT_EXISTS_PROMO_CODE')
        ]);

        /** @var PromoCode $promoCode */
        $promoCode = $this->hyper['helper']['promocode']->getBy('code', $this->hyper['input']->getString('code'));
        if ($promoCode->limit > 0 && $promoCode->used >= $promoCode->limit) {
            $output->set('message', Text::_('COM_HYPERPC_ERROR_PROMO_CODE_HAS_BEEN_END'));
            $this->hyper['cms']->close($output->write());
        }

        if ($promoCode->publish_up || $promoCode->publish_down) {
            $date = new JoomlaDate();

            if ($date < $promoCode->publish_up) {
                $output->set('message', Text::_('COM_HYPERPC_ERROR_NOT_EXISTS_PROMO_CODE'));
                $this->hyper['cms']->close($output->write());
            }

            if ($date > $promoCode->publish_down) {
                $output->set('message', Text::_('COM_HYPERPC_ERROR_PROMO_CODE_DATE_END'));
                $this->hyper['cms']->close($output->write());
            }
        }

        if ($promoCode->id) {

            /** @var Data $promoSessionData */
            $promoSessionData = $this->hyper['helper']['promocode']->getSessionData();

            if (!empty($promoSessionData->get('code'))) {
                $output->set('message', Text::_('COM_HYPERPC_ERROR_PROMO_CODE_HAS_BEEN_ENTER'));
                $this->hyper['cms']->close($output->write());
            }

            $_items = [];
            $items  = $promoCode->getItems();

            $sessionData = [
                'parts'     => [],
                'products'  => [],
                'positions' => [],
                'code'      => $promoCode->code,
                'rate'      => ($promoCode->type == PromoCode::TYPE_GIFT) ? 100 : $promoCode->rate
            ];

            if (count($items)) {
                /** @var Entity $item */
                foreach ($items as $item) {
                    if ($item instanceof Position) {
                        $_items[] = $cartData = [
                            'id'        => $item->id,
                            'type'      => CartHelper::TYPE_POSITION
                        ];

                        $sessionData['positions'][$item->id] = $item->id;

                        if ($item instanceof PartMarker && $promoCode->type === PromoCode::TYPE_GIFT) {
                            $cartData['quantity'] = 1;

                            $option = $item->getDefaultOption();
                            if ($option->id) {
                                $cartData['option'] = $option->id;
                            }

                            $this->hyper['helper']['cart']->addItem($cartData);
                        }
                    }
                }
            }

            $this->hyper['helper']['promocode']->setSessionData($sessionData);

            $output
                ->set('result', true)
                ->set('items', $_items)
                ->set('type', $promoCode->type)
                ->set('message', $promoCode->description)
                ->set('rate', $promoCode->rate);
        }

        $this->hyper['cms']->close($output->write());
    }

    /**
     * Clear cart.
     */
    public function clearAll()
    {
        $this->hyper['helper']['cart']->clearSession();
        $this->hyper['helper']['promocode']->clearSessionData();

        $this->hyper['cms']->close('{"result": true}');
    }

    /**
     * Get array of itemkeys of items in cart
     */
    public function getCartItemsKeys()
    {
        $items = $this->hyper['helper']['cart']->getSessionItems();
        $this->hyper['cms']->close(json_encode(array_keys($items)));
    }

    /**
     * Get order pickup dates
     */
    public function getDates()
    {
        $result = new JSON($this->hyper['helper']['cart']->getOrderPickingDates());
        $this->hyper['cms']->close($result->write());
    }

    /**
     * Get the number of items in cart.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function getItemsCount()
    {
        $items  = $this->hyper['helper']['cart']->getSessionItems();
        $result = new JSON(['count' => count($items)]);
        $this->hyper['cms']->close($result->write());
    }

    /**
     * Hook on initialize controller.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        $this
            ->registerTask('addToCart', 'addToCart')
            ->registerTask('reloadModule', 'reloadModule')
            ->registerTask('removeItem', 'removeItem')
            ->registerTask('check-promo-code', 'checkPromoCode')
            ->registerTask('reset-promo-code', 'resetPromoCode')
            ->registerTask('add-product-and-create-config', 'addProductAndCreateConfig')
            ->registerTask('get-items-count', 'getItemsCount')
            ->registerTask('get-dates', 'getDates')
            ->registerTask('initiate-checkout', 'initiateCheckout')
            ->registerTask('get-cart-items-keys', 'getCartItemsKeys')
            ->registerTask('clear-all', 'clearAll');
    }

    /**
     * Initiate checkout event.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initiateCheckout()
    {
        $result = new JSON();

        /** @var FacebookHelper */
        $facebookHelper = $this->hyper['helper']['facebook'];
        try {
            $facebookHelper->initiateCheckoutEvent();
        } catch (\Throwable $th) {
            $result->set('warning', $th->getMessage());
        }

        $this->hyper['cms']->close($result->write());
    }

    /**
     * Reload cart module.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function reloadModule()
    {
        $moduleId = $this->hyper['input']->get('moduleId');
        $this->hyper['cms']->close($this->hyper['helper']['module']->renderById($moduleId));
    }

    /**
     * Remove item from cart.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function removeItem()
    {
        $data    = new Registry($this->hyper['input']->get('args', [], 'array'));
        $itemKey = $data->get('itemKey');

        $result = new Registry([
            'result' => $this->hyper['helper']['cart']->removeItem($itemKey)
        ]);

        /*
        $relatedParts = $data->get('relatedParts', []);
        foreach ($relatedParts as $partItemKey) {
            if ($this->hyper['helper']['cart']->removeItem($partItemKey) {
                $result['relatedRemoved'][$partItemKey] = $partItemKey;
            }
        }
        */

        $items = $this->hyper['helper']['cart']->getItemsShortList();

        $result->set('items', $items);
        $result->set('count', count($items));

        $this->hyper['cms']->close(json_encode($result));
    }

    /**
     * Reset promo code.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function resetPromoCode()
    {
        /** @var PromoCode $promoCode */
        $promoCode = $this->hyper['helper']['promocode']->getBy('code', $this->hyper['input']->get('code'));

        $output = new JSON([
            'result'  => false,
            'message' => null
        ]);

        if ($promoCode->id) {
            if ($promoCode->type === PromoCode::TYPE_GIFT) {
                $items = $promoCode->getItems();
                if (count($items)) {
                    foreach ($items as $item) {
                        if ($item instanceof PartMarker) {
                            $option = $item->getDefaultOption();
                            $itemId = $item->id;
                            if ($option->id) {
                                $itemId .= '-' . $option->id;
                            }

                            $this->hyper['helper']['cart']->removeItem($itemId, CartHelper::TYPE_POSITION);
                        }

                        if ($item instanceof ProductMarker) {
                            $this->hyper['helper']['cart']->removeItem(
                                $item->id, CartHelper::TYPE_POSITION, (int) $item->saved_configuration
                            );
                        }
                    }
                }
            }

            $this->hyper['helper']['promocode']->setSessionData([]);
            $output->set('result', true);
        }

        $this->hyper['cms']->close($output->write());
    }
}
