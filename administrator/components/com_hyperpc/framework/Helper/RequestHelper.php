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

use JBZoo\Utils\Str;


/**
 * Class RequestHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class RequestHelper extends AppHelper
{

    /**
     * Check if is current request method is POST.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isPost()
    {
        return 'POST' === strtoupper($this->hyper['input']->getMethod(false, false));
    }

    /**
     * Check, is current request - ajax.
     *
     * @return  bool
     *
     * @since   2.0
     */
    public function isAjax()
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $value) {
                if (Str::low($key) === 'x-requested-with' && Str::low($value) === 'xmlhttprequest') {
                    return true;
                }
            }
        } else if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && Str::low($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            return true;
        }

        return false;
    }
}
