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
 * @author      Sergey Kalistratov <kalistratov.s.m@gmail.com>
 */

use HYPERPC\App;
use JBZoo\Utils\Str;
use HYPERPC\Joomla\Form\FormField;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHPImage
 *
 * @since   2.0
 */
class JFormFieldHPImage extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPImage';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.image';

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['wa']->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/image.js')
            ->widget('#' . Str::slug($this->id), 'HyperPC.FieldImage');

        return parent::getInput();
    }
}
