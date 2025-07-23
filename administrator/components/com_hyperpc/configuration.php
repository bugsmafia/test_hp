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
 * @link        https://github.com/HYPER-PC/HYPERPC".
 *
 * @author      Sergey Kalistratov © <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

return [

    /**
     * Development user ids.
     *
     * @desc By specifying the user id you will have access to the hidden sections of the developer.
     */
    'devUsers' => [
        532,
        534,
        632,
        635
    ],

    /**
     * Specify a local domain for development
     */
    'local_domain' => [
        'epix.loc',
        'hyper.pc',
        'hyperpc.new',
        'hyperpc.loc',
        'new.hyperpc.loc'
    ],

    'office_ip' => '109.73.13.77',

    'debug_ip' => [
        '127.0.0.1',
        '88.147.153.3',
        '178.217.121.35',
        '78.85.16.98',
        '109.73.13.77'
    ]
];
