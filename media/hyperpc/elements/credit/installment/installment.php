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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use HYPERPC\Elements\Manager;

JLoader::register('ElementCreditHappylend', JPATH_ROOT . '/media/hyperpc/elements/credit/happylend/happylend.php');

/**
 * Class ElementCreditInstallment
 *
 * @since   2.0
 */
class ElementCreditInstallment extends ElementCreditHappylend
{

    const PARAM_KEY = '7seconds_inst';

    /**
     * Load element language.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function _loadLang()
    {
        Factory::getLanguage()->load('el_' . $this->getGroup() . '_' .  $this->getType(), $this->getPath(), null, true);
        Factory::getLanguage()->load('el_' . $this->getGroup() . '_happylend', $this->hyper['path']->get('elements:' . $this->_group . '/happylend'), null, true);
    }

    /**
     * Get parent manifest params
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getParentManifestParams()
    {
        $parentManifestPath = $this->hyper['path']->get('elements:' . $this->_group . '/happylend') . '/' . Manager::ELEMENT_MANIFEST_FILE;
        $parentManifest = include $parentManifestPath;
        $params = $parentManifest['params'] ?? [];
        return $params;
    }
}
