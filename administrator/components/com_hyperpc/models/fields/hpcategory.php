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

use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die('Restricted access');

FormHelper::loadFieldClass('HPParent');

/**
 * Class JFormFieldHPCategory
 *
 * @since 2.0
 */
class JFormFieldHPCategory extends JFormFieldHPParent
{

    /**
     * The form field type.
     *
     * @var     string
     *
     * @since   2.0
     */
    protected $type = 'HPCategory';
}
