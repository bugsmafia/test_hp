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
 * @var         RenderHelper    $this
 * @var         JSON            $amoContact
 */

use HYPERPC\Data\JSON;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\RenderHelper;
?>

<?= Text::sprintf('COM_HYPERPC_COMMERCIAL_PROPOSAL_WITH_USER_RESPECT', '') ?>
<br />
<br />
<b><?= $amoContact->get('name') ?></b>
<br />
<?php
$contactGroupName = $amoContact->find('_embedded.groups.0.name');
if ($contactGroupName) {
    echo $contactGroupName . '<br />';
}
?>
<b>Email</b>
/
<?php echo $amoContact->get('email');
