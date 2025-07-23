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
 * @desc        This class overrides the Joomla! Form filed standard class.
 */

namespace HYPERPC\Joomla\Form;

use HYPERPC\App;
use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class FormFieldList
 *
 * @package HYPERPC\Joomla\Form
 *
 * @since   2.0
 */
class FormFieldList extends ListField
{

    /**
     * Hold HYPERPC application object.
     *
     * @var     App
     *
     * @since   2.0
     */
    public $hyper;

    /**
     * FormField constructor.
     *
     * @param   \Joomla\CMS\Form\Form|null $form
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function __construct($form = null)
    {
        $this->hyper = App::getInstance();
        parent::__construct($form);
        $this->initialize();
    }

    /**
     * Initialize form field method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function initialize()
    {
    }
}
