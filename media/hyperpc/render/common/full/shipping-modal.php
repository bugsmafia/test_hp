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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Object\Delivery\MeasurementsData;
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var RenderHelper $this
 * @var Stockable $entity
 * @var MeasurementsData $parcelData
 */
?>

<div id="delivery-options" class="uk-modal" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
        <button class="uk-modal-close-default uk-close-large uk-icon uk-close" type="button" uk-close></button>
        <div class="uk-h3 uk-margin-small-top">
            <?= Text::_('COM_HYPERPC_WAYS_TO_RECEIVE_AN_ORDER') ?>
        </div>
        <ul uk-tab>
            <li class="uk-active">
                <a href="#">
                    <?= Text::_('COM_HYPERPC_DELIVERY') ?>
                </a>
            </li>
            <li>
                <a href="#">
                    <?= Text::_('COM_HYPERPC_PICKUP') ?>
                </a>
            </li>
        </ul>

        <ul class="uk-margin uk-switcher" style="min-height: 170px">
            <li class="uk-active">
                <?= $this->hyper['helper']['render']->render('common/full/delivery', [
                    'entity'     => $entity,
                    'parcelInfo' => $parcelData
                ]); ?>
            </li>
            <li>
                <div class="uk-flex">
                    <div class="uk-width-expand">
                        <div class="tm-text-medium uk-text-emphasis">
                            <span class="uk-flex-none uk-margin-small-right uk-icon uk-text-top" uk-icon="home" style="padding-top: 2px;"></span><?= Text::_('COM_HYPERPC_FROM_THE_STORE') ?>
                        </div>
                        <hr class="uk-margin-small">
                        <?= $this->hyper['helper']['render']->render('common/full/pickup-stores', [
                            'entity' => $entity
                        ]); ?>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
