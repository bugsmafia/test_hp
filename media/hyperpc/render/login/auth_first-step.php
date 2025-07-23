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

use HYPERPC\Elements\Manager;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Elements\ElementAuth;

/**
 * @var RenderHelper $this
 */

/** @var ElementAuth[] $elements */
$elements = Manager::getInstance()->getByPosition(Manager::ELEMENT_TYPE_AUTH);

$elements = array_filter($elements, function ($element) {
    /** @var ElementAuth $element */
    return $element->isEnabled();
});

$countElements = count($elements);
?>

<?php if ($countElements > 1) : ?>
    <ul class="uk-subnav uk-subnav-pill" <?= $countElements <= 2 ? 'hidden' : '' ?> data-uk-switcher>
        <?php
        $class = 'uk-active';
        foreach ($elements as $key => $element) {
            echo '<li class="' . $class . '"><a href="#" aria-expanded="' . ((bool) $class ? 'true' : 'false') . '">' . $element->getConfig('name') . '</a></li>';
            $class = "";
        }
        ?>
    </ul>
<?php endif; ?>

<ul class="uk-switcher" style="touch-action: pan-y pinch-zoom;">
    <?php
    $class = 'uk-active';
    foreach ($elements as $element) {
        $renderParams = [
            'layout' => 'default',
            'countElements' => $countElements
        ];

        echo '<li class="' . $class . '">' . $element->render($renderParams) . '</li>';
        $class = "";
    }
    ?>
</ul>
