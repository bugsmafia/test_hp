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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Field;
use HYPERPC\Joomla\Model\Entity\MoyskladService;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;
use HYPERPC\Joomla\Model\Entity\Interfaces\OptionMarker;

/**
 * @var     RenderHelper                $this
 * @var     Field[]                     $properties
 * @var     PartMarker|MoyskladService  $part
 * @var     OptionMarker|null           $option
 */

$groupHeadBuffer = (object) [
    'property' => null,
    'state'    => false
  ];
?>

<table class="uk-table uk-table-divider tm-table-specs">
    <?php foreach ($properties as $property) :
        if ($option instanceof OptionMarker && $option->id) {
            $property->value = $option->params->find('options.' . $property->name) ?? $property->value;
        }

        if ((empty($property->value) && $property->type !== 'hpseparator') || $property->value === 'none') {
            continue;
        }

        $value = $property->getValue();
        if ($property->type === 'url') {
            $linkUri = Uri::getInstance($value);

            if (empty($linkUri->getHost())) { // probably relative path
                if (strpos($linkUri->getPath(), '/') !== 0 || strlen($linkUri->getPath()) < 1) {
                    continue; // bad path
                }
            } else {
                $currentUri = Uri::getInstance();

                if ($currentUri->getHost() !== $linkUri->getHost()) {
                    /** @todo external resources */
                    continue;
                }
            }

            $path = Path::clean(JPATH_ROOT . $linkUri->getPath());

            if (!File::exists($path)) {
                continue;
            }

            $base     = log(filesize($path)) / log(1024);
            $suffixes = ['', 'k', 'M', 'G', 'T'];
            $fileSize = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)] . 'B';

            $ext = File::getExt($path);
            $fileName = basename($path, ".$ext");

            $value =
                '<a href="' . $value . '" class="uk-link-reset uk-display-inline-block" target="_blank">' .
                    '<span class="uk-margin-small-right uk-float-left uk-icon uk-text-primary" uk-icon="icon: download; ratio: 1.5"></span>' .
                    $fileName .
                    '<span class="uk-text-muted uk-text-small uk-display-block">(' . strtoupper($ext) . ', ' . $fileSize . ')</span>' .
                '</a>';
        }

        if ($property->type === 'hpseparator') {
            $groupHeadBuffer->property = $property;
            $groupHeadBuffer->state    = false;
            continue;
        }
        ?>
        <?php if ($groupHeadBuffer->state === false && $groupHeadBuffer->property !== null) :
            $groupHeadBuffer->state = true; ?>
            <tr class="tm-table-specs__group-head">
                <th colspan="2"><span class="uk-h3"><?= $groupHeadBuffer->property->label ?></span></th>
            </tr>
        <?php endif; ?>
        <tr>
            <th class="tm-table-specs__property-name"><?= $property->label ?></th>
            <td>
                <?= $value ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
