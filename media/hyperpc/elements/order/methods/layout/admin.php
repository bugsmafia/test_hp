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
 * @var         \ElementOrderMethods $this
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$company = $this->getConfig('data.company');
?>
<dt><?= $this->getConfig('name') ?></dt>
<dd><?= $this->getMethodTitle() ?></dd>
<?php if ($company) : ?>
    <dt><?= Text::_('HYPER_ELEMENT_ORDER_METHODS_COMPANY_LABEL') ?></dt>
    <dd><?= $company ?></dd>
<?php endif;
