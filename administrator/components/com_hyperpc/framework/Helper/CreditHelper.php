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

namespace HYPERPC\Helper;

use JBZoo\Utils\FS;
use JBZoo\Utils\Arr;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use HYPERPC\Data\ShortCode;
use Cake\Utility\Inflector;
use HYPERPC\Joomla\Factory;
use HYPERPC\Elements\Manager;
use HYPERPC\Elements\Element;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\Traits\LoadAssets;
use HYPERPC\Joomla\Model\Entity\Order;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class CreditHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class CreditHelper extends AppHelper
{

    use LoadAssets;

    const DEFAULT_RATE                      = 20.0;
    const DEFAULT_LOAN_TERM                 = 36;
    const DEFAULT_CALCULATE_LAYOUT          = 'calculate_default';
    const DEFAULT_CALCULATE_DEFAULT_ENTITY  = 'product';
    const REGEX_CALCULATE                   = '/{credit_calculate\s(.*?)}/i';

    /**
     * Hold 7Cloud status map.
     *
     * @var     JSON
     *
     * @since   2.0
     */
    protected $_statusMap;

    /**
     * Hold credit info popup rendered state
     *
     * @var boolean
     *
     * @since   2.0
     */
    private static $_infoModalRendered = false;

    /**
     * Render options by tag snippet.
     *
     * @param   object $article
     *
     * @return  void
     *
     * @throws \JBZoo\Utils\Exception
     * @throws \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function renderBySnippet(&$article)
    {
        preg_match_all(self::REGEX_CALCULATE, $article->text, $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $match) {
                $config = new ShortCode([
                    'price'  => 0,
                    'id'     => null,
                    'attrs'  => $match[1],
                    'layout' => self::DEFAULT_CALCULATE_LAYOUT,
                    'type'   => self::DEFAULT_CALCULATE_DEFAULT_ENTITY
                ]);

                $output     = [];
                $layoutPath = FS::clean('credit/' . $config->get('layout'));
                $price      = is_array($config->get('price')) ? Arr::implode(',', $config->get('price')) : $config->get('price');

                if ($config->get('id') && $this->hyper['helper']->loaded($config->get('type'))) {
                    $item  = $this->hyper['helper'][$config->get('type')]->findById($config->get('id'));
                    /** @var Money $price */
                    $price = $this->hyper['helper']['money']->get(0);
                    if ($item instanceof ProductMarker) {
                        $price = $item->getConfigPrice();
                    } elseif ($item instanceof PartMarker) {
                        $price = $item->getPrice();
                    }

                    if ($price->compare(0, '>')) {
                        $output[] = $this->hyper['helper']['render']->render($layoutPath, [
                            'item'  => $item,
                            'price' => $price
                        ]);
                    }
                } else {
                    $price = $this->hyper['helper']['money']->get($price);
                    if ($price->compare(0, '>')) {
                        $output[] = $this->hyper['helper']['render']->render($layoutPath, [
                            'item' => null,
                            'price'=> $price->getClone()
                        ]);
                    }
                }

                $config = Factory::getConfig();
                if (count($output) && (int) $config->get('caching') > 0) {
                    $article->text .= $this->loadScript(
                        $this->hyper['path']->url('js:widget/site/credit-calculate.js', false),
                        "$('.jsCreditCalculatorWrapper').HyperPCCreditCalculateRate({});"
                    );
                }

                $article->text = preg_replace(
                    "|$match[0]|",
                    addcslashes(implode(PHP_EOL, $output), '\\$'),
                    $article->text,
                    1
                );
            }
        }
    }

    /**
     * Get clearance day limit value.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function getClearanceDayLimit()
    {
        return Filter::int($this->hyper['params']->get('credit_clearance_day_limit', 14));
    }

    /**
     * Check order clearance day limit.
     *
     * @param   Order  $order
     *
     * @return  bool false if expired
     *
     * @since   2.0
     */
    public function checkClearanceDayLimit(Order $order)
    {
        $nowTime = new Date();
        $creditDeadline = $this->hyper['helper']['date']->addDays($order->created_time, $this->getClearanceDayLimit());

        return $creditDeadline > $nowTime;
    }

    /**
     * Get test callback server data - json string.
     *
     * @param   int     $orderId
     * @param   int     $totalAmount
     * @param   string  $statusId
     *
     * @return  string
     *
     * @since   2.0
     */
    public function get7SecondCallbackData($orderId = 5026, $totalAmount = 15640000, $statusId = 'CredAppr')
    {
        return (new JSON([
            'ApplicationID'      => 'aedca934-746f-4154-9907-b43c5c439034',
            'Status'             => 'test',
            'StatusID'           => $statusId,
            'OrderID'            => (string) $orderId,
            'Amount'             => Filter::int($totalAmount),
            'AmountWithDiscount' => Filter::int($totalAmount),
            'CreditSumm'         => Filter::int($totalAmount)
        ]))->write();
    }

    /**
     * Get 7Second status by id.
     *
     * @param   null|string $statusId
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function get7SecondStatus($statusId = null)
    {
        return new JSON((array) $this->_statusMap->find($statusId));
    }

    /**
     * Get default credit rate for calculating monthly payment
     *
     * @return string
     *
     * @since   2.0
     */
    public function getDefaultCreditRate()
    {
        return self::DEFAULT_RATE;
    }

    /**
     * Get amount of monthly payment.
     *
     * @param   int|string $basePrice
     * @param   float|string $rate
     * @param   int|string $loanTerm
     * @param   int|string $downPayment
     *
     * @return  Money
     *
     * @since   2.0
     */
    public function getMonthlyPayment($basePrice, $rate = self::DEFAULT_RATE, $loanTerm = self::DEFAULT_LOAN_TERM, $downPayment = 0)
    {
        $basePrice      = Filter::int($basePrice);
        $rate           = Filter::float($rate);
        $loanTerm       = Filter::int($loanTerm);
        $downPayment    = Filter::int($downPayment);
        $monthlyPayment = 0;

        if ($rate > 0) {
            $monthlyPayment = ((($basePrice - $downPayment) * $rate) / 1200) / (1 - pow((1 / (1 + ($rate / 1200))), $loanTerm));
        } else {
            $monthlyPayment = ($basePrice - $downPayment) / $loanTerm;
        }

        return $this->hyper['helper']['money']->get(ceil($monthlyPayment));
    }

    /**
     * Initialize helper.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();
        $this->_statusMap = $this->_set7SecondStatusMap();
    }

    /**
     * Check is enable credit form.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isEnable()
    {
        return Filter::bool($this->hyper['params']->get('credit_enable', 1));
    }

    /**
     * Checks credit popup.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function hasPopupInfo()
    {
        $creditPopup = $this->hyper['helper']['module']->renderById($this->hyper['params']->get('credit_info_popup'));
        return !empty($creditPopup);
    }

    /**
     * Get popup with credit info.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderInfoPopup()
    {
        if (!self::$_infoModalRendered) {
            self::$_infoModalRendered = true;
            return $this->hyper['helper']['module']->renderById($this->hyper['params']->get('credit_info_popup'));
        }
        return '';
    }

    /**
     * Get tariffs.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getTariffs()
    {
        $manager = Manager::getInstance();
        return $manager->getByPosition(Manager::ELEMENT_TYPE_CREDIT_CALCULATE);
    }

    /**
     * Count tariff.
     *
     * @return  int
     *
     * @since   2.0
     */
    public function countTariff()
    {
        return count((array) $this->getTariffs());
    }

    /**
     * Render credit tariffs.
     *
     * @param   Entity|null     $item
     * @param   int             $price
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     * @since   2.0
     */
    public function renderTariff($item = null, $price = 0)
    {
        $elements = $this->getTariffs();

        $output = [];
        /** @var Element $element */
        foreach ($elements as $element) {

            $element->setConfig([
                'item'  => $item,
                'price' => $price
            ]);

            $output[] = $element->render();
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Setup 7Second status list map.
     *
     * @return  JSON
     *
     * @since   2.0
     */
    private function _set7SecondStatusMap()
    {
        $statusList = [
            'AppSubm',
            'CustLog',
            'CredAppr',
            'CustConf',
            'WaitSign',
            'Den7S',
            'DenFO',
            'DenCust',
            'AppAband',
            'AppAbandAppr',
            'CustResj',
            'InitFee',
            'InitFeeToCust',
            'MonSent',
            'GoodsShip',
            'FillingThroughCallCentre',
            'AppFormDispalyed',
            'WaitAccept',
            'PendingAvailabilityConf',
            'GoodsAreNotAvailable',
            'GoodsAreAvailable',
            'DenAtSignFO',
            'WaitSignFO'
        ];

        $statusMap = [];
        foreach ($statusList as $statusId) {
            $langStatusKey = Str::up(Inflector::underscore($statusId));
            $statusMap[$statusId] = [
                'id'    => $statusId,
                'label' => Text::_('COM_HYPERPC_7_CLOUDS_STATUS_' . $langStatusKey . '_LABEL'),
                'desc'  => Text::_('COM_HYPERPC_7_CLOUDS_STATUS_' . $langStatusKey . '_DESC')
            ];
        }

        return new JSON($statusMap);
    }

    /**
     * Get max price from credit elements
     *
     * @return int
     *
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getMaxPrice()
    {
        $creditElements = Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_CREDIT);
        $maxPrice       = 0;

        foreach ($creditElements as $creditElement) {
            $elementMaxPrice = $creditElement?->getConfig()->get('max_price', 0, 'int');

            if (empty($elementMaxPrice)) {
                continue;
            }

            if ($maxPrice < $elementMaxPrice) {
                $maxPrice = $elementMaxPrice;
            }
        }

        return $maxPrice;
    }
}
