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

use HYPERPC\Joomla\Model\Entity\Entity;

/**
 * Class ObjectHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class ObjectHelper extends AppHelper
{

    /**
     * Create entity object.
     *
     * @param   array   $data
     * @param   string  $className
     *
     * @return  null|object
     *
     * @since   2.0
     */
    public function create(array $data, $className)
    {
        if (class_exists($className)) {
            return new $className($data);
        }

        return null;
    }

    /**
     * Create entities object list.
     *
     * @param   array       $data
     * @param   mixed       $className
     * @param   string|null $key
     *
     * @return  array
     *
     * @since   2.0
     */
    public function createList(array $data, $className, $key = null)
    {
        $return = [];
        foreach ($data as $properties) {
            if (class_exists($className)) {
                /** @var Entity $entity */
                $entity = new $className($properties);
                if ($entity->get($key, null) !== null) {
                    $return[$entity->get($key)] = $entity;
                } else {
                    $return[] = $entity;
                }
            }
        }

        return $return;
    }

    /**
     * Get current class entity.
     *
     * @param   string $className
     * @param   string $defaultClass
     *
     * @return  string
     *
     * @since   2.0
     */
    public function getEntityClass($className, $defaultClass = 'stdClass')
    {
        //  Old style.
        if (class_exists('\HYPERPC\Joomla\Model\Entity\\' . $className)) {
            return '\HYPERPC\Joomla\Model\Entity\\' . $className;
        }

        if (class_exists('\\HYPERPC\\ORM\\Entity\\' . $className)) {
            return '\\HYPERPC\\ORM\\Entity\\' . $className;
        }

        return $defaultClass;
    }
}
