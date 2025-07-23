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

namespace HYPERPC\Helper;

use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use Joomla\CMS\Factory;

/**
 * Class RenderHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class RenderHelper extends AppHelper
{

    const DEFAULT_GROUP = 'renderer';

    /**
     * Render template.
     *
     * @param   string $name
     * @param   array $args
     * @param   string $group
     * @param   bool|string $cached
     * @return  null|string
     *
     * @since    2.0
     */
    public function render($name, array $args = [], $group = self::DEFAULT_GROUP, $cached = false)
    {
        $cacheGroup = 'hp_renders';
        if (is_string($cached) && $cached !== '') {
            $cacheGroup = Str::low($cached);
            $cached = true;
        }

        $caching = (int) Factory::getConfig()->get('caching', 0);

        if ($cached === true && $caching > 0) {
            $cache     = Factory::getCache($cacheGroup, null);
            $argsCache = $this->_serializeArgs($args);
            $cacheKey  = md5(implode('///', [$name, $argsCache, $group, $cached]));

            if (!$cache->get($cacheKey, $cacheGroup)) {
                $cache->store($this->_render($name, $args, $group), $cacheKey, $cacheGroup);
            }

            return $cache->get($cacheKey, $cacheGroup);
        }

        return $this->_render($name, $args, $group);
    }

    /**
     * Render layout.
     *
     * @param   string $name
     * @param   array $args
     * @param   string $group
     * @return  null|string
     *
     * @since   2.0
     */
    protected function _render($name, array $args = [], $group = self::DEFAULT_GROUP)
    {
        $ext = FS::ext($name);
        if (empty($ext)) {
            $name .= '.php';
        }

        $args['render'] = $this;

        $file = $this->hyper['path']->get($group . ':' . $name);
        if ($file !== null) {
            extract($args);
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include $file;
            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        return null;
    }

    /**
     * Serialize args for cache id.
     *
     * @param   array $args
     * @return  string
     *
     * @since   2.0
     */
    protected function _serializeArgs(array $args = [])
    {
        ksort($args);
        $argsCache = [];

        foreach ($args as $argKey => $arg) {
            if (is_bool($arg)) {
                $argsCache[] = $argKey . '-' . ((string) $arg);
            } elseif (is_string($arg) || is_numeric($arg)) {
                $argsCache[] = "{$argKey}-{$arg}";
            } elseif (is_array($arg)) {
                $argsCache[] = $argKey . '-' . $this->_serializeArgs($arg);
            } elseif (is_object($arg) && property_exists($arg, 'id')) {
                $argsCache[] = get_class($arg) . '-' . $arg->id;
            } else {
                $argsCache[] = $argKey;
            }
        }

        return implode('&', $argsCache);
    }
}
