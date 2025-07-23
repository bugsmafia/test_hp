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

namespace HYPERPC\Helper;

use Joomla\CMS\Language\Text;

/**
 * Class WidgetkitHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class WidgetkitHelper extends AppHelper
{

    /**
     * Render widgetkit widget.
     *
     * @param   string $params
     * @return  string
     *
     * @since   2.0
     */
    public function renderWidget($params = '{}')
    {
        /** @noinspection PhpIncludeInspection */
        if (!$app = @include(JPATH_ADMINISTRATOR . '/components/com_widgetkit/widgetkit-app.php')) {
            return '';
        }

        $output = $app->renderWidget(json_decode($params, true));
        if ($output === false) {
            return Text::_('COM_HYPERPC_COULD_NOT_LOAD_WIDGET');
        }

        return $output;
    }
}

