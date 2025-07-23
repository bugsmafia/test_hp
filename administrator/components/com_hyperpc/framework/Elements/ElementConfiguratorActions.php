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

namespace HYPERPC\Elements;

use JBZoo\Utils\Filter;
use HYPERPC\Helper\ConfigurationHelper;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;

/**
 * Class ElementConfiguratorActions
 *
 * @property    ConfigurationHelper $helper
 *
 * @since       2.0
 */
abstract class ElementConfiguratorActions extends Element
{

    /**
     * Check uer group for enabled action.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function canDo()
    {
        $userGroups = (array) $this->hyper['user']->groups;
        foreach ((array) $this->getConfig('enabled_groups') as $groupId) {
            if (in_array((string) $groupId, $userGroups)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get action title.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getAccountActionTile()
    {
        $actionName = $this->getConfig('account_action_name', '', 'trim');
        $title = ($actionName) ? $actionName : $this->getConfig('title');
        if ($this->getConfig('action_icon')) {
            $icon  = '<span uk-icon="' . $this->getConfig('action_icon') . '"></span>';
            $title = $icon . $title;
        }

        return $title;
    }

    /**
     * Get configuration entity.
     *
     * @return  SaveConfiguration
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getConfiguration()
    {
        if ($this->getConfig('configuration') instanceof SaveConfiguration) {
            return $this->getConfig('configuration');
        }

        if ($this->hyper['input']->get('id')) {
            return $this->helper->findById($this->hyper['input']->get('id'));
        }

        return new SaveConfiguration();
    }

    /**
     * Get entity itemKey
     *
     * @return false|string
     *
     * @throws \JBZoo\Utils\Exception
     *
     * @since 2.0
     */
    public function getItemKey()
    {
        $configuration = $this->getConfiguration();

        if ($configuration->id) {
            $product = $configuration->getProduct();
        }

        if (!isset($product) || !$product->id) {
            return false;
        }

        return $product->getItemKey();
    }

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
        $this->helper = $this->hyper['helper']['configuration'];
    }

    /**
     * Check is enable element.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isEnabled()
    {
        return Filter::bool($this->getConfig('is_enable', HP_STATUS_PUBLISHED));
    }

    /**
     * Check is single element position.
     *
     * @return  bool
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function isSingle()
    {
        return Filter::bool($this->getConfig('is_single'));
    }

    /**
     * Render action button in profile account.
     *
     * @return  string
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function renderActionButton()
    {
        return implode(null, [
            '<a href="#">',
                $this->getAccountActionTile(),
            '</a>'
        ]);
    }

    /**
     * Check configuration contact data.
     *
     * @param   SaveConfiguration  $configuration
     *
     * @return  bool
     *
     * @since   2.0
     */
    protected function _checkContactData(SaveConfiguration $configuration)
    {
        return (
            $configuration->params->get('username') &&
            $configuration->params->get('phone') &&
            $configuration->params->get('email')
        );
    }
}
