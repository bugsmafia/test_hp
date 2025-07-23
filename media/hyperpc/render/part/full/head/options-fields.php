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

use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var RenderHelper   $this
 * @var PartMarker     $part
 * @var OptionMarker[] $options
 * @var OptionMarker   $optionDefault
 */

?>

<?php foreach ($options as $option) :
    if ($option->isDiscontinued()) {
        continue;
    }
    ?>
    <?php if (count((array) $option->get('fields'))) :
        $isSelected = ($optionDefault->id === $option->id);
        ?>
        <div class="jsOptionFields uk-display-inline-block" data-option-id="<?= $option->id ?>"<?= $isSelected ? '' : ' hidden aria-hidden="true"' ?>>
            <ul class="uk-list uk-list-divider uk-text-small uk-margin-remove">
                <?php foreach ((array) $option->get('fields') as $field) : ?>
                    <li>
                        <?= $this->hyper['helper']['html']->icon($field->getIcon(), ['class' => 'uk-margin-small-right']) ?>
                        <?= $field->title ?>: <?= $field->value ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endforeach;
