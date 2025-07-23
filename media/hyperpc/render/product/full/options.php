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

use JBZoo\Image\Image;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\ProductMarker;

/**
 * @var RenderHelper    $this
 * @var ProductMarker   $product
 * @var array           $properties
 */

if (count($properties)) : ?>
    <div class="hp-product-head__options uk-margin uk-flex-last@s">
        <?php foreach ($properties as $part) :
            $group     = $part->getGroup();
            $options   = $part->getOptions(false, false);
            $group     = $part->getGroup();
            $groupName = ($group->params->get('product_option_select_name')) ? $group->params->get('product_option_select_name') : $group->title;
            ?>
            <?php if (count($options)) : ?>
                <div class="uk-h4 uk-margin-small-bottom">
                    <?= $groupName ?>
                </div>
                <div class="uk-grid uk-grid-small jsScrollableList" uk-grid data-media="1199px">
                    <?php foreach ($options as $option) :
                        $optionWrapperClass = 'hp-product-head__option';
                        if ($part->option instanceof OptionMarker && $option->id === $part->option->id) {
                            $optionWrapperClass .= ' hp-product-head__option--selected';
                        }

                        $propertyWrapperAttrs = [
                            'data'  => [
                                'option-id'  => $option->id,
                                'product-id' => $product->id,
                                'part-id'    => $option->part_id
                            ],
                            'class' => $optionWrapperClass
                        ];
                        ?>
                        <div>
                            <div <?= $this->hyper['helper']['html']->buildAttrs($propertyWrapperAttrs) ?>>
                                <span title="<?= $option->name ?>" class="uk-button uk-button-default uk-button-small">
                                    <?php
                                    $image = $option->getRender()->image(124);
                                    if (isset($image['thumb']) && $image['thumb'] instanceof Image) {
                                        echo '<img src="' . $image['thumb']->getUrl() . '" />';
                                    }
                                    ?>
                                    <br />
                                    <span class="uk-flex uk-flex-center">
                                        <span class="uk-text-truncate"><?= $option->getConfigurationName() ?></span>
                                        <a href="<?= $option->getViewUrl() ?>" uk-icon="icon: info; ratio: 0.75"
                                           class="jsLoadIframe uk-link-muted uk-flex-none uk-visible@s"
                                           title="<?= Text::_('COM_HYPERPC_CONFIGURATOR_PART_INFO') ?>"></a>
                                    </span>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif;
