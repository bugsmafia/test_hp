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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var         RenderHelper    $this
 * @var         PartMarker      $part
 * @var         CategoryMarker  $group
 * @var         ProductMarker   $product
 * @var         string          $hiddenCls
 * @var         string          $hiddenAttr
 * @var         bool            $hasChangeParts
 */

$shortDesc = trim($part->getParams()->get('reload_content_short_desc'));
$fullDesc  = trim($part->getParams()->get('reload_content_desc'));
$content   = !empty($shortDesc) ? $shortDesc : $fullDesc;

if ($part->quantity <= 1) {
    $content = str_replace(['{quantity} x ', '{quantity} x ', '{quantity} - '], '', $content);
}

$content = $this->hyper['helper']['macros']
    ->setData($part->getArray())
    ->text($content);

$task = 'moysklad_product.display-group-configurator';
$groupIdKey = 'product_folder_id';

$groupChangePartUrl = $this->hyper['route']->build([
    'd_oid'     => '0',
    'd_pid'     => $part->id,
    'tmpl'      => 'component',
    $groupIdKey => $group->id,
    'id'        => $product->id,
    'task'      => $task
]);

$partName = ($part->getParams()->get('reload_content_name')) ? $part->getParams()->get('reload_content_name') : $part->name;
if (!isset($this->defaultPartsData[$part->id])) {
    $this->defaultPartsData[$part->id] = [
        'option_id'  => '0',
        'desc'       => $content,
        'id'         => $part->id,
        'name'       => $partName,
        'url_view'   => $part->getViewUrl(),
        'url_change' => $groupChangePartUrl,
        'advantages' => $part->getAdvantages()
    ];
}

$rowClassPrefix = $part->group_id;
if ($hasChangeParts && false) { // TODO: change parts with redefined content
    $rowClassPrefix .= ' jsCanBeChanged';
}
?>

<?php if (!empty($content)) : ?>
    <div class="hp-group-row-<?= $rowClassPrefix ?> hp-equipment-part<?= $hiddenCls ?>"<?= $hiddenAttr ?>
         data-id="<?= $part->id ?>">
        <div>
            <?= $content ?>
        </div>

        <?php if ($hasChangeParts && false) : // TODO: change parts with redefined content ?>
            <div class="uk-margin-small tm-text-medium">
                <div class="uk-grid uk-grid-small uk-grid-divider" uk-grid>
                    <span>
                        <a class="hp-equipment-part__link uk-button-text jsItemChangeButton jsLoadIframe" href="<?= $groupChangePartUrl ?>">
                            <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PART_CHANGE') ?>
                        </a>
                        <input class="jsGroupPartValue" type="hidden" name="group[<?= $part->getFolderId() ?>][part]" value="<?= $part->id ?>"/>
                        <input class="jsGroupOptionValue" type="hidden" name="group[<?= $part->getFolderId() ?>][option]" value="0"/>
                    </span>
                    <span class="jsItemResetButton" hidden>
                        <a href="#" class="hp-equipment-part__link uk-button-text">
                            <?= Text::_('COM_HYPERPC_PRODUCT_QUICK_CONFIGURATOR_PART_RESET') ?>
                        </a>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif;
