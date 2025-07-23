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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use HYPERPC\Helper\RenderHelper;

/**
 * @var string $logo
 * @var RenderHelper $this
 */
?>
<?php if ($logo && File::exists(Path::clean(JPATH_ROOT . '/' . $logo))) :
    $fileName  = pathinfo($logo, PATHINFO_FILENAME);
    $logoTitle = Text::_('COM_HYPERPC_LOGO_' . strtoupper($fileName));
    ?>
    <span>
        <?php
        echo $this->hyper['helper']['html']->image($logo, [
            'alt'        => '',
            'title'      => $logoTitle,
            'uk-tooltip' => 'pos: right'
        ]);
        ?>
    </span>
<?php endif;
