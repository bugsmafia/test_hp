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

use JBZoo\Utils\Str;
use Cake\Utility\Hash;
use HYPERPC\Data\JSON;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Joomla\Factory;
use HYPERPC\ORM\Entity\User;
use HYPERPC\Helper\CrmHelper;
use HYPERPC\Helper\MoneyHelper;
use Joomla\CMS\Language\Language;
use HYPERPC\Elements\ElementOrderHook;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\ORM\Entity\ProductInStock;
use HYPERPC\Object\Order\PositionData;
use HYPERPC\Joomla\Model\Entity\Worker;
use HYPERPC\Object\Order\PositionDataCollection;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ElementOrderHookAmoCrm
 *
 * @since   2.0
 */
class ElementOrderHookAmoCrm extends ElementOrderHook
{

    /**
     * Hold CRM helper
     *
     * @var     CrmHelper
     *
     * @since   2.0
     */
    protected $_helper;

    /**
     * Hold language
     *
     * @var     Language
     *
     * @since   2.0
     */
    protected static $_language;

    /**
     * Hold lead data.
     *
     * @var     array
     *
     * @since   2.0
     */
    protected $_leadData = [];

    /**
     * Hold saved lead id.
     *
     * @var     null|int
     *
     * @since   2.0
     */
    protected $_leadId;

    /**
     * Hold order entity.
     *
     * @var     Order
     *
     * @since   2.0
     */
    protected $_order;

    /**
     * Hold worker entity.
     *
     * @var     Worker
     *
     * @since   2.0
     */
    protected $_worker;

    /**
     * Get lead id.
     *
     * @return  int|null
     *
     * @since   2.0
     */
    public function getLeadId()
    {
        return $this->_leadId;
    }

    /**
     * Hook action.
     *
     * @return  void
     *
     * @throws  Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function hook()
    {
        if (!$this->_isEnabled()) {
            return;
        }

        /** @var User */
        $user = $this->hyper['user'];

        if ($user->id) {
            if ($user->isManager() && !$this->getConfig('send_only_manager', 0, 'bool')) {
                return;
            }
        }

        $this->_order = $this->_getOrder();

        $products     = $this->_order->getProducts();
        $noteText     = [sprintf(self::$_language->_('COM_HYPERPC_ORDER_NUMBER'), $this->_order->getName())];
        $productData  = $this->_processProducts($products, $noteText);

        $contactId = $this->_findContact();

        if ($contactId === null) {
            $contactId = $this->_addContact();
        } else {
            if ($this->_order->getBuyerOrderMethod()) {
                $this->_addCompany($contactId);
            }

            $this->_updateContact($contactId);
        }

        $this->_addLead($productData, $contactId, $noteText);
    }

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();
        $this->_helper = $this->hyper['helper']['crm'];

        if (!self::$_language) {
            self::$_language = $this->_helper->getCrmLanguage();
            self::$_language->load('el_' . $this->getGroup() . '_' .  $this->getType(), $this->getPath());
        }
    }

    /**
     * Update lead by order data
     *
     * @param   Order $order
     *
     * @return  void
     *
     * @since   2.0
     */
    public function updateLeadByOrderData(Order $order)
    {
        if (!$this->_isEnabled()) {
            return;
        }

        $leadId = $order->getAmoLeadId();
        if (empty($leadId)) {
            return;
        }

        $this->_order = $order;

        $this->_leadData = [
            'id'            => $leadId,
            'updated_at'    => time(),
            'sale'          => $this->_order->getTotal()->val(),
            'tags'          => false,
            'custom_fields' => [
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PROMOCODE_KEY),
                    'values' => [
                        [
                            'value' => $this->_order->promo_code
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_DELIVERY_ADDRESS_KEY),
                    'values' => [
                        [
                            'value' => $this->_getOrderDeliveryAddress()
                        ]
                    ]
                ]
            ]
        ];

        $this
            ->_setLeadCustomProductTypeField()
            ->_setPaymentType()
            ->_setDeliveryCustomFields();

        $orderCreatedTime = clone $this->_order->created_time;
        $orderCreatedTime->setTimezone($this->hyper['helper']['date']->getServerTimeZone());

        $noteText = [sprintf(
            self::$_language->_('COM_HYPERPC_ORDER_NUMBER_FROM'),
            $this->_order->id,
            $orderCreatedTime->format(self::$_language->_('DATE_FORMAT_LC5'), true)
        ) . ':'];

        $moneyHelper = $this->hyper['helper']['money'];
        $positionsData = PositionDataCollection::create($this->_order->positions->getArrayCopy());

        $i = 0;
        foreach ($positionsData as $positionData) {
            $i++;

            $quantity = sprintf(self::$_language->_('COM_HYPERPC_ORDER_ITEM_QUANTITY'), $positionData->quantity);
            $positionPrice = $moneyHelper->get($positionData->price);
            if ($positionData->discount > 0) {
                $positionPrice->multiply((100 - $positionData->discount) / 100);
            }

            $noteText[] = "{$i}. {$positionData->name}, {$quantity}, {$positionPrice->text()}";
        }

        $vat = $moneyHelper->getVat($this->_order->getTotal());

        $noteText[] = self::$_language->_('COM_HYPERPC_INCLUDES_VAT') . ' ' . $vat->text();
        $noteText[] = sprintf(self::$_language->_('COM_HYPERPC_BASKET_TOTAL_PRICE'), $this->_order->getTotal()->text());

        $this->_helper->addNote($leadId, implode("\n", $noteText));

        $this->_helper->updateLead([$this->_leadData]);
    }

    /**
     * Add company.
     *
     * @param   string  $contactId
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _addCompany($contactId)
    {
        $companyName = $this->_order->elements->find('company.value');
        $companyList = (array) $this->_helper->getCompanies(['query' => $companyName])->find('_embedded.items');

        $companyId = 0;
        if (count($companyList)) {
            foreach ($companyList as $company) {
                $company = new JSON($company);
                if (Str::low($company->get('name')) === Str::low($companyName)) {
                    $companyId = $company->get('id', 0, 'int');
                }
            }
        }

        if ($companyId === 0) {
            $companyResult = $this->_helper->addCompany([
                [
                    'name' => $companyName,
                    'custom_fields' => [
                        [
                            'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_EMAIL_KEY),
                            'values' => [
                                [
                                    'enum'  => 'WORK',
                                    'value' => $this->_order->getBuyerEmail()
                                ]
                            ]
                        ],
                        [
                            'id'     => $this->_helper->getCustomFieldId(CrmHelper::COMPANY_FIELD_INN_KEY),
                            'values' => [
                                [
                                    'value' => $this->_order->elements->find('company.value')
                                ]
                            ]
                        ],
                        [
                            'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_PHONE_KEY),
                            'values' => [
                                [
                                    'enum'  => 'MOB',
                                    'value' => $this->_order->getBuyerPhone()
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            $companyId = $companyResult->find('_embedded.items.0.id');
        }

        $this->_helper->updateContact([
            [
                'updated_at' => time(),
                'id'         => $contactId,
                'company_id' => $companyId
            ]
        ]);
    }

    /**
     * Add contact.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _addContact()
    {
        $contactData = [
            'created_at'    => time(),
            'name'          => $this->_order->getBuyer(),
            'custom_fields' => [
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_EMAIL_KEY),
                    'values' => [
                        [
                            'enum'  => 'WORK',
                            'value' => $this->_order->getBuyerEmail()
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_PHONE_KEY),
                    'values' => [
                        [
                            'enum'  => 'MOB',
                            'value' => $this->_order->getBuyerPhone()
                        ]
                    ]
                ]
            ]
        ];

        $companyName = $this->_order->elements->find('company.value');
        if ($companyName) {
            $contactData['company_name'] = $companyName;
        }

        $newContactBody = $this->_helper->addContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }

    /**
     * Add lead action.
     *
     * @param   JSON    $productData
     * @param   string  $contactId
     * @param   array   $noteText
     *
     * @return  void
     *
     * @throws  Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _addLead(JSON $productData, $contactId, &$noteText)
    {
        $this
            ->_setDefaultLeadData($contactId, $productData->get('configuration_id'))
            ->_setLeadCustomProductTypeField()
            ->_setCustomTags()
            ->_setWorker()
            ->_setPaymentType()
            ->_setDeliveryCustomFields();

        if ($productData->get('has_in_stock') === true) {
            $this->_leadData['tags'][] = self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_IN_STOCK_TAG');
        }

        $this->_sendLeadToAmoCRM();

        if ($this->_leadId) {
            /** Start update order record (Write amo crn lead id) **/
            $this->_order->params->set('amo_lead_id', $this->_leadId);
            $this->_order->helper->getTable()->save($this->_order->getArray());

            $this->hyper['helper']['dealMap']->bindCrmLeadToSiteOrder($this->_leadId, $this->_order->id);
            /** End update order record **/

            /** Start update worker record **/
            if ($this->_worker->id) {
                /** @var HyperPcTableWorkers $table */
                $table = $this->hyper['helper']['worker']->getTable();
                $this->_worker->set('last_order_turn', Factory::getDate());
                $table->save($this->_worker);
            }
            /** End update worker record **/

            $this->_processParts($noteText);
            $this->_processPositions($noteText);

            $this->_helper
                ->addLeadNoteTextByOrderElements($this->_order, $noteText)
                ->addNote($this->_leadId, implode("\n", $noteText));
        }
    }

    /**
     * Find actual contact by order data.
     *
     * @return  mixed|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _findContact()
    {
        $phoneNumber  = $this->_helper->clearMobilePhone($this->_order->getBuyerPhone());
        $contactsBody = $this->_helper->getContacts(['query' => $phoneNumber]);
        $contacts     = (array) $contactsBody->find('_embedded.items');
        $contactId    = $this->_helper->findContactByMobilePhone($phoneNumber, $contacts);

        if ($contactId) {
            return $contactId;
        }

        $mailContacts = $this->_helper->getContacts(['query' => $this->_order->getBuyerEmail()]);

        return $this->_helper->findContactByEmail(
            $this->_order->getBuyerEmail(),
            (array) $mailContacts->find('_embedded.items')
        );
    }

    /**
     * Get lead name.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getLeadName()
    {
        $order = clone $this->_order;

        $subject = $order->isCredit() ? $this->_config->get('name_credit') : $this->_config->get('name_order');

        return $this->hyper['helper']['macros']
            ->setData($order)
            ->text($subject);
    }

    /**
     * Get current order delivery method.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _getOrderDeliveryAddress()
    {
        $deliveryAddress = $this->_order->elements->find('yandex_delivery.original_address');
        if (empty($deliveryAddress)) {
            $deliveryAddress = $this->_order->elements->find('yandex_delivery.pickup_point_address');
        }

        return $deliveryAddress;
    }

    /**
     * Get current pipeline and status id.
     *
     * @return  array
     *
     * @since   2.0
     *
     * @todo    get from initial order status
     */
    protected function _getPipelineStatus()
    {
        $order = $this->_getOrder();

        $cPipeLineDef           = $this->_config->get('pipeline_default', ':');
        $cPipeLineCred          = $this->_config->get('pipeline_credit', ':');
        $cPipeLineAccess        = $this->_config->get('pipeline_accessories', ':');
        $cPipeLineUpgrade       = $this->_config->get('pipeline_accessories_upgrade', ':');
        $cPipeLineCredUpgrade   = $this->_config->get('pipeline_credit_upgrade', ':');

        $pipelineDefault        = ($cPipeLineDef)         ? $cPipeLineDef : ':';
        $pipelineCredit         = ($cPipeLineCred)        ? $cPipeLineCred : ':';
        $pipelineAccessories    = ($cPipeLineAccess)      ? $cPipeLineAccess : ':';
        $pipelineUpgrade        = ($cPipeLineUpgrade)     ? $cPipeLineUpgrade : ':';
        $pipeLineCredUpgrade    = ($cPipeLineCredUpgrade) ? $cPipeLineCredUpgrade : ':';

        if ($order->hasUpgradeAccessories()) {
            if ($order->isCredit()) {
                return explode(':', $pipeLineCredUpgrade);
            }
            return explode(':', $pipelineUpgrade);
        }

        if ($order->isCredit()) {
            return explode(':', $pipelineCredit);
        }

        if ($order->hasOnlyAccessories()) {
            return explode(':', $pipelineAccessories);
        }

        return explode(':', $pipelineDefault);
    }

    /**
     * Check debug mode.
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function _isEnabled()
    {
        /** @todo change param name */
        return $this->_config->get('debug', true, 'bool');
    }

    /**
     * Process order parts.
     *
     * @param   array  $noteText
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _processParts(array &$noteText)
    {
        $parts = $this->_order->getParts();
        if (count($parts)) {
            $noteText[] = self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PART_LABEL');
            /** @var PartMarker $part */
            $i = 0;
            foreach ($parts as $part) {
                $i++;

                $partUrl = $part->getViewUrl();
                if ($part->option instanceof OptionMarker && $part->option->id) {
                    $part->set('name', $part->name . ' (' . $part->option->name . ')');
                    $partUrl = $part->getViewUrl(['opt' => true]);
                }

                $partName = $part->name;
                $group    = $part->getGroup();

                $notebookGroups = (array) $this->hyper['params']->get('notebook_groups', []);
                if ($group->id) {
                    $partName = $group->title . ': ' . $partName;

                    if (in_array((string) $group->id, $notebookGroups)) {
                        $partName = 'Ноутбук (' . $group->title . '): ' . $part->name;
                    }
                }

                if ($part->quantity > 1) {
                    $partName = $part->quantity . ' x ' . $partName;
                }

                $partString = $i . '. ' . $partName . '';

                $promoPrice = $part->getPrice();
                $rateValue  = $part->getRate();

                if ($rateValue) {
                    if ($part->quantity > 1) {
                        $quantityPrice = clone $promoPrice;
                        $quantityPrice->multiply($part->quantity);
                        $partString .= ' - ' . $part->quantity . ' x ' . $promoPrice->text() . ' = ' . $quantityPrice->text() . ' с учетом скидки -' . $part->get('rate') . '%';
                    } else {
                        $partString .= ' - ' . $promoPrice->text() . ' с учетом скидки -' . $part->get('rate') . '%';
                    }
                } else {
                    if ($part->quantity > 1) {
                        $partString .= ' - ' . $part->quantity . ' x ' . $part->price->text() . ' = ' . $part->getQuantityPrice()->text();
                    } else {
                        $partString .= ' - ' . $part->price->text();
                    }
                }

                $noteText[] = $partString;
                $noteText[] = '---' . Uri::base() . ltrim($partUrl, '/');
            }
            $noteText[] = '-----------------------------------';
            $noteText[] = "\n";
        }
    }

    /**
     * Process order positions.
     *
     * @param   array  $noteText
     *
     * @return  void
     *
     * @throws  \Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _processPositions(array &$noteText)
    {
        $positions = $this->_order->getPositions();
        if (count($positions)) {
            $positionsData = PositionDataCollection::create((array) $this->_order->positions);
            $noteText[] = self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_POSITION_LABEL');
            $i = 0;
            foreach ($positions as $itemKey => $position) {
                $i++;

                /** @var PositionData */
                $positionData = $positionsData->offsetGet($itemKey);

                $url = $position->getViewUrl(['opt' => true]);
                if ($position instanceof ProductMarker) {
                    if ($positionData->option_id && !$position->isFromStock()) {
                        $configuration = $this->hyper['helper']['configuration']->findById($positionData->option_id);
                        $url = $configuration->getViewUrl();
                    } else {
                        $url = $position->getViewUrl();
                    }
                }

                $name = $positionData->name;
                $folder = $position->getFolder();
                if ($folder->id) {
                    $name = "{$folder->title}: {$name}";
                }

                $quantity = $positionData->quantity;
                if ($quantity > 1) {
                    $name = "{$quantity} x {$name}";
                }

                $positionString = "{$i}. {$name} - ";

                /** @var MoneyHelper */
                $moneyHelper = $this->hyper['helper']['money'];

                $discount = $positionData->discount;

                $salePrice = $moneyHelper->get($positionData->price * ((100 - $discount) / 100));
                $linePrice = $salePrice->multiply($quantity, true);

                if ($quantity > 1) {
                    $positionString .= "{$quantity} x {$salePrice->text()} = {$linePrice->text()}";
                } else {
                    $positionString .= $salePrice->text();
                }

                if ($discount > 0) {
                    $positionString .= ' ' . sprintf(self::$_language->_('COM_HYPERPC_AMO_CRM_INCLUDING_DISCOUNT'), $discount . '%');
                }

                $noteText[] = $positionString;
                $noteText[] = '---' . Uri::base() . ltrim($url, '/');
            }
            $noteText[] = '-----------------------------------';
            $noteText[] = "\n";
        }
    }

    /**
     * Process order products for crm lead.
     *
     * @param   array $products
     * @param   array $noteText
     *
     * @return  JSON
     *
     * @throws  Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    protected function _processProducts($products, array &$noteText)
    {
        $return = new JSON([
            'config_url'       => null,
            'configuration_id' => null,
            'has_in_stock'     => false
        ]);

        if (count($products)) {
            $noteText[] = self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_LABEL');

            $order = $this->_getOrder();

            $i = 0;
            /** @var ProductMarker $product */
            foreach ($products as $product) {
                $product->set('order_id', $order->id);

                $fullConfigUrl = $this->hyper['helper']['route']->getSiteSefUrl(
                    $product->getConfigUrl($product->saved_configuration)
                );

                $totalPrice = clone $product->getConfigPrice();
                $hasPromo   = $product->get('rate');

                if ($hasPromo) {
                    $totalPrice->add('-' . $product->get('rate') . '%');
                }

                $productName = $product->getName();
                if ($product->quantity > 1) {
                    $productName = $product->quantity . ' x ' . $productName;
                }

                if ($product->params->get('stock') instanceof ProductInStock) {
                    $return->set('has_in_stock', true);
                    $productName .= ' ' . sprintf(
                        self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_IN_STOCK'),
                        $product->params->get('stock')->get('configuration_id')
                    );
                }
                $noteText[] = sprintf(
                    self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_LINE'),
                    $i,
                    $productName,
                    $totalPrice->text()
                );

                if ($product->quantity > 1) {
                    $noteText[] = sprintf(
                        self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_LINE_TOTAL'),
                        $product->quantity,
                        $totalPrice->text(),
                        $product->getQuantityPrice()->text()
                    );
                }

                $noteText[] = sprintf(
                    self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_LINE_CONFIG_LINK'),
                    $fullConfigUrl
                );

                if (!$product->params->get('stock') instanceof ProductInStock) {
                    $noteText[] = $product->isDefaultConfiguration() ?
                        self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_LINE_DEFAULT') :
                        self::$_language->_('HYPER_ELEMENT_ORDER_HOOK_AMO_CRM_NOTE_PRODUCT_LINE_CONFIGURED');
                }

                $return
                    ->set('config_url', $fullConfigUrl)
                    ->set('configuration_id', $product->saved_configuration);

                $this->_helper->addLeadNoteTextByProductConfiguration($product, $noteText);
            }
        }

        return $return;
    }

    /**
     * Add lead to amo crm.
     *
     * @return  $this
     *
     * TODO write order log for send amo crm
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _sendLeadToAmoCRM()
    {
        $userLeads = $this->_helper->findAllUserOpenLeads(
            $this->_order->getBuyerPhone(),
            $this->_order->getBuyerEmail()
        );

        $userFirstOpenLead = current($userLeads);

        if ($userFirstOpenLead instanceof JSON) {
            //  Remove new roistat value.
            foreach ($this->_leadData['custom_fields'] as $cId => $customField) {
                $customField = new JSON($customField);
                if ($customField->get('id') === $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ROISTAT_KEY)) {
                    unset($this->_leadData['custom_fields'][$cId]);
                }
            }

            $newLeadBody   = new JSON((array) Hash::merge($userFirstOpenLead->getArrayCopy(), $this->_leadData));
            $this->_leadId = $newLeadBody->get('id');

            $this->_helper->addNote(
                $this->_leadId,
                sprintf(
                    self::$_language->_('COM_HYPERPC_AMO_CRM_SYSTEM_NOTE_SITE_LEAD_DOUBLE'),
                    $userFirstOpenLead->get('name')
                ),
                CrmHelper::NOTE_EVENT_DEAL_SYSTEM
            );

            $this->_helper->updateLead([$newLeadBody->getArrayCopy()]);
        } else {
            $newLeadBody = $this->_helper->addLead([$this->_leadData]);
            $this->_leadId = $newLeadBody->find('_embedded.items.0.id');
        }

        return $this;
    }

    /**
     * Setup custom tags.
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setCustomTags()
    {
        if ($this->_order->hasOnlyAccessories()) {
            $this->_leadData['tags'][] = self::$_language->_('COM_HYPERPC_CRM_TAG_ACCESSORIES');
        } else {
            $this->_leadData['tags'][] = self::$_language->_('COM_HYPERPC_CRM_TAG_PC_SALE');
        }

        if ($this->_order->isCredit()) {
            $this->_leadData['tags'][] = self::$_language->_('COM_HYPERPC_CRM_TAG_CREDIT');
        }

        if ($this->_order->hasGame()) {
            $this->_leadData['tags'][] = 'GamePass';
        }

        return $this;
    }

    /**
     * Setup default lead data.
     *
     * @param   int $contactId
     * @param   int $configId
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setDefaultLeadData($contactId, $configId)
    {
        list ($pipeline, $statusId) = $this->_getPipelineStatus();

        $roiStatId    = $this->hyper['input']->cookie->get('roistat_visit', '');
        $newRoiStatID = $this->_order->elements->find('roistat_id.value');
        $cid          = $this->_order->cid;

        /** @var User */
        $user = $this->_order->getCreatedUser();
        $uid = $user->getUid();

        if ($newRoiStatID) {
            $roiStatId = $newRoiStatID;
        }

        $saleValue     = clone $this->_order->total;
        $deliveryPrice = $this->_order->getCustomDeliveryPrice();

        //  Add delivery price to total.
        if ($deliveryPrice->compare(0, '>')) {
            $saleValue->add($deliveryPrice);
        }

        $this->_leadData = [
            'created_at'    => time(),
            'pipeline_id'   => $pipeline,
            'status_id'     => $statusId,
            'contacts_id'   => $contactId,
            'name'          => $this->_getLeadName(),
            'sale'          => $saleValue->val(),
            'tags'          => [],
            'custom_fields' => [
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_CONFIGURATION_ID_KEY),
                    'values' => [
                        [
                            'value' => $configId
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ORDER_ID_KEY),
                    'values' => [
                        [
                            'value' => $this->_order->id
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_DELIVERY_ADDRESS_KEY),
                    'values' => [
                        [
                            'value' => $this->_getOrderDeliveryAddress()
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_COMMENT_KEY),
                    'values' => [
                        [
                            'value' => $this->_order->elements->find('comment.value')
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PROMOCODE_KEY),
                    'values' => [
                        [
                            'value' => $this->_order->promo_code
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_ROISTAT_KEY),
                    'values' => [
                        [
                            'value' => $roiStatId
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_GOOGLE_CLIENT_ID_KEY),
                    'values' => [
                        [
                            'value' => $cid
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_USER_ID_KEY),
                    'values' => [
                        [
                            'value' => $uid
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_COUNTER_KEY),
                    'values' => [
                        [
                            'value' => $this->_order->getYmCounter()
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_YM_UID_KEY),
                    'values' => [
                        [
                            'value' => $this->_order->getYmUid()
                        ]
                    ]
                ],
                [
                    'id' => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_BUYER_TYPE_KEY),
                    'values' => [
                        [
                            'value' => ($this->_order->getBuyerOrderMethod() === Order::BUYER_TYPE_INDIVIDUAL) ?
                                $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_BUYER_TYPE_INDIVIDUAL_KEY) :
                                $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_BUYER_TYPE_LEGAL_KEY)
                        ]
                    ]
                ]
            ]
        ];

        return $this;
    }

    /**
     * Setup lead custom field product type.
     *
     * @return  $this
     *
     * @since   2.0
     */
    protected function _setLeadCustomProductTypeField()
    {
        $productFieldId = $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PRODUCT_KEY);

        if ($this->_order->hasProducts()) {
            $this->_leadData['custom_fields'][] = [
                'id'     => $productFieldId,
                'values' => [
                    [
                        'value' => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_PC_KEY)
                    ]
                ]
            ];

            return $this;
        }

        if ($this->_order->hasNotebooks()) {
            $this->_leadData['custom_fields'][] = [
                'id'     => $productFieldId,
                'values' => [
                    [
                        'value' => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_NOTEBOOK_KEY)
                    ]
                ]
            ];

            return $this;
        }

        $this->_leadData['custom_fields'][] = [
            'id'     => $productFieldId,
            'values' => [
                [
                    'value' => $this->_helper->getEnumId(CrmHelper::LEAD_FIELD_PRODUCT_ACCESSORY_KEY)
                ]
            ]
        ];

        return $this;
    }

    /**
     * Setup delivery custom fields.
     *
     * @return  $this
     *
     * @throws  \JBZoo\SimpleTypes\Exception
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setDeliveryCustomFields()
    {
        /** @var ElementOrderYandexDelivery $deliveryEl */
        $deliveryEl = $this->getManager()->create('yandex_delivery', 'order', [
            'data' => $this->_order->elements->find('yandex_delivery')
        ]);

        $deliveryEl->setAmoCustomFields($this->_leadData);

        return $this;
    }

    /**
     * Setup payment type.
     *
     * @return  $this
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _setPaymentType()
    {
        $paymentTypeId = $this->_helper->getCrmPaymentTypeId($this->_order);
        if ($paymentTypeId !== null) {
            $this->_leadData['custom_fields'][] = [
                'id'     => $this->_helper->getCustomFieldId(CrmHelper::LEAD_FIELD_PAYMENT_KEY),
                'values' => [
                    [
                        'value' => $paymentTypeId
                    ]
                ]
            ];
        }

        return $this;
    }

    /**
     * Setup worker.
     *
     * @return  $this
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function _setWorker()
    {
        $worker    = new Worker();
        $managerId = $this->_helper->getManagerCrmIdByName($this->_order);

        if ($managerId === null) {
            /** @var Worker $worker */
            $worker    = $this->hyper['helper']['worker']->getDefaultWorker();
            $managerId = $worker->id;
        }

        if ($managerId !== null) {
            $this->_leadData['responsible_user_id'] = $managerId;
        }

        $this->_worker = $worker;

        return $this;
    }

    /**
     * Update contact.
     *
     * @param   int $contactId  AmoCRM contact id.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _updateContact($contactId)
    {
        $contactData = [
            'id'            => $contactId,
            'updated_at'    => time(),
            'name'          => $this->_order->getBuyer(),
            'custom_fields' => [
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_EMAIL_KEY),
                    'values' => [
                        [
                            'enum'  => 'WORK',
                            'value' => $this->_order->getBuyerEmail()
                        ]
                    ]
                ],
                [
                    'id'     => $this->_helper->getCustomFieldId(CrmHelper::CONTACT_FIELD_PHONE_KEY),
                    'values' => [
                        [
                            'enum'  => 'MOB',
                            'value' => $this->_order->getBuyerPhone()
                        ]
                    ]
                ]
            ]
        ];

        $companyName = $this->_order->elements->find('company.value');
        if ($companyName) {
            $contactData['company_name'] = $companyName;
        }

        $newContactBody = $this->_helper->updateContact([$contactData]);
        return $newContactBody->find('_embedded.items.0.id');
    }
}
