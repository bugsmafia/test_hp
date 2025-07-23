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

defined('_JEXEC') or die('Restricted access');

use HYPERPC\Data\JSON;
use Joomla\CMS\Form\Form;
use HYPERPC\Elements\Manager;
use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Form\FormField;

/**
 * Class JFormFieldElementsPosition
 *
 * @since   2.0
 */
class JFormFieldElementsPosition extends FormField
{

    /**
     * Unique id.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $_uniqueId;

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.elements_position';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'ElementsPositions';

    /**
     * JFormFieldElementsPosition constructor.
     *
     * @param   Form|null $form
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function __construct(Form $form = null)
    {
        parent::__construct($form);
        $this->_uniqueId = uniqid('elements-');
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @throws  \InvalidArgumentException
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['assets']->add('elements_position', 'less:jui/fields/elements_position.less');
        $this->hyper['helper']['assets']
            ->js('js:widget/admin/fields/elements-position.js')
            ->widget('#' . $this->_uniqueId, 'HyperPC.AdminFieldElementsPosition');

        return parent::getInput();
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $manager  = Manager::getInstance();
        $_group   = (isset($this->element['group']))   ? (string) $this->element['group'] : 'order';
        $_execute = (isset($this->element['execute'])) ? explode(',', (string) $this->element['execute']) : [];

        $groups = [];
        foreach (explode(',', $_group) as $groupName) {
            if (empty($groupName)) {
                continue;
            }

            $groups[] = trim($groupName);
        }

        $elements = $manager->getElementsByGroups($groups);
        if (count($_execute)) {
            foreach ($_execute as $elName) {
                if (isset($elements[$_group][$elName])) {
                    /** @var JSON $meta */
                    $meta = $elements[$_group][$elName]->getMetaData();
                    $meta->set('disable', true);
                }
            }
        }

        return array_merge(parent::getLayoutData(), [
            'groups'    => $groups,
            'elements'  => $elements,
            'uniqueId'  => $this->_uniqueId
        ]);
    }
}
