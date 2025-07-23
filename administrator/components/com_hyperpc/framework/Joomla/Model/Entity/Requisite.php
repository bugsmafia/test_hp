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

namespace HYPERPC\Joomla\Model\Entity;

/**
 * Class Requisite
 *
 * @package HYPERPC\Joomla\Model\Entity
 * @method \HYPERPC\Render\Requisite render()
 *
 * @since 2.0
 */
class Requisite extends Entity
{

    /**
     * Company short name.
     *
     * @var string
     *
     * @since 2.0
     */
    public $name;

    /**
     * Company legal address.
     *
     * @var string
     *
     * @since 2.0
     */
    public $legal_address;

    /**
     * Company phones.
     *
     * @var string
     *
     * @since 2.0
     */
    public $phones;

    /**
     * Instance of requisites.
     *
     * @return Requisite
     *
     * @since 2.0
     */
    public static function getInstance()
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Initialize entity.
     *
     * @return void
     *
     * @since 2.0
     */
    public function initialize()
    {
        parent::initialize();

        $properties = array_keys(get_class_vars($this));
        foreach ($properties as $property) {
            $value = $this->hyper['params']->get('company_' . $property);
            if ($value !== null) {
                $this->set($property, $value);
            }
        }
    }

    /**
     * Get site view category link.
     *
     * @param array $query
     * @return null
     *
     * @since 2.0
     */
    public function getViewUrl(array $query= [])
    {
        return null;
    }
}
