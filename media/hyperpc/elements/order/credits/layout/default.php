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
 *
 * @var         array $elements
 * @var         \ElementOrderCredits $this
 * @var         \HYPERPC\Elements\ElementCredit $element
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

$value   = $this->getValue();
$default = $this->getConfig('default', 'happylend');
?>
<?php if (count($elements)) : ?>
    <div id="field-<?= $this->getIdentifier() ?>" class="uk-margin">
        <?php if (!empty($this->getConfig('name'))) : ?>
            <div class="uk-h4 uk-margin-small">
                <?= $this->getConfig('name') ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($this->getConfig('description'))) : ?>
            <div class="uk-margin-bottom uk-text-muted tm-text-italic">
                <?= $this->getConfig('description') ?>
            </div>
        <?php endif; ?>
        <div class="uk-margin-small-bottom" uk-margin="margin: uk-margin-top">
            <table class="uk-table uk-table-divider uk-table-hover">
                <tbody>
                <?php foreach ($elements as $element) :
                    $attrs = [
                        'type'  => 'radio',
                        'class' => 'uk-radio',
                        'value' => $element->getType(),
                        'name'  => $this->getControlName('value')
                    ];

                    $image = $element->getPath('assets/img/label.png', true);

                    if ($element->getType() === $default) {
                        $attrs['checked'] = 'checked';
                    }
                    ?>
                    <tr>
                        <td class="uk-padding-remove">
                            <label class="uk-display-block uk-padding-small">
                                <span class="uk-flex uk-flex-middle uk-margin-small-bottom">
                                    <span class="uk-flex-none uk-margin-small-right">
                                        <input <?= $this->hyper['helper']['html']->buildAttrs($attrs) ?>>
                                    </span>
                                    <span>
                                        <span class="uk-h5"><?= $element->getConfig('name') ?></span>
                                    </span>
                                </span>
                                <span class="uk-display-block uk-text-muted" style="margin-inline-start: 30px">
                                    <?= HTMLHelper::_('content.prepare', $element->getConfig('description')) ?>
                                </span>
                            </label>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif;
