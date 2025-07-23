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

use HYPERPC\Joomla\Controller\ControllerLegacy;
use HYPERPC\XML\PriceList\PriceListsBuilderFactory;

/**
 * Class HyperPcControllerPrice_Lists
 *
 * @since 2.0
 */
class HyperPcControllerPrice_Lists extends ControllerLegacy
{

    /**
     * Hook on initialize controller.
     *
     * @param array $config
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->registerTask('update', 'update');
    }

    /**
     * Update all price-lists
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function update()
    {
        if (strtolower($this->hyper['input']->get('api-key')) !== strtolower(HP_REQUEST_API_KEY)) {
            $this->hyper['cms']->close('error');
        }

        $priceListsBuilder = (new PriceListsBuilderFactory())->createBuilder();
        $priceListsBuilder->buildPriceLists();

        $this->hyper['cms']->close('OK');
    }
}
