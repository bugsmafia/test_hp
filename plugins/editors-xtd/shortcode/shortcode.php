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

/**
 * Editor Shortcode buton
 *
 * @since  1.5
 */
class PlgButtonShortcode extends JPlugin
{
	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  JObject  The button options as JObject
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{
        $link = 'index.php?option=com_hyperpc&amp;view=shortcode&amp;tmpl=component&amp;'
            . JSession::getFormToken() . '=1&amp;editor=' . $name;

		$button = new JObject;
		$button->modal   = true;
		$button->class   = 'btn';
		$button->link    = $link;
		$button->text    = 'Shortcode';
		$button->name    = 'pencil';
		$button->options = "{handler: 'iframe', size: {x: 600, y: 500}}";

		return $button;
	}
}
