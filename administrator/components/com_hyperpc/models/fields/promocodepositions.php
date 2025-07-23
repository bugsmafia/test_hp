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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use HYPERPC\Joomla\Form\FormField;

/**
 * Class JFormFieldPromoCodePositions
 *
 * @since   2.0
 */
class JFormFieldPromoCodePositions extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'PromoCodePositions';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.promocode_positions';

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
        $this->hyper['wa']->usePreset('jquery-fancybox');

        $this->hyper['helper']['assets']
            ->js('js:widget/fields/promocode-positions.js')
            ->widget('.jsPromoCodePositions', 'HyperPC.FieldPromoCodePositions', [
                'fieldName'    => $this->name,
                'viewItemId'   => $this->hyper['input']->get('id'),
                'removeTitle'  => Text::_('COM_HYPERPC_FIELD_RELATED_REMOVE_ITEM'),
                'deleteImgUrl' => $this->hyper['path']->url('img:icons/delete.png', false)
            ]);

        return parent::getInput();
    }
}
