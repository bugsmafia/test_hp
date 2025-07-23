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
 * @author      Roman Evsyukov
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\MoyskladStoreItem;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;
use HYPERPC\Joomla\Model\Entity\MoyskladStore;
use HYPERPC\Joomla\Model\Entity\MoyskladVariant;

/**
 * @var array $displayData
 */

$app   = App::getInstance();
$data  = new JSON($displayData);

/** @var MoyskladPart[] */
$items = $data->get('items');

/** @var \JFormFieldMoyskladStoreItems $field */
$field = $data->get('field');

/** @var MoyskladStore[] $stores */
$stores = (array) $data->get('stores');
?>

<?php if (count($items) > 1) : ?>
    <table class="table table-striped">
        <tbody>
            <tr>
                <td class="small"><?= Text::_('COM_HYPERPC_OPTION') ?></td>
                <?php foreach ((array) $data->get('stores') as $store) : ?>
                    <td class="small"><?= $store->name ?></td>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($items as $item) : ?>
                <tr>
                    <td class="small"><?= $item->name ?></td>
                    <?php foreach ((array) $data->get('stores') as $store) : ?>
                        <td>
                            <?php
                            $optionId = 0;
                            if (isset($item->option) && $item->option instanceof MoyskladVariant) {
                                $optionId = $item->option->id;
                            }

                            /** @var MoyskladStoreItem $storeItem */
                            $storeItem = $app['helper']['moyskladStock']->getItem($store->id, $item->id, $optionId);

                            $balance = (int) $storeItem->balance;
                            ?>
                            <?= $balance ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else :
    $item = $items[array_key_first($items)];
    ?>
    <?php foreach ((array) $data->get('stores') as $store) :
        $optionId = 0;
        if (isset($item->option) && $item->option instanceof MoyskladVariant) {
            $optionId = $item->option->id;
        }

        /** @var MoyskladStoreItem $storeItem */
        $storeItem = $app['helper']['moyskladStock']->getItem($store->id, $item->id, $optionId);

        $balance = (int) $storeItem->balance;
        ?>
        <div>
            <div class="small">
                <?= $store->name ?>
            </div>
            <div>
                <input type="number" class="form-control readonly" readonly value="<?= $balance ?>"/>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif;
