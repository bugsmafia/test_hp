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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use HYPERPC\Joomla\Form\Form;
use HYPERPC\Joomla\View\ViewLegacy;

/**
 * Class HyperPcViewShortcode
 *
 * @property    Form    $form
 *
 * @since       2.0
 */
class HyperPcViewShortcode extends ViewLegacy
{
    /**
     * Display view action.
     *
     * @param   null|string $tpl
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   2.0
     */
    public function display($tpl = null)
    {
        HTMLHelper::_('bootstrap.tab');

        Form::addFormPath(HP_ADMIN_PATH_MODELS . '/forms');

        $this->form = Form::getInstance('shortcode', 'shortcode', []);

        parent::display($tpl);
    }
}
