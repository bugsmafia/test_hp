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
 * @var         \ElementCoreTextarea $this
 */

defined('_JEXEC') or die('Restricted access');

$value = trim(strip_tags((string) $this->getValue()));
?>

<?php if ($this->getConfig('name') === 'Комментарий') : ?>
    <dt><?= $this->getTitle() ?></dt>
    <dd>
        <blockquote>
            <?= ($value) ? $value : '---' ?>
        </blockquote>
    </dd>
<?php else : ?>
    <?php if ($value) : ?>
        <dt><?= $this->getTitle() ?></dt>
        <dd><?= $value ?></dd>
    <?php endif; ?>
<?php endif;
