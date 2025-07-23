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
 * @author      Roman Evsyukov
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use HYPERPC\Joomla\Form\FormField;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldPartVariants
 *
 * @since 2.0
 */
class JFormFieldPartVariants extends FormField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'PartVariants';

    /**
     * Name of the layout being used to render the field.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $layout = 'joomla.form.field.moysklad.part_variants';

    /**
     * Get layout data.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function getData()
    {
        return $this->getLayoutData();
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function getInput()
    {
        $this->hyper['helper']['assets']
            ->js('js:widget/fields/variants.js')
            ->widget('.jsFieldVariants', 'HyperPC.FieldVariants', [
                'formToken'          => Session::getFormToken(),
                'confirmMessage'     => Text::_('COM_HYPERPC_ARE_YOU_SURE'),
                'showArchiveMessage' => Text::_('COM_HYPERPC_PART_OPTION_ARCHIVE_SHOW'),
                'hideArchiveMessage' => Text::_('COM_HYPERPC_PART_OPTION_ARCHIVE_HIDE')
            ]);

        return parent::getInput();
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    protected function getLayoutData()
    {
        $data = clone $this->form->getData();
        $data->offsetUnset('created_time');
        $data->offsetUnset('modified_time');

        if (!isset($data['params'])) {
            $data['params'] = [];
        }

        $part = new MoyskladPart($data->toArray());

        return array_merge(parent::getLayoutData(), ['part' => $part]);
    }
}
