<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Period;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Period
 *
 * Allows easy filtering by period names.
 *
 * @package Ansas\Component\Period
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Period
{
    /**
     * Add time values to periods
     */
    const DAYS_AND_TIME = 1;

    /**
     * Do not add time values to periods (time will be 00:00:00 for "max" value as well)
     */
    const DAYS_ONLY = 0;

    /**
     * @var string[] List of available periods
     */
    protected static $periodsAvailable = [
        "today",
        "yesterday",
        "tomorrow",

        "thisMonth",
        "thisMonthToDate",
        "thisWeek",
        "thisWeekToDate",
        "thisYear",
        "thisYearToDate",

        "lastMonth",
        "lastWeek",
        "lastYear",

        "last7",
        "last30",
        "last365",

        "total",
        "untilToday",
    ];

    /**
     * @var DateTimeZone|null
     */
    protected $timezoneFrom;

    /**
     * @var DateTimeZone|null
     */
    protected $timezoneTo;

    /**
     * Period constructor.
     *
     * @param string|DateTimeZone $timezoneFrom [optional]
     * @param string|DateTimeZone $timezoneTo   [optional]
     *
     * @throws Exception
     */
    public function __construct($timezoneFrom = null, $timezoneTo = null)
    {
        $this->setTimezoneFrom($timezoneFrom);
        $this->setTimezoneTo($timezoneTo);
    }

    /**
     * @return string[]
     */
    public static function getAvailablePeriods()
    {
        return static::$periodsAvailable;
    }

    /**
     * Create new instance via static method.
     *
     * @param string|DateTimeZone $timezoneFrom [optional]
     * @param string|DateTimeZone $timezoneTo   [optional]
     *
     * @return static
     */
    public static function create($timezoneFrom = null, $timezoneTo = null)
    {
        return new static($timezoneFrom, $timezoneTo);
    }

    /**
     * Calculate min and max dates and return them as DateTime objects.
     *
     * @param string $period Period name
     * @param int    $mode   [optional] Period::DAYS_AND_TIME or Period::DAYS_ONLY
     *
     * @return DateTime[]
     * @throws Exception If unable to determine the period.
     */
    public function getPeriod($period, $mode = self::DAYS_ONLY)
    {
        $date = new DateTime('today', $this->getTimezoneFrom());

        $min = null;
        $max = null;

        switch ($period) {
            case 'today' :
                $max = clone $date;
                $min = clone $date;
                break;

            case 'yesterday' :
                $max = clone $date->modify('-1 day');
                $min = clone $date;
                break;

            case 'tomorrow' :
                $max = clone $date->modify('+1 day');
                $min = clone $date;
                break;

            case 'thisMonth' :
                $max = clone $date->modify('last day of this month');
                $min = clone $date->modify('first day of this month');
                break;

            case 'thisMonthToDate' :
                $max = clone $date;
                $min = clone $date->modify('first day of this month');
                break;

            case 'thisWeek' :
                $max = clone $date->modify('sunday this week');
                $min = clone $date->modify("monday this week");
                break;

            case 'thisWeekToDate' :
                $max = clone $date;
                $min = clone $date->modify("monday this week");
                break;

            case 'thisYear' :
                $max = clone $date->modify('last day of december this year');
                $min = clone $date->modify('first day of january this year');
                break;

            case 'thisYearToDate' :
                $max = clone $date;
                $min = clone $date->modify('first day of january this year');
                break;

            case 'lastMonth' :
                $max = clone $date->modify('last day of last month');
                $min = clone $date->modify('first day of this month');
                break;

            case 'lastWeek' :
                $max = clone $date->modify('sunday last week');
                $min = clone $date->modify("monday this week");
                break;

            case 'lastYear' :
                $max = clone $date->modify('last day of december last year');
                $min = clone $date->modify('first day of january this year');
                break;

            case 'total' :
                break;

            case 'untilToday' :
                $max = clone $date;
                break;

            default :
                if (preg_match('/^last(\d+)(?:Days)?$/u', $period, $found)) {
                    $days = $found[1];
                    $max  = clone $date;
                    $min  = clone $date->modify("-{$days} day");
                } elseif (preg_match('/^(?:before|-)(\d+)(?:Days)?$/u', $period, $found)) {
                    $days = $found[1];
                    $min  = clone $date->modify("-{$days} day");
                    $max  = clone $date;
                } else {
                    throw new Exception("Period {$period} not supported");
                }
        }

        $result = [];

        if ($max && $mode == self::DAYS_AND_TIME) {
            $max->modify("+1 day -1 second");
        }

        foreach (['min' => $min, 'max' => $max] as $key => $value) {
            if (!$value) {
                continue;
            }
            if ($mode == self::DAYS_ONLY) {
                $value = new DateTime($value->format('Y-m-d'), $this->getTimezoneTo());
            } elseif ($this->getTimezoneTo()) {
                $value->setTimezone($this->getTimezoneTo());
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Calculate min and max dates and return them as DateTime objects.
     *
     * @param string $period Period name
     *
     * @return array
     */
    public function getPeriodWithTime($period)
    {
        return $this->getPeriod($period, self::DAYS_AND_TIME);
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimezoneFrom()
    {
        return $this->timezoneFrom;
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimezoneTo()
    {
        return $this->timezoneTo;
    }

    /**
     * @param string|DateTimeZone|null $timezoneFrom
     *
     * @return $this
     */
    public function setTimezoneFrom($timezoneFrom)
    {
        $this->timezoneFrom = $this->toTimezone($timezoneFrom);

        return $this;
    }

    /**
     * @param string|DateTimeZone|null $timezoneTo
     *
     * @return $this
     */
    public function setTimezoneTo($timezoneTo)
    {
        $this->timezoneTo = $this->toTimezone($timezoneTo);

        return $this;
    }

    /**
     * @param string|DateTimeZone|null $timezone
     *
     * @return DateTimeZone|null
     */
    protected function toTimezone($timezone)
    {
        if (!$timezone) {
            return null;
        }

        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }

        return new DateTimeZone($timezone);
    }
}
