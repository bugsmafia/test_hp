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
 * Class JFormFieldRelatedCase
 *
 * @since 2.0
 */
class JFormFieldRelatedCase extends JFormFieldRelatedPositions
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'RelatedCase';

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
        $caseFolderId = $app['params']->get('cases_folder', 0, 'int');

        return [$caseFolderId];
    }
}
