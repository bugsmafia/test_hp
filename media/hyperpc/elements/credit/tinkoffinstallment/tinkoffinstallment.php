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

use Joomla\CMS\Factory;
use HYPERPC\Elements\Manager;

JLoader::register('ElementCreditTinkoff', JPATH_ROOT . '/media/hyperpc/elements/credit/tinkoff/tinkoff.php');

/**
 * Class ElementCreditTinkoffInstallment
 *
 * @since   2.0
 */
class ElementCreditTinkoffInstallment extends ElementCreditTinkoff
{
    const PARAM_KEY = 'tinkoffinstallment';

    /**
     * @var     string
     *
     * @since   2.0
     */
    protected $_orderPrefix = 'Р';

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
        Factory::getLanguage()->load('el_' . $this->getGroup() . '_tinkoff', $this->hyper['path']->get('elements:' . $this->_group . '/tinkoff'), null, true);
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
        $parentManifestPath = $this->hyper['path']->get('elements:' . $this->_group . '/tinkoff') . '/' . Manager::ELEMENT_MANIFEST_FILE;
        $parentManifest = include $parentManifestPath;
        $params = $parentManifest['params'] ?? [];
        return $params;
    }
}
