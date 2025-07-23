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

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\View\ViewLegacy;
use HYPERPC\Helper\MoyskladProductHelper;

/**
 * Class HyperPcViewMoysklad_Products
 *
 * @property    string                  $context
 * @property    array                   $groups
 * @property    string                  $layout
 * @property    string                  $game
 * @property    bool                    $showFps
 * @property    string                  $instock
 * @property    array                   $options
 * @property    array                   $products
 * @property    int                     $total
 * @property    array                   $ajaxLoadArgs
 * @property    MoyskladProductHelper   $helper
 *
 * @since       2.0
 */
class HyperPcViewMoysklad_Products extends ViewLegacy
{

    const FIND_TYPE_VALUE = 'value';

    /**
     * Hook on initialize view.
     *
     * @param   array $config
     *
     * @return  void
     *
     * @since   2.0
     *
     * @SuppressWarnings("unused")
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->groups  = $this->hyper['helper']['productFolder']->findAll();
        $this->options = $this->hyper['helper']['moyskladVariant']->getVariants();
        $this->helper  = $this->hyper['helper']['moyskladProduct'];
    }

    /**
     * Default display view action.
     *
     * @param   null|string $tpl
     *
     * @return  mixed
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        $ids             = $this->hyper['input']->get('ids', [], 'array');
        $tags            = $this->hyper['input']->get('tags', [], 'array');
        $game            = $this->hyper['input']->get('game', [], 'array');
        $config          = $this->hyper['input']->get('config', [], 'array');
        $layout          = $this->hyper['input']->get('layout', 'default', 'string');
        $instock         = $this->hyper['input']->get('instock', 'except', 'string');
        $showFps         = $this->hyper['input']->get('showFps', false, 'bool');
        $platform        = $this->hyper['input']->get('platform');
        $priceRange      = $this->hyper['input']->get('price_range');
        $productOrder    = $this->hyper['input']->get('order', '', 'string');
        $loadUnavailable = $this->hyper['input']->get('load_unavailable', false, 'bool');

        $limit = $this->hyper['input']->get('limit', 0, 'int');
        $offset = $this->hyper['input']->get('offset', 0, 'int');

        if (!$productOrder) {
            $productOrder = $this->hyper['params']->get('product_order', 'a.id ASC');
        }

        try {
            $this->products = $this->helper->findByConditions([
                'type'             => 'product',
                'ids'              => array_diff($ids, array('')),
                'tags'             => array_diff($tags, array('')),
                'game'             => array_diff($game, array('')),
                'config'           => array_diff($config, array('')),
                'priceRange'       => $priceRange,
                'platform'         => $platform,
                'order'            => $productOrder,
                'instock'          => $instock,
                'loadUnavailable'  => $loadUnavailable
            ], $limit, $offset);
        } catch (UnexpectedValueException $e) {
            $this->hyper['cms']->enqueueMessage('Can\'t render positions via snippet: ' . $e->getMessage(), 'warning');
        }

        if (!count($this->products)) {
            $this->hyper['cms']->enqueueMessage(Text::_('COM_HYPERPC_PRODUCTS_NOT_FOUND'), 'warning');
        }

        $this->layout = $layout;
        $this->instock = $instock;

        $this->showFps = $showFps;
        $this->game = '';
        if (!empty($game) && !empty($game[0])) {
            $this->game = $game[0];
        }

        if ($limit && count($this->products) === $limit) {
            $this->ajaxLoadArgs = [
                'limit'           => $limit,
                'offset'          => $offset + $limit,
                'ids'             => !empty($ids) ? join(',', $ids) : null,
                'tags'            => !empty($tags) ? join(',', $tags) : null,
                'game'            => !empty($game) ? join(',', $game) : null,
                'type'            => 'product',
                'parts'           => !empty($config) ? join(',', $config) : null,
                'order'           => $productOrder,
                'layout'          => $layout,
                'instock'         => $instock,
                'showFps'         => $showFps,
                'platform'        => $platform,
                'price-range'     => $priceRange ?: null,
                'loadUnavailable' => (int) $loadUnavailable,
            ];
        }

        parent::display($tpl);
    }
}
