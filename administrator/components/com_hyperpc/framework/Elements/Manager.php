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

namespace HYPERPC\Elements;

use HYPERPC\App;
use JBZoo\Utils\FS;
use JBZoo\Utils\Str;
use HYPERPC\Data\JSON;
use Cake\Utility\Inflector;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Cake\Utility\Text as CakeText;

/**
 * Class Manager
 *
 * @package     HYPERPC\Elements
 *
 * @since       2.0
 */
class Manager
{
    const ELEMENT_MANIFEST_FILE              = 'manifest.php';

    const ELEMENT_TYPE_CORE                  = 'core';
    const ELEMENT_TYPE_MARKETPLACE           = 'marketplace';
    const ELEMENT_TYPE_AUTH                  = 'auth';
    const ELEMENT_TYPE_CREDIT                = 'credit';
    const ELEMENT_TYPE_CONFIGURATION_ACTIONS = 'configuration_actions';
    const ELEMENT_TYPE_CREDIT_CALCULATE      = 'credit_calculate';
    const ELEMENT_TYPE_ORDER                 = 'order';
    const ELEMENT_TYPE_ORDER_HOOK            = 'order_hook';
    const ELEMENT_TYPE_PAYMENT               = 'payment';
    const ELEMENT_TYPE_PRICE_LIST            = 'price_list';

    const ELEMENT_POS_CONFIGURATION_AFTER_AUTH_SAVE     = 'configurator_after_auth_save';
    const ELEMENT_POS_CONFIGURATION_AFTER_NATIVE_SAVE   = 'configurator_after_native_save';
    const ELEMENT_POS_CONFIGURATION_AFTER_SAVE          = 'configurator_after_save';
    const ELEMENT_POS_ORDER_AFTER_SAVE                  = 'order_after_save';
    const ELEMENT_POS_PRODUCT_SERVICE                   = 'product_service_elements';

    /**
     * HYPERPC Application.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * Hold instance.
     *
     * @var     Manager
     *
     * @since   2.0
     */
    protected static $__instance;

    /**
     * Create element.
     *
     * @param   string $type
     * @param   string $group
     * @param   array  $config
     *
     * @return  Element|null
     *
     * @throws \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function create($type, $group, array $config = [])
    {
        $eType  = Inflector::camelize($type);
        $eGroup = Inflector::camelize($group);

        $elementClass = 'Element' . $eGroup . $eType;

        $classPath = $this->hyper['path']->get("elements:{$group}/{$type}/{$type}.php");
        if ($classPath) {
            /** @noinspection PhpIncludeInspection */
            require_once $classPath;
        }

        if (!class_exists($elementClass)) {
            return null;
        }

        /** @var Element $element */
        $element     = new $elementClass($type, $group);
        $keyName     = 'HYPER_ELEMENT_' . Str::up($group) . '_' . Str::up($type) . '_NAME';
        $elementName = Text::_($keyName) !== $keyName ? Text::_($keyName) : '';

        $config = array_merge([
            'type'       => $type,
            'group'      => $group,
            'name'       => $elementName,
            'identifier' => CakeText::uuid()
        ], (array) $config);

        if ($element->isCore()) {
            $config['identifier'] = Str::low($element->getType());
        }

        $element->setConfig($config);
        $element->onAfterCreate();

        return $element;
    }

    /**
     * Get elements by position.
     *
     * @param   string $position
     *
     * @return  Element[]
     *
     * @since   2.0
     */
    public function getByPosition($position)
    {
        $elements        = [];
        $_elementsConfig = (array) $this->hyper['params']->get($position);

        foreach ($_elementsConfig as $identifier => $config) {
            $config = new JSON((array) $config);
            $config->set('identifier', $identifier);

            try {
                $element = $this->create(
                    $config->get('type'),
                    $config->get('group'),
                    $config->getArrayCopy()
                );
            } catch (\Throwable $th) {
            }

            if ($element instanceof Element) {
                $elements[$identifier] = $element;
            }
        }

        return $elements;
    }

    /**
     * Get element instance.
     *
     * @param   string $position
     * @param   string $name
     *
     * @return  null|Element
     *
     * @since   2.0
     */
    public function getElement($position, $name)
    {
        return (new JSON((array) $this->getByPosition($position)))->find($name);
    }

    /**
     * Get all elements from group.
     *
     * @param   string  $groups
     * @param   bool    $getHidden
     *
     * @return  array
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    public function getElementsByGroups($groups, $getHidden = false)
    {
        $elements = [];
        $groups   = (array) $groups;

        foreach ($groups as $group) {
            $groupPath = $this->hyper['path']->get("elements:{$group}");
            $elements[$group] = [];
            if ($groupPath) {
                foreach ((array) Folder::folders($groupPath) as $type) {
                    $groupPath = $this->hyper['path']->get("elements:{$group}/{$type}/{$type}.php");
                    if (FS::isFile($groupPath)) {
                        $element = $this->create($type, $group);
                        if ($element && !$element->isHidden() || $getHidden) {
                            $elements[$group][$type] = $element;
                        }
                    }
                }
            }

            uasort($elements[$group], [$this, '_sortGroup']);
        }

        $elements = array_filter($elements);

        return $elements;
    }

    /**
     * Get manager instance.
     *
     * @return  Manager
     *
     * @throws \Exception
     *
     * @since   2.0
     */
    public static function getInstance()
    {
        if (self::$__instance === null) {
            self::$__instance = new self();
        }

        return self::$__instance;
    }

    /**
     * User compare function for element grouping.
     *
     * @param   Element $element1
     * @param   Element $element2
     *
     * @return  int
     *
     * @throws  \JBZoo\Utils\Exception
     *
     * @since   2.0
     */
    protected function _sortGroup($element1, $element2)
    {
        $core1 = $element1->isCore();
        $core2 = $element2->isCore();
        $name1 = $element1->getMetaData('name');
        $name2 = $element2->getMetaData('name');

        if ($core1 == $core2) {
            return strcasecmp($name1, $name2);
        }

        return ($core1 && !$core2) ? -1 : 1;
    }

    /**
     * Manager constructor.
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    private function __construct()
    {
        $this->hyper = App::getInstance();
    }
}
