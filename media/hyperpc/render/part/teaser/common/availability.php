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
use HYPERPC\Joomla\Model\Entity\Interfaces\Stockable;

/**
 * @var RenderHelper $this
 * @var string $availability
 */

$availabilityLabelClass = match ($availability) {
    Stockable::AVAILABILITY_INSTOCK => 'uk-text-success',
    Stockable::AVAILABILITY_PREORDER => 'uk-text-warning',
    Stockable::AVAILABILITY_OUTOFSTOCK,
    Stockable::AVAILABILITY_DISCONTINUED => 'uk-text-danger'
};
?>

<div class="tm-color-gray-100 uk-text-muted uk-flex uk-flex-center uk-flex-middle">
    <span class="uk-icon tm-margin-8-right <?= $availabilityLabelClass ?>">
        <svg width="12" height="12" viewBox="0 0 12 12">
            <circle cx="6" cy="6" r="6"></circle>
        </svg>
    </span>
    <?= Text::_('COM_HYPERPC_AVAILABILITY_LABEL_' . \strtoupper($availability)) ?>
</div>
