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
 * @author      Roman Evsyukov <roman_e@hyperpc.ru>
 */

use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldPreview
 *
 * @since 2.0
 */
class JFormFieldPreview extends FormField
{

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'preview';

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'Preview';

    /**
     * Get link attributes
     *
     * @return string
     *
     * @since   2.0
     */
    public function getAttrs()
    {
        $task  = (string) $this->element['task'];
        $field = (string) $this->element['field'];
        $class = $this->class;
        $route = [];

        if ($task === 'pdf') {
            $route = [
                'tmpl'   => 'component',
                'action' => 'buildPdfPreview',
                'task'   => 'elements.call',
                'group'  => 'configuration_actions',
                'type'   => 'commercial_proposal',
            ];

            $class .= ' jsPdfPreview';
        }

        if ($field) {
            $route['field'] = $field;
        }

        $url = $this->hyper['route']->build($route);

        return $this->hyper['helper']['html']->buildAttrs([
            'href'  => $url,
            'class' => $class,
        ]);
    }

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    public function getInput()
    {
        $this->hyper['wa']->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('elements:configuration_actions/commercial_proposal/assets/js/fields/preview.js')
            ->widget('body', 'HyperPC.FieldPreview', []);

        return parent::getInput();
    }

    /**
     * Get link title
     *
     * @return string
     *
     * @since   2.0
     */
    public function getTitle()
    {
        return (string) $this->element['title'];
    }

    /**
     * Allow to override renderer include paths in child fields.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getLayoutPaths()
    {
        return [$this->hyper['path']->get('elements:configuration_actions/commercial_proposal/fields/layout')];
    }
}
