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
use Joomla\CMS\Language\Text;

/**
 * @var         string       $itemName
 * @var         string       $configurationId
 * @var         string       $price
 * @var         RenderHelper $this
 */

$modalKey = 'buy-now-modal';

$formExists = true;
try {
    $buyNowForm = $this->hyper['helper']['moyskladProduct']->renderBuyNowForm();
    if (!empty($buyNowForm)) {
        echo $this->hyper['helper']['render']->render('product/common/buy_now/_modal', [
            'modalKey' => $modalKey,
            'form'     => $buyNowForm
        ]);
    }
} catch (\Throwable $th) {
    $formExists = false;
}
?>

<?php if ($formExists) :
    $this->hyper['helper']['assets']->productTeaserModalButton('.jsBuyNowButton');

    $itemInfo = [
        'name'  => $itemName,
        'price' => $price,
    ];

    if (isset($configurationId) && $configurationId) {
        $itemInfo['configurationId'] = $configurationId;
    }
    ?>
    <a href="#<?= $modalKey ?>" role="button" class="jsBuyNowButton jsProductTeaserFormButton uk-link-muted tm-link-dashed" data-item-info='<?= json_encode($itemInfo) ?>' data-uk-toggle>
        <?= Text::_('COM_HYPERPC_BUY_NOW') ?>
    </a>
<?php endif;
