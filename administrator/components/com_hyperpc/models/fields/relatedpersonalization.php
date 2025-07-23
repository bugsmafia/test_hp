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
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('relatedpositions');

/**
 * Class JFormFieldRelatedPersonalization
 *
 * @since 2.0
 */
class JFormFieldRelatedPersonalization extends JFormFieldRelatedPositions
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'RelatedPersonalization';

    /**
     * Get folder ids
     *
     * @return  int[]
     *
     * @since   2.0
     */
    protected function getFolderIds()
    {
        $app = App::getInstance();
        $folderIds = $app['params']->get('product_customization_folders', []);

        return array_map(function ($id) {
            return (int) $id;
        }, $folderIds);
    }
}
