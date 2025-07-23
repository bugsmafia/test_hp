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
 * @desc        This class overrides the Joomla! Form filed standard class.
 */

namespace HYPERPC\Joomla\Form;

use HYPERPC\App;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Form\FormField as JFormField;

/**
 * Class FormField
 *
 * @package HYPERPC\Joomla\Form
 *
 * @since 2.0
 */
class FormField extends JFormField
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
     * Get form object.
     *
     * @return  \Joomla\CMS\Form\Form|null
     *
     * @since   2.0
     */
    public function getForm()
    {
        return $this->form;
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

    /**
     * Allow to override renderer include paths in child fields
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutPaths()
    {
        return [$this->hyper['path']->get('admin:layouts')];
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   2.0
     */
    protected function getRenderer($layoutId = 'default')
    {
        $renderer     = new FileLayout($layoutId);
        $defaultPaths = $renderer->getDefaultIncludePaths();

        $renderer->setDebug($this->isDebugEnabled());

        $layoutPaths = array_merge($this->getLayoutPaths(), $defaultPaths);

        if ($layoutPaths) {
            $renderer->setIncludePaths($layoutPaths);
        }

        return $renderer;
    }
}
