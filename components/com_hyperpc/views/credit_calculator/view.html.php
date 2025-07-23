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

use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\ORM\Entity\ProductInStock;
use HYPERPC\Joomla\Model\Entity\Entity;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewCredit_Calculator
 *
 * @since       2.0
 */
class HyperPcViewCredit_Calculator extends ViewLegacy
{

    const DEFAULT_PRICE_VAL = 100000;

    /**
     * Hold item object.
     *
     * @var     null|Entity
     *
     * @since   2.0
     */
    public $item;

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  mixed
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $this->hyper['doc']->setMetaData('robots', 'noindex, nofollow');

        $type               = $this->hyper['input']->get('type');
        $itemId             = $this->hyper['input']->get('id', 0, 'string');
        $optionId           = $this->hyper['input']->get('option_id', 0, 'string');
        $configurationId    = $this->hyper['input']->get('configuration_id', 0, 'string');
        $priceValue         = $this->hyper['input']->get('price');
        $price              = $this->hyper['helper']['money']->get(($priceValue ?: 0));

        if ($type === 'position' && $itemId !== 0) {
            $position = $this->hyper['helper']['position']->findById($itemId);
        }

        if (isset($position) && $position->id) {
            $type = $position->getType();
        }

        if ($type === 'product' && $itemId) {
            if (preg_match('/:/', $itemId)) {
                list($itemId, $configurationId) = explode(':', $itemId);
            }

            /** @var ProductMarker item */
            $this->item = $this->hyper['helper']['moyskladProduct']->findById($itemId);
            if ($this->item->id) {
                $products = $this->hyper['helper']['moyskladStock']->getProductsByConfigurationId($configurationId);
                if (count($products)) {
                    $this->item = array_shift($products);
                }

                if (!$priceValue) {
                    $price = $this->item->getConfigPrice(true);
                }
            }
        } elseif ($type === 'part' && $itemId) {
            /** @var PartMarker item */
            $this->item = $this->hyper['helper']['moyskladPart']->findById($itemId);

            if ($this->item->id && $optionId) {
                $option = clone $this->hyper['helper']['moyskladVariant']->getById((int) $optionId);
                $this->item->set('option', $option);
                if ($option->id) {
                    $this->item->setListPrice($option->getListPrice());
                }
            }

            if (!$priceValue) {
                $price = $this->item->getPrice(false);
            }
        }

        if ($this->item instanceof Entity && !$this->item->id) {
            $this->item = null;
        }

        if (!$price->val()) {
            $price = $this->hyper['helper']['money']->get(self::DEFAULT_PRICE_VAL);
        }

        echo HTMLHelper::_('content.prepare', $this->hyper['helper']['render']->render('credit/calculate_default', [
            'item'   => $this->item,
            'price'  => $price,
        ]));
    }
}
