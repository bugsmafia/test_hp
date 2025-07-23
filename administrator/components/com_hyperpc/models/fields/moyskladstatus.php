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
 * @author      Artem Vyshnevskiy
 */

defined('_JEXEC') or die('Restricted access');

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MoySkladHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Class JFormFieldMoyskladStatus
 *
 * @since 2.0
 */
class JFormFieldMoyskladStatus extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'MoyskladStatus';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @throws  Exception
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $app = App::getInstance();
        /** @var MoySkladHelper $moyskladHelper */
        $moyskladHelper = $app['helper']['moysklad'];

        $options = [
            [
                'value' => 0,
                'text'  => Text::_('JOPTION_DO_NOT_USE')
            ]
        ];

        $statusList = $moyskladHelper->getStatusList();

        foreach ($statusList as $status) {
            $options[$status->id]['value'] = $status->id;
            $options[$status->id]['text']  = $status->name;
        }

        return array_merge(parent::getOptions(), $options);
    }
}
