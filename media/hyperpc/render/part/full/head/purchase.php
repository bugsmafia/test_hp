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

use HYPERPC\Money\Type\Money;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var RenderHelper                $this
 * @var Money                       $partPrice
 * @var PartMarker|MoyskladService  $part
 * @var ?OptionMarker[]             $options
 * @var ?OptionMarker               $optionDefault
 */
?>

<div class="hp-part-head__purchase uk-flex uk-flex-middle uk-flex-wrap">

    <?= $this->hyper['helper']['render']->render('common/price/item-price', [
        'price'      => $partPrice,
        'entity'     => $part,
        'htmlPrices' => true
    ]); ?>

    <div class="uk-width-1-1 uk-width-auto@s">
        <?php if (isset($options) && count($options)) : ?>
            <?= $part->getRender()->getCartBtn('button', [
                'option'           => $optionDefault,
                'part'             => $part,
                'useDefaultOption' => false,
                'size'             => 'default'
            ]);
            ?>
        <?php else : ?>
            <?= $part->getRender()->getCartBtn('button', ['size' => 'default']) ?>
        <?php endif; ?>
    </div>
</div>
