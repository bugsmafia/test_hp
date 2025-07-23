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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoySkladHelper;
use HYPERPC\Joomla\View\ViewLegacy;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Exception\NotAllowed;

/**
 * Class HyperPcViewMoysklad
 *
 * @property array $webhooks
 *
 * @since    2.0
 */
class HyperPcViewMoysklad extends ViewLegacy
{

    /**
     * View display action.
     *
     * @param   null|string $tpl
     *
     * @return  void
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

        ToolbarHelper::title(Text::_('COM_HYPERPC_VIEW_MOYSKLAD_TITLE'), 'hyperpc');
        ToolbarHelper::preferences(HP_OPTION);

        /** @var MoySkladHelper */
        $moyskladHelper = $this->hyper['helper']['moysklad'];
        $this->webhooks = $moyskladHelper->getWebhooks();

        parent::display($tpl);
    }

    /**
     * Load assets for display action.
     *
     * @return  void
     *
     * @throws  InvalidArgumentException
     *
     * @since   2.0
     */
    protected function _loadAssets()
    {
        parent::_loadAssets();

        $this->hyper['helper']['assets']
            ->js('js:widget/admin/moysklad-webhooks.js')
            ->widget('body', 'HyperPC.MoyskladWebhooks');
    }
}
