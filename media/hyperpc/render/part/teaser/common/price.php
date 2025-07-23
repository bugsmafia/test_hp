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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Money\Type\Money;
use Joomla\CMS\Language\Text;

/**
 * @var RenderHelper $this
 * @var Money $price
 */

$monthlyPayment = $this->hyper['helper']['credit']->getMonthlyPayment($price->val());
?>
<div>
    <div class="tm-font-semi-bold tm-color-white">
        <?= $price->text() ?>
    </div>
    <div class="uk-text-small tm-color-gray-100">
        <?= Text::sprintf('COM_HYPERPC_CREDIT_MONTHLY_PAYMENT_SHORT', $monthlyPayment) ?>
    </div>
</div>
