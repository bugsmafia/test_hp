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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use HYPERPC\Elements\ElementConfiguratorActions;

/**
 * Class ElementConfigurationActionsPdf
 */
class ElementConfigurationActionsPdf extends ElementConfiguratorActions
{
    /**
     * Render action button in profile account.
     *
     * @return  string
     */
    public function renderActionButton()
    {
        $route = Route::_(
            'index.php?option=com_hyperpc&format=raw&tmpl=component&task=configurator.build_pdf&configuration_id=' .
            $this->getConfiguration()->id
        );

        return implode('', [
            '<a href="' . $route . '" target="_blank">',
                $this->getAccountActionTile(),
            '</a>'
        ]);
    }
}
