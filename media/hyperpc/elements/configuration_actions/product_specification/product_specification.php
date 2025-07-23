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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Elements\ElementConfiguratorActions;
use HYPERPC\Joomla\Model\Entity\SaveConfiguration;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * Class ElementConfigurationActionsProductSpecification
 *
 * @property    SaveConfiguration   $_configuration
 * @property    ProductMarker       $_product
 *
 * @since       2.0
 */
class ElementConfigurationActionsProductSpecification extends ElementConfiguratorActions
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
            ->js('js:widget/site/product-specification.js')
            ->widget('body', 'HyperPC.ProductSpecification');
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
        return implode([
            '<a data-itemkey="' . $this->getItemKey() . '" role="button" class="jsSpecificationButton">',
            $this->getAccountActionTile(),
            '</a>'
        ]);
    }
}
