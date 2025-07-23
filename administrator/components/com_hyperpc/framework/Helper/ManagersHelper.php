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

namespace HYPERPC\Helper;

/**
 * Class ManagersHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ManagersHelper extends AppHelper
{

    /**
     * Render managers.
     *
     * @return  string
     *
     * @since   2.0
     */
    public function renderManagers()
    {
        $managersIds   = (array) $this->hyper['params']->get('manager_ids');
        $managersCount = (int) $this->hyper['params']->get('managers_count');

        return $this->hyper['helper']['render']->render('common/managers/default', [
            'managersIds'   => $managersIds,
            'managersCount' => $managersCount
        ]);
    }
}
