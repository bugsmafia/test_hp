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
 *
 * @var         HyperPcViewDashboard $this
 */

defined('_JEXEC') or die('Restricted access');
?>
<div class="uk-container uk-container-large uk-overflow-hidden">
    <h2 class="uk-h1 uk-text-center uk-margin-medium-bottom">
        Персональная информация
    </h2>
    <ul class="uk-margin-bottom">
        <li>
            Ваш персональный ID визита: <strong class="uk-text-primary uk-heading-small"><?= $this->hyper['input']->cookie->get('roistat_visit', 'неизвестно') ?></strong>
        </li>
    </ul>
</div>