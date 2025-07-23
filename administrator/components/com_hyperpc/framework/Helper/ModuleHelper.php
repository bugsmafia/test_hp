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
 */

namespace HYPERPC\Helper;

use JBZoo\Data\Data;
use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;

/**
 * Class ModuleHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ModuleHelper extends AppHelper
{

    /**
     * Load Joomla modules.
     *
     * @param   bool $published
     *
     * @return  array
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function load($published = false)
    {
        static $modules;

        if ($modules !== null) {
            return $modules;
        }

        $db = $this->hyper['db'];

        /** @var \JDatabaseQueryMysqli $query */
        $query = $db->getQuery(true)
            ->select([
                'm.id', 'm.title', 'm.module', 'm.position', 'm.content', 'm.showtitle', 'm.params', 'm.published'
            ])
            ->from($db->quoteName('#__modules', 'm'))
            ->where($db->quoteName('m.client_id') . ' = ' . $db->quote(0));

        if ($published) {
            $query->where($db->quoteName('m.published') . ' = ' . $db->quote(1));
        }

        $query
            ->order($db->quoteName('position'))
            ->order($db->quoteName('ordering'));

        $modules = $db->setQuery($query)->loadObjectList('id');
        foreach ((array) $modules as $module) {
            $file             = $module->module;
            $custom           = (string) substr($file, 0, 4) === 'mod_' ? 0 : 1;
            $module->user     = $custom;
            $module->name     = $custom ? $module->title : substr($file, 4);
            $module->style    = null;
            $module->position = Str::low($module->position);
        }

        return $modules;
    }

    /**
     * Render module part.
     *
     * @param   string  $module
     * @param   string  $partial
     * @param   array   $args
     *
     * @return  null|string
     *
     * @since   2.0
     */
    public function partial($module, $partial, array $args = [])
    {
        $path = FS::clean($module . '/tmpl/_' . $partial);
        return $this->hyper['helper']['render']->render($path, $args, 'modules');
    }

    /**
     * Find module by id.
     *
     * @param   int $moduleId
     *
     * @return  Data
     *
     * @since   2.0
     */
    public function findById($moduleId)
    {
        $db = $this->hyper['db'];

        /** @var \JDatabaseQueryMysqli $query */
        $query = $db->getQuery(true)
            ->select(['m.*'])
            ->from($db->quoteName('#__modules', 'm'))
            ->where([
                $db->quoteName('m.client_id') . ' = ' . $db->quote(0),
                $db->quoteName('m.published') . ' = ' . $db->quote(1),
                $db->quoteName('m.id') . ' = ' . $db->quote($moduleId)
            ]);

        return new Data($db->setQuery($query)->loadObject());
    }

    /**
     * Render Joomla! module by id.
     *
     * @param   int|string $moduleId
     *
     * @return  null
     *
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    public function renderById($moduleId)
    {
        $db = $this->hyper['db'];

        /** @var \JDatabaseQueryMysqli $query */
        $query = $db->getQuery(true)
            ->select([
                'm.id', 'm.title', 'm.module', 'm.position', 'm.content', 'm.showtitle', 'm.params', 'm.published'
            ])
            ->from($db->quoteName('#__modules', 'm'))
            ->where([
                $db->quoteName('m.client_id')   . ' = ' . $db->quote(0),
                $db->quoteName('m.published')   . ' = ' . $db->quote(1),
                $db->quoteName('m.id')          . ' = ' . $db->quote($moduleId)
            ]);

        $module = $db->setQuery($query)->loadObject();
        if ($module !== null) {
            if ($module->published) {
                $params = new JSON($module->params);
                return JModuleHelper::renderModule($module, $params->getArrayCopy());
            }
        }

        return null;
    }
}
