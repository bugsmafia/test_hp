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
use Joomla\CMS\Form\Field\ListField;
use HYPERPC\Joomla\Model\Entity\MoyskladPart;

/**
 * Class JFormFieldRelatedPositions
 *
 * @since 2.0
 */
abstract class JFormFieldRelatedPositions extends ListField
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'RelatedPositions';

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
        $app = App::getInstance();

        $options = [[
            'value' => HP_STATUS_UNPUBLISHED,
            'text'  => Text::_('COM_HYPERPC_SELECT_ITEM_ENTITY')
        ]];

        $folderIds = $this->getFolderIds();

        $db = $app['db'];
        $conditions = [];
        if (!empty($folderIds)) {
            $conditions[] = $db->qn('a.product_folder_id') . ' IN (' . $db->q(join(', ', $folderIds)) . ')';
        }

        $positionHelper = $app['helper']['position'];

        $positions = $positionHelper->findAll([
            'conditions' => $conditions
        ]);

        foreach ((array) $positions as $id => $position) {
            $position = $positionHelper->expandToSubtype($position);

            $itemKey = $position->getItemKey();
            $options[$itemKey]['value'] = $itemKey;
            $options[$itemKey]['text']  = $position->name;

            if ($position instanceof MoyskladPart) {
                $variants = $position->getOptions();
                if (!empty($variants)) {
                    $options[$itemKey]['disable'] = true;
                }

                foreach ($variants as $id => $variant) {
                    $variantItemKey = $itemKey . '-' . $id;
                    $options[$variantItemKey]['value'] = $variantItemKey;
                    $options[$variantItemKey]['text']  = ' - ' . $variant->name;
                }
            }
        }

        return $options;
    }

    /**
     * Get folder ids
     *
     * @return  int[]
     *
     * @since   2.0
     */
    abstract protected function getFolderIds();
}
