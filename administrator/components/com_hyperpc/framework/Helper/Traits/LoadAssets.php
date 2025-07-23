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

namespace HYPERPC\Helper\Traits;

use JBZoo\Utils\FS;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;

/**
 * Trait LoadAssets
 *
 * @package HYPERPC\Helper\Traits
 *
 * @since   2.0
 */
trait LoadAssets
{

    /**
     * Load script.
     *
     * @param   string  $src
     * @param   string  $initWidget
     * @param   bool    $mDate
     *
     * @return  string
     *
     * @since   2.0
     */
    public function loadScript($src, $initWidget, $mDate = true)
    {
        $file = $src;
        if (!FS::isFile($file)) {
            $file = FS::clean(JPATH_BASE . '/' . $file);
        }

        if (FS::isFile($file) && $mDate) {
            $src .= '?mtime=' . $this->_checkFile($file);
        }

        return "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let hasScript = false;
                        const pageScripts = document.getElementsByTagName('script');

                        for (let i = 0; i < pageScripts.length; i++) {
                            const element = pageScripts[i];
                            if (element.src && element.src.indexOf('{$src}') !== -1) {
                                hasScript = true;
                                break;
                            }
                        }

                        if (!hasScript) {
                            const widget = document.createElement('script');
                            widget.src   = '{$src}';
                            widget.async = true;

                            document.body.appendChild(widget);

                            widget.addEventListener('load', function(e) {
                                jQuery(function($){
                                    {$initWidget}
                                });
                            });
                        }
                    });
                </script>";
    }

    /**
     * Check file exists and return last modified.
     *
     * @param   string  $path
     *
     * @return  int|null
     *
     * @since   2.0
     */
    protected function _checkFile($path)
    {
        $path = Path::clean($path);
        if (File::exists($path) && filesize($path) > 5) {
            $mDate = substr(filemtime($path), -3);
            return $mDate;
        }

        return null;
    }
}
