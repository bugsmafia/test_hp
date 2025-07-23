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

namespace HYPERPC\Joomla\Log\Logger;

use Joomla\CMS\Log\LogEntry;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Log\Logger\FormattedtextLogger as TextLogger;

defined('_JEXEC') or die('Restricted access');

/**
 * Class FormatedTextLogger
 *
 * @package HYPERPC\Joomla\Log\Logger
 *
 * @since   2.0
 */
class FormatedTextLogger extends TextLogger
{

    /**
     * Format a line for the log file.
     *
     * @param   LogEntry  $entry  The log entry to format as a string.
     *
     * @return  string
     *
     * @since   2.0
     */
    protected function formatLine(LogEntry $entry)
    {
        //  Set some default field values if not already set.
        if (!isset($entry->clientIP)) {
            $ip = IpHelper::getIp();

            if ($ip !== '') {
                $entry->clientIP = $ip;
            }
        }

        //  If the time field is missing or the date field isn't only the date we need to rework it.
        if ((strlen($entry->date) != 10) || !isset($entry->time)) {
            // Get the date and time strings in GMT.
            $entry->datetime = $entry->date->toSql();
            $entry->time     = $entry->date->format('H:i:s', false);
            $entry->date     = $entry->date->format('Y-m-d', false);
        }

        //  Get a list of all the entry keys and make sure they are upper case.
        $tmp = array_change_key_case(get_object_vars($entry), CASE_UPPER);

        //  Decode the entry priority into an English string.
        $tmp['PRIORITY'] = $this->priorities[$entry->priority];

        //  Fill in field data for the line.
        $line = $this->format;

        foreach ($this->fields as $field) {
            $line = str_replace('{' . $field . '}', (isset($tmp[$field])) ? $tmp[$field] : '-', $line);
        }

        return $line;
    }
}
