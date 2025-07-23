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
 * @package     HYPERPC\Joomla\Routes\Rules
 *
 * @since       2.0
 */
class ProductInstockRules extends MenuRules
{

    /**
     * Finds the right Itemid for this query
     *
     * @param   array  &$query  The query array to process
     *
     * @return  void
     *
     * @since   3.4
     */
    public function preprocess(&$query)
    {
        $queryView = $query['view'] ?? null;

        if (!$queryView || $query['view'] !== 'product_in_stock') {
            return;
        }

        $active = $this->router->menu->getActive();
        if ($active && isset($active->query['view']) && $active->query['view'] === 'products_in_stock') {
            return;
        }

        $context = $query['context'] ?? HP_OPTION . '.product';

        $attributes = 'link';
        $values = 'index.php?option=' . HP_OPTION . '&view=products_in_stock&context=' . $context;

        $menuItem = $this->router->menu->getItems($attributes, $values, true);

        if ($menuItem) {
            $query['Itemid'] = $menuItem->id;
        }
    }
}
