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
 */

use HYPERPC\Data\JSON;
use JBZoo\Utils\Filter;
use HYPERPC\Elements\Element;
use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\CreditHelper;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ElementCreditCalculateRate
 *
 * @property    CreditHelper $helper
 *
 * @since       2.0
 */
class ElementCreditCalculateRate extends Element
{

    /**
     * Recount action.
     *
     * @return  JSON
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function actionRecount()
    {
        return new JSON([
            'price'                 => $this->getPrice()->val(),
            'monthlyPayment'        => $this->getMonthlyPayment()->html(),
            'checkoutTotalPrice'    => $this->getCheckoutTotalPrice()->html(),
            'overPayment'           => $this->getOverPayment()->html(),
            'overPaymentByPercent'  => $this->getOverPaymentByPercent()
        ]);
    }

    /**
     * Get allowed products.
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getAllowedProducts()
    {
        return (array) $this->getConfig('allowed_products', []);
    }

    /**
     * Get checkout total price value.
     *
     * @return  int|Money
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getCheckoutTotalPrice()
    {
        $rateVal = $this->getRateVal();
        $value   = clone $this->getMonthlyPayment();

        if ($rateVal > 0) {
            return $value->multiply($this->getTermVal());
        }

        return $this->getPrice(Filter::bool($this->getDiscount()));
    }

    /**
     * Get discount value.
     *
     * @return  float
     *
     * @throws` \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getDiscount()
    {
        return Filter::float($this->getConfig('discount'));
    }

    /**
     * Get monthly payment.
     *
     * @return  mixed
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getMonthlyPayment()
    {
        $rateVal  = $this->getRateVal();
        $priceVal = $this->getPrice(Filter::bool($this->getDiscount()))->val();
        $termVal  = $this->getTermVal();

        return $this->helper->getMonthlyPayment($priceVal, $rateVal, $termVal);
    }

    /**
     * Get over payment value.
     *
     * @return  Money
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getOverPayment()
    {
        return $this->getCheckoutTotalPrice()->add(-$this->getPrice()->val(), true);
    }

    /**
     * Get percent over payment value.
     *
     * @return  float|int
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getOverPaymentByPercent()
    {
        if ($this->getPrice()->val() > 0) {
            return round(($this->getOverPayment()->val() / $this->getPrice()->val()) * 100, 2);
        }

        return 0;
    }

    /**
     * Get price value.
     *
     * @param   bool  $checkDiscount
     *
     * @return  int|Money
     *
     * @throws  \JBZoo\Utils\Exception
     * @throws  \JBZoo\SimpleTypes\Exception
     *
     * @since   2.0
     */
    public function getPrice($checkDiscount = false)
    {
        /** @var Money $price */
        $price = $this->hyper['helper']['money']->get($this->getConfig('price', 0, 'float'));
        $item  = $this->getConfig('item');
        if (!$price->val()) {
            if ($item instanceof ProductMarker) {
                $price = $item->getConfigPrice(true);
            } elseif ($item instanceof PartMarker) {
                $price = $item->getPrice(false);
            }
        }

        $discount = $this->getDiscount();
        if ($checkDiscount && $discount) {
            return $price->add('-' . $discount . '%', true);
        }

        return $price;
    }

    /**
     * Get rate value.
     *
     * @return  float
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getRateVal()
    {
        return Filter::float($this->getConfig('rate'));
    }

    /**
     * Get term value.
     *
     * @return  int
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getTermVal()
    {
        $term = $this->getConfig('term');
        if (preg_match('/:/', $term)) {
            list($from, $to) = explode(':', $term);
            $term = $from;
        }

        return Filter::int($term);
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
        $this->registerAction('recount');
        $this->helper = $this->hyper['helper']['credit'];
    }

    /**
     * Check is available element.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isAvailable()
    {
        $itemId = null;
        $item   = $this->getConfig('item');
        if ($item instanceof Entity) {
            $itemId = $item->id;
        }

        if ($itemId === null) {
            return true;
        }

        $productIds = $this->getAllowedProducts();
        if (count($productIds) && !in_array((string) $itemId, $productIds)) {
            return false;
        }

        return true;
    }

    /**
     * Check is default.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2,0
     */
    public function isDefault()
    {
        return Filter::bool($this->getConfig('is_default', false));
    }

    /**
     * Render action.
     *
     * @param   array $params
     *
     * @return  null|string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function render(array $params = [])
    {
        if (!$this->isAvailable()) {
            return null;
        }

        return parent::render($params);
    }
}
