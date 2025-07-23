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

use Joomla\CMS\HTML\HTMLHelper;

/**
 * @var HyperPcViewMoysklad_Part $this
 */

$review         = $this->part->getReview();
$containerClass = $this->part->getParams()->get('full_width', 0) ? '' : 'uk-container uk-container-large';
$sectionClass   = $this->part->getParams()->get('full_width', 0) ? '' : 'uk-section uk-section-small';
$description    = $this->part->getParams()->get('reload_content_desc', '');
?>

<div class="hp-part">

    <div class="<?= $sectionClass ?>">
        <div class="<?= $containerClass ?>">
            <?php if (trim($description) !== '') : ?>
                <div id="part-description">
                    <?= HTMLHelper::_('content.prepare', $description); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
