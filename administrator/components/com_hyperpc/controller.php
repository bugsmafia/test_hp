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

use HYPERPC\Joomla\Controller\ControllerLegacy;

/**
 * Class HyperPcController
 *
 * @since   2.0
 */
class HyperPcController extends ControllerLegacy
{

    /**
     * The default view.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $default_view = 'dashboard';
}
