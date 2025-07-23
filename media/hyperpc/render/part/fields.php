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

use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
use HYPERPC\Joomla\Model\Entity\Interfaces\PartMarker;

/**
 * @var string       $href
 * @var array        $fields
 * @var RenderHelper $this
 * @var PartMarker   $part
 */

$notebookGroups = (array) $this->hyper['params']->get('notebook_groups', []);
?>

<?php if (count($fields)) : ?>
    <div class="uk-modal-container">
        <table class="uk-table uk-table-divider tm-table-specs tm-table-specs--icons">
            <tbody>
                <tr class="hp-specs-group-head">
                    <td colspan="2">
                        <span class="uk-h3">
                            <?= Text::_('COM_HYPERPC_SPECS') ?>
                        </span>
                    </td>
                </tr>
                <?php
                foreach ($fields as $field) :
                    $fieldTitle = $field->title;
                    if (in_array((string) $part->group_id, $notebookGroups)) {
                        $fieldTitle = $this->hyper['helper']['html']->icon($field->getIcon(), ['class' => 'uk-margin-small-right']) . $fieldTitle;
                    }
                    ?>
                    <tr>
                        <th>
                            <?= $fieldTitle ?>
                        </th>
                        <td>
                            <?= $field->value ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif;
