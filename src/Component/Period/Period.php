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
     * Do not manipulate time values => time will be (if range exist) related to startingPoint for "min" and "max"
     */
    const DAYS_AND_TIME_ORIGINAL = 2;

    /**
     * Add time values for whole day to periods => time will be (if range exist) 00:00:00 for "min" and 23:59:59 for
     * "max"
     */
    const DAYS_AND_TIME = 1;

    /**
     * Do not add time values to periods => time will be (if range exist) 00:00:00 for "min" and "max"
     */
    const DAYS_ONLY = 0;

    /**
     * Default starting point if none set
     */
    const DEFAULT_STARTING_POINT = 'now';

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
     * @var DateTime
     */
    protected $startingPoint;

    /**
     * Period constructor.
     *
     * @param string|DateTimeZone $timezoneFrom  [optional]
     * @param string|DateTimeZone $timezoneTo    [optional]
     * @param string|DateTime     $startingPoint [optional]
     *
     * @throws Exception
     */
    public function __construct($timezoneFrom = null, $timezoneTo = null, $startingPoint = null)
    {
        $this->setTimezoneFrom($timezoneFrom);
        $this->setTimezoneTo($timezoneTo);
        $this->setStartingPoint($startingPoint);
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
     * @param string|DateTimeZone $timezoneFrom  [optional]
     * @param string|DateTimeZone $timezoneTo    [optional]
     * @param string|DateTime     $startingPoint [optional]
     *
     * @return static
     * @throws Exception
     */
    public static function create($timezoneFrom = null, $timezoneTo = null, $startingPoint = null)
    {
        return new static($timezoneFrom, $timezoneTo, $startingPoint);
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
        $result = $this->parsePeriod($period);

        // Remove empty keys (if min/max is not set)
        $result = array_filter($result);

        // Consider "whole day"
        if ($mode == self::DAYS_AND_TIME) {
            if ($result['min']) {
                $result['min']->setTime(0, 0, 0);
            }
            if ($result['max']) {
                $result['max']->setTime(23, 59, 59);
            }
        }

        foreach ($result as $key => $value) {
            if ($mode == self::DAYS_ONLY) {
                $result[$key] = new DateTime($result[$key]->format('Y-m-d'), $this->getTimezoneTo());
            } elseif ($this->getTimezoneTo()) {
                $result[$key]->setTimezone($this->getTimezoneTo());
            }
        }

        return $result;
    }

    /**
     * Calculate min and max dates and return them as DateTime objects.
     *
     * @param string $period Period name
     *
     * @return array
     * @throws Exception
     */
    public function getPeriodWithTime($period)
    {
        return $this->getPeriod($period, self::DAYS_AND_TIME);
    }

    /**
     * Note: Always returns clone so initial starting point is not changed.
     *
     * @return DateTime
     */
    public function getStartingPoint()
    {
        return clone $this->startingPoint;
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
     * @param string|DateTime|null $startingPoint
     *
     * @return $this
     * @throws Exception
     */
    public function setStartingPoint($startingPoint)
    {
        if ($startingPoint instanceof DateTime) {
            $this->startingPoint = $startingPoint->setTimezone($this->getTimezoneFrom());
        } else {
            if (!$startingPoint) {
                $startingPoint = static::DEFAULT_STARTING_POINT;
            }
            $this->startingPoint = new DateTime($startingPoint, $this->getTimezoneFrom());
        }

        return $this;
    }

    /**
     * @param string|DateTimeZone|null $timezoneFrom
     *
     * @return $this
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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

    /**
     * @param $period
     *
     * @return array
     * @throws Exception
     */
    protected function parsePeriod($period)
    {
        if (preg_match('/^\d*-\d*$/u', $period)) {
            return $this->parsePeriodFromDates($period);
        }

        return $this->parsePeriodFromName($period);
    }

    /**
     * @param $period
     *
     * @return array
     * @throws Exception
     */
    protected function parsePeriodFromDates($period)
    {
        if (substr_count($period, '-') != 1) {
            throw new Exception("Expecting 'date1-date2' syntax");
        }

        $result = [
            'min' => null,
            'max' => null,
        ];

        list($result['min'], $result['max']) = explode('-', $period);

        foreach ($result as $key => $value) {
            if (!$value) {
                $result[$key] = null;
                continue;
            }

            $result[$key] = new DateTime($value, $this->getTimezoneFrom());
        }

        return $result;
    }

    /**
     * @param $period
     *
     * @return array
     * @throws Exception
     */
    protected function parsePeriodFromName($period)
    {
        $min = null;
        $max = null;

        $date = $this->getStartingPoint();

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
                } elseif ($period[0] === '-') {
                    $max = clone $date;
                    $min = clone $date->modify($period);
                } elseif ($period[0] === '+') {
                    $min = clone $date;
                    $max = clone $date->modify($period);
                } else {
                    throw new Exception("Period {$period} not supported");
                }
        }

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $result = [
            'min' => $min,
            'max' => $max,
        ];

        return $result;
    }
}
