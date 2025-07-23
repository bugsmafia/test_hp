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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use HYPERPC\Joomla\View\ViewLegacy;

defined('_JEXEC') or die('Restricted access');

/**
 * Class HyperPcViewMedia
 *
 * @property    JObject $state
 * @property    array   $images
 * @property    array   $folders
 * @property    string  $baseURL
 * @property    JObject $_tmp_img
 * @property    JObject $_tmp_folder
 *
 * @since       2.0
 */
class HyperPcViewMedia extends ViewLegacy
{

    /**
     * Hook on initialize view.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since    2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        JLoader::register('MediaModelList', JPATH_ADMINISTRATOR . '/components/com_media/models/list.php');
        //  Load the media helper class.
        JLoader::register('MediaHelper', JPATH_ADMINISTRATOR . '/components/com_media/helpers/media.php');

        if (!defined('COM_MEDIA_BASE')) {
            define('COM_MEDIA_BASE', JPATH_ROOT . '/images');
        }

        if (!defined('COM_MEDIA_BASEURL')) {
            define('COM_MEDIA_BASEURL', Uri::root() . 'images');
        }

        $model = Factory::getApplication()->bootComponent('com_media')->getMVCFactory()->createModel('Api');
        $this->setModel($model, true);
    }

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  mixed
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $adapter = $this->getAdapter();
        $path    = $this->getPath();

        $files = $this->getModel()->getFiles($adapter, $path, ['search' => null]);
        foreach ($files as $file) {
            switch ($file->type) {
                case 'dir':
                    $this->folders[] = (array) $file;
                    break;
                default:
                    $this->images[] = (array) $file;
                    break;
            }
        }

        $this->baseURL = COM_MEDIA_BASE;
        $this->images  = (array) $this->get('images');
        $this->folders = (array) $this->get('folders');
        $this->state   = $this->get('state');

        parent::display($tpl);
    }

    /**
     * Set the active folder.
     *
     * @param   int $index
     *
     * @return  void
     *
     * @since   2.0
     */
    public function setFolder($index = 0)
    {
        if (array_key_exists($index, $this->folders)) {
            $this->_tmp_folder = &$this->folders[$index];
        } else {
            $this->_tmp_folder = new JObject;
        }
    }

    /**
     * Set the active image.
     *
     * @param   int $index
     *
     * @return  void
     *
     * @since   2.0
     */
    public function setImage($index = 0)
    {
        if (array_key_exists($index, $this->images)) {
            $this->_tmp_img = &$this->images[$index];
        } else {
            $this->_tmp_img = new JObject;
        }
    }

    /**
     * Get the Adapter.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function getAdapter()
    {
        $parts = explode(':', $this->hyper['input']->getString('folder', 'local-images'), 2);

        return $parts[0];
    }

    /**
     * Get the Path.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function getPath()
    {
        $parts = explode(':', $this->hyper['input']->getString('folder', ''), 2);

        if (count($parts) < 2) {
            return '/';
        }

        return $parts[1];
    }
}
