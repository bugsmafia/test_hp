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
 * @author      Artem Vyshnevskly
 */

namespace HYPERPC\Joomla\Router\Rules;

use Joomla\CMS\Component\Router\Rules\MenuRules;

/**
 * @package HYPERPC\Joomla\Routes\Rules
 */
class ConfiguratorRules extends MenuRules
{
    /**
     * Process the parsed variables.
     *
     * @param   array   &$query     The vars that should be converted
     * @param   array   &$segments  The URL segments to create
     *
     * @return  void
     */
    public function build(&$query, &$segments): void
    {
        $queryView = $query['view'] ?? null;
        if (!$queryView || $queryView !== 'configurator_moysklad') {
            return;
        }

        $menuItemId = $query['Itemid'] ?? null;
        if (!$menuItemId) {
            return;
        }

        $menuItem = $this->router->menu->getItem($menuItemId);
        if (!$menuItem) {
            return;
        }

        if (\array_key_exists('view', $menuItem->query) && $menuItem->query['view'] === 'moysklad_product') {
            unset($query['product_folder_id']);
        }
    }
}
