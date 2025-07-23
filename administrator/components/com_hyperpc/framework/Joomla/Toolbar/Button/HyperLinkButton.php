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
 */

namespace HYPERPC\Joomla\Toolbar\Button;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Class HyperLinkButton
 *
 * @package     HYPERPC\Joomla\Toolbar\Button
 *
 * @since       2.0
 */
class HyperLinkButton extends ToolbarButton
{

    /**
     * Button type.
     *
     * @var    string
     *
     * @since   2.0
     */
    protected $_name = 'HyperLink';

    /**
     * Get the button.
     *
     * @param   string  $type
     * @param   string  $icon
     * @param   string  $text
     * @param   null    $url
     * @param   string  $target
     *
     * @return  string
     *
     * @since   2.0
     */
    public function fetchButton($type = 'HyperLink', $icon = 'link', $text = '', $url = null, $target = '_blank')
    {
        return (new FileLayout('joomla.toolbar.hyper_link'))->render([
            'url'    => $url,
            'icon'   => $icon,
            'target' => $target,
            'text'   => Text::_($text)
        ]);
    }

    /**
     * Get the button CSS Id
     *
     * @param   string  $type  The button type.
     * @param   string  $name  The name of the button.
     *
     * @return  string  Button CSS Id
     *
     * @since   2.0
     */
    public function fetchId($type = 'Link', $name = '')
    {
        return $this->_parent->getName() . '-' . $name;
    }
}
