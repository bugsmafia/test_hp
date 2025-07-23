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
 *
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

defined('_JEXEC') or die('Restricted access');

$phones = $this->hyper['helper']['params']->getCompanyPhones();
$primaryPhone = array_shift($phones);
?>
<a href="tel:<?= $primaryPhone->get('rawvalue', '+74951203520') ?>" class="jsMgoNumber uk-link-reset" dir="ltr">
    <?= $primaryPhone->get('value', '+7 (495) 120-35-20') ?>
</a>
