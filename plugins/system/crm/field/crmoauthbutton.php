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

use HYPERPC\App;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\CrmHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Class JFormFieldCrmOauthButton
 *
 * @since 2.0
 */
class JFormFieldCrmOauthButton extends FormField
{

    /**
     * Hyper app instance
     *
     * @var     App
     *
     * @since   2.0
     */
    protected $hyper;

    /**
     * Crm helper instance
     *
     * @var CrmHelper
     *
     * @since   2.0
     */
    protected $crmHelper;

    /**
     * Plugin params
     *
     * @var     Registry
     *
     * @since   2.0
     */
    protected $pluginParams;

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'CrmOauthButton';

    /**
     * Name of the layout being used to render the field
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'field.crmoauthbutton';

    /**
     * Get the layouts paths
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutPaths()
    {
        return [
            dirname(__DIR__) . '/layouts',
            JPATH_SITE . '/layouts'
        ];
    }

    /**
     * Method to instantiate the form field object.
     *
     * @param   Form  $form  The form to attach to the form field object.
     *
     * @since   2.0
     */
    public function __construct($form = null)
    {
        parent::__construct($form);

        $this->hyper = App::getInstance();

        $plugin = PluginHelper::getPlugin('system', 'crm');
        if (empty($plugin)) {
            $this->hyper['cms']->enqueueMessage(Text::_('PLG_SYSTEM_CRM_ERROR_PLUGIN_DISABLED'), 'error');
        }

        $this->pluginParams = new Registry($plugin->params ?? []);

        try {
            $this->crmHelper = $this->hyper['helper']['crm'];
        } catch (\Throwable $th) {
        }
    }

    /**
     * Check auth state
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function checkAuthState()
    {
        try {
            $result = $this->crmHelper->checkOauthState();
        } catch (\Throwable $th) {
            return false;
        }

        return $result;
    }

    /**
     * Get crm button js source url
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getButtonJsSourceUrl()
    {
        $buttonJsSourceUrl = '';
        switch ($this->getPlatform()) {
            case 'amocrm':
                $buttonJsSourceUrl = 'https://www.amocrm.ru/auth/button.min.js';
                break;
            case 'kommo':
                $buttonJsSourceUrl = 'https://www.kommo.com/auth/button.min.js';
                break;
        }

        return $buttonJsSourceUrl;
    }

    /**
     * Get path for file with integration keys
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getClientFilePath()
    {
        try {
            $path = $this->crmHelper->getClientFilePath();
        } catch (\Throwable $th) {
            return '';
        }

        return $path;
    }

    /**
     * Get client
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getClientId()
    {
        try {
            $result = $this->crmHelper->getClientId();
        } catch (\Throwable $th) {
            return '';
        }

        return $result;
    }

    /**
     * Get client
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getClientSecret()
    {
        try {
            $result = $this->crmHelper->getClientSecret();
        } catch (\Throwable $th) {
            return '';
        }

        return $result;
    }

    /**
     * Get folder path for auth files
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getOauthDirPath()
    {
        try {
            $path = $this->crmHelper->getOauthDirPath();
        } catch (\Throwable $th) {
            return '';
        }

        return $path;
    }

    /**
     * Get platform from the plugin params
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getPlatform()
    {
        return $this->pluginParams->get('platform', '');
    }

    /**
     * Get state hash
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getStateHash()
    {
        try {
            $hash = $this->crmHelper->getOauthStateHash();
        } catch (\Throwable $th) {
            return md5('{}');
        };

        return $hash;
    }

    /**
     * Get subdomain from the plugin params
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getSubdomain()
    {
        return $this->pluginParams->get('subdomain', '');
    }
}
