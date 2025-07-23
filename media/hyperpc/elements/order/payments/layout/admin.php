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
 * @var         \ElementOrderPayments               $this
 * @var         \HYPERPC\Elements\Element           $element
 * @var         \HYPERPC\Elements\ElementPayment    $method
 */

defined('_JEXEC') or die('Restricted access');

$value = $this->getValue();
foreach ($this->getMethods() as $method) {
    if ($method->getType() === $value) {
        $value = $method->getConfig('name');
        break;
    }
}
?>
<dt><?= $this->getConfig('name') ?></dt>
<dd><?= $value ?></dd>
