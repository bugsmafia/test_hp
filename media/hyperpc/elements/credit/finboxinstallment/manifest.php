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

$params = $this->getParentManifestParams();

$params['max_discount'] = [
    'type'    => 'number',
    'min'     => '1',
    'max'     => '100',
    'step'    => '0.1',
    'default' => '1'
];

return [
    'core'         => true,
    'version'      => '1.0',
    'group'        => 'credit',
    'type'         => 'finboxinstallment',
    'authorUrl'    => 'https://hyperpc.ru',
    'author'       => 'Artem Vyshnevskiy',
    'authorEmail'  => 'avyshnevskiy@hyperpc.ru',
    'params'       => $params
];
