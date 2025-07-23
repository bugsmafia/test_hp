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

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewManager
 *
 * @since       2.0
 */
class HyperPcViewManager extends ViewLegacy
{

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $userIsSuperAdmin = $this->getCurrentUser()->authorise('core.admin');
        if (!$userIsSuperAdmin) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $this->_addToolbar();
        ToolbarHelper::preferences(HP_OPTION);

        parent::display($tpl);
    }

    protected function _loadAssets()
    {
        parent::_loadAssets();

        $this->hyper['helper']['assets']
            ->js('js:widget/admin/account-normalize.js')
            ->widget('#j-main-container', 'HyperPC.AccountNormalize');
    }

    /**
     * Add toolbar for display action.
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function _addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_DEVELOPMENT_MANAGER'), 'joomla');
    }
}
