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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\CategoryMarker;

/**
 * @var     RenderHelper $this
 * @var     ProductMarker $product
 * @var     CategoryMarker[] $groups
 * @var     OptionMarker[] $options
 * @var     MoyskladService[] $parts
 */
?>

<?php foreach ($parts as $groupId => $groupParts) :
    $group = $groups[$groupId];
    ?>
    <li class="group-<?= $group->id ?>">

        <div class="hp-product-teaser__part">
            <div>
                <span class="hp-product-teaser__part-icon" uk-icon="hp-<?= $group->alias ?>"></span>
                <?= $group->title ?>:
            </div>
            <div class="hp-product-teaser__part-name">
                <?php
                foreach ($groupParts as $part) :
                    $groupIds = $part->prepareGroups($groups);
                    ?>
                    <?php if (in_array($group->id, $groupIds)) : ?>
                        <?php if ($part->get('quantity', 1, 'int') > 1) : ?>
                            <?= $part->get('quantity', 1) . ' x' ?>
                        <?php endif; ?>

                        <?php
                        $itemHref = $part->getViewUrl();
                        $itemName = $part->getConfiguratorName($product->id);
                        $isReload = false;
                        if (!$part->isReloadContentForProduct($product->id)) {
                            $partOptions = $this->hyper['helper']['moyskladVariant']->getPartVariants($part->id, $options);
                            if (count($partOptions) > 0) {
                                $option = $product->getDefaultPartOption($part, $partOptions);
                                if ($option->id !== null) {
                                    $itemHref = $option->getViewUrl();
                                    $itemName .= '&nbsp;' . $option->getConfigurationName();
                                }
                            }
                        } else {
                            $isReload = true;
                        }
                        ?>

                        <?php if ($isReload || !$part->isPublished()) : ?>
                            <?= $itemName ?>
                        <?php else : ?>
                            <a href="<?= $itemHref ?>" class="jsLoadIframe uk-link-reset">
                                <?= $itemName ?>
                            </a>
                        <?php endif; ?>
                        <br>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

    </li>
<?php endforeach;
