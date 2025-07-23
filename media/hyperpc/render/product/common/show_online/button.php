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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 *
 * @var         string       $itemName
 * @var         string       $configurationId
 * @var         int          $storeId
 * @var         string       $price
 * @var         RenderHelper $this
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;

defined('_JEXEC') or die('Restricted access');

$onlineShowStores = $this->hyper['params']->get('online_show_stores', []);

$formExists = false;
if (in_array((string) $storeId, $onlineShowStores)) {
    $formExists = true;
    $modalKey = 'show-online';
    try {
        $showOnlineForm = $this->hyper['helper']['moyskladProduct']->renderShowOnlineForm();
        if (!empty($showOnlineForm)) {
            $this->hyper['helper']['assets']->productTeaserModalButton('.jsShowOnlineButton');
            echo $this->render('product/common/show_online/_modal', [
                'modalKey' => $modalKey,
                'form'     => $showOnlineForm
            ]);
        }
    } catch (\Throwable $th) {
        $formExists = false;
    }
}
?>

<?php if ($formExists) :
    $itemInfo = [
        'name'            => $itemName,
        'price'           => $price,
        'configurationId' => $configurationId
    ]
    ?>
    <div>
        <a href="#<?= $modalKey ?>" role="button" class="jsShowOnlineButton jsProductTeaserFormButton uk-button uk-button-default uk-button-small" data-item-info='<?= json_encode($itemInfo) ?>' uk-toggle>
            <span class="uk-icon" uk-icon="camera" style="margin-inline-end: 5px"></span>
            <?= Text::_('COM_HYPERPC_PRODUCT_SHOW_ONLINE_DETAILS') ?>
        </a>
    </div>
<?php endif;
