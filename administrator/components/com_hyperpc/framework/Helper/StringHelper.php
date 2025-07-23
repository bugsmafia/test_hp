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
 * @author      Artem Vyshnevskiy
 */

namespace HYPERPC\Helper;

use JBZoo\Utils\Filter;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use HYPERPC\Helper\MacrosHelper;

/**
 * Class StringHelper
 *
 * @package HYPERPC\Helper
 *
 * @since   2.0
 */
class StringHelper extends AppHelper
{

    /**
     * Clear mobile phone.
     *
     * @param   string|int  $phone
     * @param   array       $search
     *
     * @return  int
     *
     * @since   2.0
     */
    public function clearMobilePhone($phone, $search = [])
    {
        $search = array_merge($search, [' ', '-', '+', '(', ')']);
        return Filter::int(str_replace($search, '', $phone));
    }

    /**
     * Get string value of parameter from multilanguage array or object having language sef keys.
     *
     * @param array|\stdClass $value
     *
     * @return string
     */
    public function filterLanguage($value): string
    {
        if ($value instanceof \stdClass) {
            $value = json_decode(json_encode($value), true);
        }

        if (\is_array($value)) {
            $sefs = [
                substr($this->hyper->getLanguageCode(), 0, 2),
                substr($this->hyper->getDefaultLanguageCode(), 0, 2)
            ];

            foreach ($sefs as $sef) {
                if (key_exists($sef, $value) && \is_string($value[$sef]) && !empty($value[$sef])) {
                    return $value[$sef];
                }
            }
        }

        return '';
    }

    /**
     * Explode string to trim array item.
     *
     * @param   string  $string
     * @param   string  $ds
     *
     * @return  array
     *
     * @since   2.0
     */
    public function toArray($string, $ds = ',')
    {
        $return  = [];
        $details = explode($ds, $string);
        foreach ($details as $detail) {
            if (empty($detail)) {
                continue;
            }

            $return[] = trim($detail);
        }

        return $return;
    }

    /**
     * Text to character data.
     *
     * @param   string|int  $text
     *
     * @return  string
     *
     * @since   2.0
     */
    public function toCData($text)
    {
        return '<![CDATA[' . $text . ']]>';
    }

    /**
     * Render date macroses
     *
     * @param  string $string
     *
     * @return string
     *
     * @since   2.0
     */
    public function renderDates(string $string)
    {
        $date = Date::getInstance();

        $month = $date->format('F');

        $monthsNames = explode(',', Text::_('COM_HYPERPC_NAMES_OF_MONTHS_NOMINATIVE'));
        if (count($monthsNames) === 12) {
            $month = $monthsNames[$date->format('n') - 1];
        }

        /** @var MacrosHelper $macrosHelper */
        $macrosHelper = $this->hyper['helper']['macros'];

        $macrosHelper->setData([
            'year' => $date->year,
            'month' => $month
        ]);

        return $macrosHelper->text($string);
    }

    /**
     * Is the email address automatically generated?
     *
     * @param   string $email
     *
     * @return  bool
     */
    public function isAutoEmail($email): bool
    {
        $email = (string) $email;
        return (bool) preg_match('/^no-name-[a-z0-9]{13}@gmail.com$/', $email);
    }

    /**
     * Validates email
     *
     * @param  string $email
     *
     * @return bool
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates phone number in an international format
     *
     * @param   string $phone
     *
     * @return  bool
     */
    public function isValidPhone(string $phone): bool
    {
        $cleanedPhone = str_replace(['(', ')', ' ', '-'], '', $phone);

        return preg_match('/^\+?\d{8,15}$/', $cleanedPhone) === 1;
    }

    /**
     * Removes any content enclosed in square brackets from the given string.
     *
     * @param   string $string The input string that may contain content within square brackets.
     *
     * @return  string The modified string with square bracket content removed.
     */
    public function stripSquareBracketContent(string $string): string
    {
        $result = preg_replace('/\s*\[.*?\]\s*/', ' ', $string);

        if ($result === null) {
            return $string;
        }

        return trim($result);
    }
}
