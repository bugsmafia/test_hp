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

namespace HYPERPC\Helper;

use HYPERPC\Data\JSON;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use HYPERPC\Object\Date\DatesRange;

/**
 * Class DateHelper
 *
 * @package HYPERPC\Helper
 */
class DateHelper extends AppHelper
{
    /**
     * Array for spread in Joomla\CMS\Date\Date::format method args
     */
    public const INTERNAL_FORMAT_ARGS = ['M d Y', true, false];

    /**
     * Add days interval to given Date object
     *
     * @param   Date $date
     * @param   int $days count of days
     *
     * @return  Date returns new instance
     */
    public function addDays(Date $date, int $days): Date
    {
        $_date = clone $date;

        $_date->add(new \DateInterval('P' . $days . 'D'));

        return $_date;
    }

    /**
     * Convert DatesRange to internal format
     *
     * @param   DatesRange $datesRange
     *
     * @return  string
     */
    public function datesRangeToRaw(DatesRange $datesRange): string
    {
        if (!$datesRange->min || !$datesRange->max) {
            return '';
        }

        $result = $datesRange->min->format(...self::INTERNAL_FORMAT_ARGS);

        if ($datesRange->max > $datesRange->min) {
            $maxDate = $datesRange->max->format(...self::INTERNAL_FORMAT_ARGS);

            if ($result !== $maxDate) {
                $result .= ' - ' . $maxDate;
            }
        }

        return $result;
    }

    /**
     * Convert DatesRange to an easy-to-read string
     *
     * @param   DatesRange $datesRange
     *
     * @return  string
     */
    public function datesRangeToString(DatesRange $datesRange, bool $year = false): string
    {
        $minDate = $datesRange->min;
        $maxDate = $datesRange->max;

        if (!$minDate || !$maxDate) {
            return '';
        }

        if ($minDate->toUnix() === $maxDate->toUnix()) {
            if ($year) {
                return $minDate->format(Text::_('DATE_FORMAT_LC3'), true);
            }

            return $minDate->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true);
        }

        $minYear = $minDate->year;
        $maxYear = $maxDate->year;

        if ($minYear !== $maxYear) {
            return $minDate->format(Text::_('DATE_FORMAT_LC3'), true) . ' - ' . $maxDate->format(Text::_('DATE_FORMAT_LC3'), true);
        }

        $resultString = '';

        $minMonth = $minDate->month;
        $maxMonth = $maxDate->month;

        if ($minMonth !== $maxMonth) {
            $resultString =
                $minDate->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true) . ' - ' .
                $maxDate->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true);
        } else {
            $minDateStr = $minDate->format(Text::_('COM_HYPERPC_DATE_FORMAT_LONG_NO_YEAR'), true);
            $resultString = preg_replace(
                '/\d+/',
                $minDate->format('j', true) . ' - ' . $maxDate->format('j', true),
                $minDateStr
            );
        }

        if ($year) {
            return $resultString .= ' ' . $minYear;
        }

        return $resultString;
    }

    /**
     * Get current time with server time zone
     *
     * @return  Date
     */
    public function getCurrentDateTime(): Date
    {
        return new Date('now', $this->getServerTimeZone());
    }

    /**
     * Get nearest date by schedule
     *
     * @param   JSON $schedule
     * @param   ?Date $fromDate
     *
     * @return  Date
     */
    public function getNearestScheduleDate(JSON $schedule, Date $fromDate = null): Date
    {
        if ($fromDate === null) {
            $fromDate = $this->getCurrentDateTime();
        }

        $scheduleDays     = $schedule->get('days', []);
        $currentDayOfWeek = $fromDate->dayofweek;
        $isWorkingDay     = empty($scheduleDays) || in_array($currentDayOfWeek, $scheduleDays);

        if ($isWorkingDay) {
            if (!$this->isToday($fromDate)) {
                return $fromDate;
            }

            $workingUntil = $schedule->get('to_time', '20:00');
            if ($workingUntil === '00:00') {
                return $fromDate;
            }

            list ($hour, $minute) = explode(':', $workingUntil);

            $workingUntilDate = (clone $fromDate)->setTime((int) $hour, (int) $minute);

            if ($fromDate < $workingUntilDate) {
                return $fromDate;
            }
        }

        $prevDaysOfWeek = array_filter($scheduleDays, function ($dayNumber) use ($currentDayOfWeek) {
            return $dayNumber < $currentDayOfWeek;
        });

        $nextDaysOfWeek = array_filter($scheduleDays, function ($dayNumber) use ($currentDayOfWeek) {
            return $dayNumber > $currentDayOfWeek;
        });

        $nextWorkingDayOfWeek = !empty($nextDaysOfWeek) ? current($nextDaysOfWeek) : current($prevDaysOfWeek);

        $daysDifference = $currentDayOfWeek < $nextWorkingDayOfWeek ?
            $nextWorkingDayOfWeek - $currentDayOfWeek:
            (7 - $currentDayOfWeek) + $nextWorkingDayOfWeek;

        return $this->addDays($fromDate, $daysDifference);
    }

    /**
     * Get server time zone
     *
     * @return  \DateTimeZone
     */
    public function getServerTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone(Factory::getApplication()->get('offset', 'UTC'));
    }

    /**
     * Get user's date object
     *
     * @return  Date
     */
    public function getUserDateTime(): Date
    {
        return new Date('now', $this->getUserTimeZone());
    }

    /**
     * Get user timezone
     *
     * @return  \DateTimeZone
     */
    public function getUserTimeZone(): \DateTimeZone
    {
        return Factory::getApplication()->getIdentity()->getTimezone();
    }

    /**
     * Is today date
     *
     * @param   Date $date
     *
     * @return  bool
     */
    public function isToday(Date $date): bool
    {
        $timeZone = $date->getTimezone();
        $currentDate = new Date('now', $timeZone);

        return $date->format(...self::INTERNAL_FORMAT_ARGS) === $currentDate->format(...self::INTERNAL_FORMAT_ARGS);
    }

    /**
     * Is tomorrow date
     *
     * @param   Date $date
     *
     * @return  bool
     */
    public function isTomorrow(Date $date): bool
    {
        $timeZone = $date->getTimezone();
        $tomorrowDate = Date::getInstance('now', $timeZone)->add(new \DateInterval('P1D'));

        return $date->format(...self::INTERNAL_FORMAT_ARGS) === $tomorrowDate->format(...self::INTERNAL_FORMAT_ARGS);
    }

    /**
     * Is working day by schedule
     *
     * @return  bool
     */
    public function isWorkingDay(): bool
    {
        $date = $this->getCurrentDateTime();

        $schedule = new JSON($this->hyper['params']->get('schedule'));

        $scheduleDays     = $schedule->get('days', []);
        $currentDayOfWeek = $date->dayofweek;

        return empty($scheduleDays) || in_array($currentDayOfWeek, $scheduleDays);
    }

    /**
     * Is working time by schedule
     *
     * @return  bool
     */
    public function isWorkingTime(): bool
    {
        $date = $this->getCurrentDateTime();

        $schedule = new JSON($this->hyper['params']->get('schedule'));

        $workingFrom = $schedule->get('to_time', '10:00');
        $workingUntil = $schedule->get('to_time', '20:00');
        if ($workingFrom === '00:00' && $workingUntil === '00:00') {
            return true;
        }

        list ($fromHour, $fromMinute) = explode(':', $workingFrom);
        list ($untilHour, $untilMinute) = explode(':', $workingUntil);

        $workingFromDate = (clone $date)->setTime((int) $fromHour, (int) $fromMinute);
        $workingUntilDate = (clone $date)->setTime((int) $untilHour, (int) $untilMinute);

        if ($date >= $workingFromDate && ($date < $workingUntilDate || $workingUntil === '00:00')) {
            return true;
        }

        return false;
    }

    /**
     * Parse dates string
     *
     * @param   string $dateString
     * @param   bool $userTimeZone server time zone when false
     *
     * @return  DatesRange
     */
    public function parseString(string $dateString, bool $userTimeZone = false): DatesRange
    {
        $delimiter = ' - ';
        $dates = explode($delimiter, $dateString);

        if (!is_array($dates) || empty($dates) || empty($dates[0]) || count($dates) > 2) {
            return new DatesRange();
        }

        $minDate = trim($dates[0]);
        $maxDate = $minDate;

        if (count($dates) === 2 && trim($dates[1] !== $minDate)) {
            $maxDate = trim($dates[1]);
        }

        $tz = $userTimeZone ?
            $this->getUserTimeZone() :
            $this->getServerTimeZone();

        try {
            return new DatesRange([
                'min' => new Date($minDate, $tz),
                'max' => new Date($maxDate, $tz)
            ]);
        } catch (\Throwable $th) {
        }

        return new DatesRange();
    }
}
