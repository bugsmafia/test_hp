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
 * 
 * @var         string $type
 * @var         Entity $item
 *
 */

use HYPERPC\Helper\CartHelper;
use HYPERPC\Joomla\Model\Entity\Entity;

defined('_JEXEC') or die('Restricted access');

$imageWidth = 120;
$imageSrc = $this->hyper['helper']['cart']->getItemImage($item, $imageWidth, 0);

$urlParams = [];
if ($type === CartHelper::TYPE_PART) {
    $urlParams['opt'] = true;
}
$href = $item->getViewUrl($urlParams);
?>
<a href="<?= $href ?>" title="<?= $item->name ?>" target="_blank">
    <img src="<?= $imageSrc ?>" alt="<?= $item->name ?>" width="<?= $imageWidth ?>" />
</a>
