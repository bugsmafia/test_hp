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

use HYPERPC\Helper\RenderHelper;

/**
 * @var RenderHelper    $this
 */

?>
<div class="jsAuthWrapper">

    <div class="jsAuthFirstStep">
        <?= $this->render('login/auth_first-step') ?>
    </div>

    <form class="jsAuthSecondStep" hidden>
        <?= $this->render('login/auth_second-step') ?>
    </form>

</div>
