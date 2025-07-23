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

namespace HYPERPC\Joomla\WebAsset;

use HYPERPC\App;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

class ProductTeaserAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
    public function onAttachCallback(Document $doc): void
    {
        Text::script('COM_HYPERPC_SPECIFICATION');
        Text::script('COM_HYPERPC_PRODUCT_SHOW_FULL_SPECIFICATION');

        $hp = App::getInstance();
        $hp['helper']['game']->setJsGamesData();

        $resolutions = $hp['helper']['fps']->getResolutions();
        foreach ($resolutions as $resolution) :
            Text::script('COM_HYPERPC_FPS_RESOLUTION_' . strtoupper($resolution));
        endforeach;

        Text::script('COM_HYPERPC_DETAILS');
        Text::script('COM_HYPERPC_FPS_DISCLAIMER');
        Text::script('COM_HYPERPC_FPS_TITLE_PRODUCT');
        Text::script('COM_HYPERPC_PRODUCT_TEASER_FPS_HEADING');
        Text::script('COM_HYPERPC_PRODUCT_TEASER_FPS_SUB');
    }
}
