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
 * @author      Roman Evsyukov
 *
 * @var         HyperPcViewMoysklad_Product  $this
 */

defined('_JEXEC') or die('Restricted access');

$serviceDescription = $this->folder->getParams()->get('service_desc', '');
?>
<div id="hp-product-service" class="uk-flex uk-flex-column uk-flex-center" uk-height-viewport="expand: true" style="min-height: calc(100vh - 24px)">
    <div>
        <h1 class="uk-text-center"><?= $this->folder->title ?></h1>
        <?php if (!empty($serviceDescription)) : ?>
            <?= $serviceDescription ?>
        <?php endif; ?>
    </div>

    <?= $this->hyper['helper']['render']->render('product/common/parts_slider', [
        'itemsData' => $this->getServicePartsData()
    ]); ?>

</div>
