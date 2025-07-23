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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use JBZoo\Utils\FS;

$autoload = realpath('./libraries/hyperpc/vendor/autoload.php');

if ($autoload !== false) {
    /** @noinspection PhpIncludeInspection */
    require_once $autoload;
} else {
    echo 'Please execute "composer update" !' . PHP_EOL;
    exit(1);
}
