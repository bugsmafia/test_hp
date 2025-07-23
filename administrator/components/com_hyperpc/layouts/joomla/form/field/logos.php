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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 * @var         array $displayData
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use JBZoo\Utils\FS;
use JBZoo\Data\Data;

$app       = App::getInstance();
$data      = new Data($displayData);
$fieldName = (string) $data->get('name');

$app['helper']['assets']
    ->js('js:widget/fields/logos.js')
    ->widget('.hp-logos-field', 'HyperPC.FieldLogos');
?>
<div class="hp-logos-field">
    <?php foreach ((array) $data->get('images', []) as $i => $image) :
        $url          = $app['path']->url($image, false);
        $fileName     = FS::filename($image);
        $ext          = FS::ext($image);
        $value        = $url;
        $isChecked    = (in_array($value, (array) $data->get('value', []))) ? true : false;
        $checkedClass = ($isChecked === true) ? ' checked' : '';
        ?>
        <label class="hp-logos-field-label thumbnail hasTooltip<?= $checkedClass ?>"
               title="<?= str_replace('_', ' ', $fileName) ?>">
            <input type="checkbox" value="<?= $value ?>" class="hp-logos-field-input<?= $checkedClass ?>"
                   name="<?= $fieldName ?>[<?= $i ?>]"<?= ($isChecked === true) ? 'checked="checked"' : '' ?>>
            <img src="<?= $url ?>" class="img-rounded" />
        </label>
    <?php endforeach; ?>
</div>
