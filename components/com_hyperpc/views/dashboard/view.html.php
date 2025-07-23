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

use Joomla\CMS\Factory;
use HYPERPC\Joomla\View\ViewLegacy;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewDashboard
 *
 * @since       2.0
 */
class HyperPcViewDashboard extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     * @return  mixed
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $menus = $app->getMenu();

        $menu = $menus->getActive();

        if ($menu->getParams()->get('robots')) {
            $this->getDocument()->setMetadata('robots', $menu->getParams()->get('robots'));
        }

        parent::display($tpl);
    }
}
