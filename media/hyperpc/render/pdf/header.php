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
 * @var         string $heading
 * @var         \HYPERPC\Helper\RenderHelper $this
 */

use Joomla\CMS\Filesystem\File;

$heading = isset($heading) ? mb_strtoupper($heading) : '';

$siteContext = $this->hyper['params']->get('site_context', 'hyperpc');
$logoImgSrc = '';
switch ($siteContext) {
    case 'hyperpc':
        $logoImgSrc = JPATH_ROOT . '/media/hyperpc/img/pdf/hyperpc-logo-pdf-black.png';
        break;
    case 'epix':
        $logoImgSrc = JPATH_ROOT . '/media/hyperpc/img/pdf/epix-logo-pdf.png';
        break;
}
?>

<div id="pdf-header">
    <table>
        <tbody>
            <tr>
                <td><?= $heading ?></td>
                <td style="text-align: right;">
                    <?php if (File::exists($logoImgSrc)) : ?>
                        <img src="<?= $logoImgSrc ?>" alt="" style="height: 0.4cm">
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
