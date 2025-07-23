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

namespace HYPERPC\Joomla\Model;

use HYPERPC\App;
use Joomla\CMS\MVC\Model\FormModel;

/**
 * Class ModelForm
 *
 * @package HYPERPC\Joomla\Model
 *
 * @since   2.0
 */
abstract class ModelForm extends FormModel
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     *
     * @deprecated  Use only $this->hyper
     */
    public $app;

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * ModelItem constructor.
     *
     * @param   array $config
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->app = $this->hyper = App::getInstance();
        $this->setDbo($this->hyper['db']);
        $this->initialize($config);
    }

    /**
     * Returns a Model object, always creating it.
     *
     * @param   string $type The model type to instantiate
     * @param   string $prefix Prefix for the model class name. Optional.
     * @param   array $config Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|boolean
     *
     * @since   2.0
     */
    public static function getInstance($type, $prefix = HP_MODEL_CLASS_PREFIX, $config = [])
    {
        return parent::getInstance($type, $prefix, $config);
    }

    /**
     * Initialize model hook method.
     *
     * @param   array $config
     * @return  void
     *
     * @since   2.0
     */
    public function initialize(array $config)
    {
    }
}
