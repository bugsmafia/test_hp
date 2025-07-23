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
use HYPERPC\Joomla\Model\Entity\ProductFolder;

/**
 * @var RenderHelper  $this
 * @var ProductFolder $group
 */

$groupDescription        = trim($group->getParams()->get('configurator_desc', ''));
$groupDescriptionHeading = trim($group->getParams()->get('configurator_desc_heading', ''));
$boundaryId              = 'boundary-' . $group->alias;
?>

<div class="sub-group-description">
    <div class="uk-flex uk-flex-middle uk-flex-wrap"<?= !empty($groupDescription) ? ' id="' . $boundaryId . '"' : '' ?>>
        <span class="uk-text-muted uk-icon" uk-icon="icon: hp-<?= $group->alias ?>; ratio:2"></span>
        <span class="uk-h3 uk-text-truncate"<?= !empty($groupDescription) && !empty($groupDescriptionHeading) ? ' style="margin-inline-end: auto"' : '' ?>><?= $group->title ?></span>
        <?php if (!empty($groupDescription)) : ?>
            <?php if (!empty($groupDescriptionHeading)) : ?>
                <a class="uk-link-muted tm-link-dashed hp-group-info-button" role="button">
                    <?= $groupDescriptionHeading ?>
                </a>
            <?php else :
                $iconQuestion = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle fill="none" stroke="#000" stroke-width="1.1" cx="10" cy="10" r="9"></circle><circle cx="10.44" cy="14.42" r="1.05"></circle><path fill="none" stroke="#000" stroke-width="1.2" d="M8.17,7.79 C8.17,4.75 12.72,4.73 12.72,7.72 C12.72,8.67 11.81,9.15 11.23,9.75 C10.75,10.24 10.51,10.73 10.45,11.4 C10.44,11.53 10.43,11.64 10.43,11.75"></path></svg>';
                ?>
                <a class="uk-icon tm-button-icon uk-margin-small-left hp-group-info-button" role="button">
                    <?= $iconQuestion ?>
                </a>
            <?php endif; ?>
            <div class="uk-drop" uk-drop="pos: bottom-center; stretch: x; boundary: #<?= $boundaryId ?>; offset: 4; flip: false; duration: 0; delay-hide: 250; mode: click">
                <div class="uk-padding-small">
                    <div class="uk-card uk-card-body uk-card-small uk-card-default">
                        <button class="uk-position-top-right uk-icon uk-close uk-close-default uk-drop-close" type="button" uk-close style="padding:10px"></button>
                        <div><?= $groupDescription ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
