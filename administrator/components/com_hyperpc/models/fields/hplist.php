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
 */

use HYPERPC\App;
use Joomla\CMS\Language\Text;
use HYPERPC\ORM\Entity\Entity;
use Joomla\CMS\Form\Field\ListField;
use HYPERPC\Helper\Context\EntityContext;

defined('_JEXEC') or die('Restricted access');

/**
 * Class JFormFieldHPList
 *
 * @since 2.0
 */
class JFormFieldHPList extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPList';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     *
     * @since  2.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Method to get the field options.
     *
     * @return  array
     *
     * @throws  \Exception
     * @throws  \RuntimeException
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function getOptions()
    {
        $hp = App::getInstance();

        $context = ((string) $this->element['context']) ?: 'position';
        $order = ((string) $this->element['order']) ?: 'a.product_folder_id ASC';
        $published = \in_array((string) $this->element['published'], ['1', 'true']);

        $options = [[
            'value' => 0,
            'text'  => Text::_('COM_HYPERPC_SELECT_ITEM_ENTITY')
        ]];

        $context = \strtolower($context);

        $db = $hp['db'];

        $findOptions = [
            'order'  => $db->quote($order),
            'select' => [$db->quoteName('a.id'), $db->quoteName('a.name')]
        ];

        if ($published && \in_array($context, ['position', 'moyskladproduct', 'moyskladpart', 'moyskladservice', 'moyskladvariant'])) {
            $findOptions['conditions'] = [
                $db->quoteName('state') . ' = ' . $db->quote(HP_STATUS_PUBLISHED)
            ];
        }

        /** @var EntityContext $helper */
        $helper = $hp['helper'][$context];
        $items  = $helper->findAll($findOptions);

        /** @var Entity $item */
        foreach ((array) $items as $i => $item) {
            $options[$item->id]['value'] = $item->id;
            $options[$item->id]['text']  = $item->id . ': ' . $item->name;
        }

        return $options;
    }
}
