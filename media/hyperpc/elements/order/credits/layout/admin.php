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
 * @var         array $elements
 * @var         \ElementOrderCredits $this
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Elements\Manager;
use HYPERPC\Elements\ElementCredit;

$value = $this->getValue();

/** @var ElementCredit[] $methods */
$methods = $this->getManager()->getByPosition(Manager::ELEMENT_TYPE_CREDIT);

foreach ($methods as $method) {
    if ($method->getType() === $value) {
        echo implode(PHP_EOL, [
            '<dt>' . $this->getConfig('name') . '</dt>',
            '<dd>' . $method->getConfig('name') . '</dd>'
        ]);

        break;
    }
}
