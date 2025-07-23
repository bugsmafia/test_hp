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
 */

use Joomla\CMS\Language\Text;
use HYPERPC\Elements\ElementConfiguratorActions;

defined('_JEXEC') or die('Restricted access');

/**
 * Class ElementConfigurationActionsRemove
 *
 * @since   2.0
 */
class ElementConfigurationActionsRemove extends ElementConfiguratorActions
{

    /**
     * Initialize method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadAssets();
    }

    /**
     * Load assets.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function loadAssets()
    {
        $this->hyper['helper']['assets']
            ->js('elements:' . $this->_group . '/' . $this->_type . '/assets/js/widget.js')
            ->widget('body', 'HyperPC.SiteConfigurationActionsRemove', [
                'msgAjaxError'   => Text::_('COM_HYPERPC_JS_MSG_AJAX_ERROR'),
                'msgSendConfirm' => Text::_('HYPER_ELEMENT_CONFIGURATION_ACTIONS_REMOVE_CONFIRM_MESSAGE')
            ]);
    }

    /**
     * Render action button in profile account.
     *
     * @return  string|null
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function renderActionButton()
    {
        $configuration = $this->getConfiguration();

        if ($configuration->isBelongsToOrder()) {
            return null;
        }

        return implode(null, [
            '<a href="#" class="jsRemoveConfig" data-configuration-id="' . $configuration->id . '">',
                $this->getAccountActionTile(),
            '</a>'
        ]);
    }
}
