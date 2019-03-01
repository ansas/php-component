<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Date;

use DateTime;
use DateTimeZone;

/**
 * Class Workday
 *
 * @package Ansas\Component\Date
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Workday extends DateTime
{
    const DAY_DATE_FORMAT    = 'Y-m-d';
    const DAY_WEEKDAY_FORMAT = 'N';

    /**
     * @var array List of dates to consider as holiday
     */
    protected $holidays = [
        "2018-01-01" => "Neujahrstag",
        "2018-03-30" => "Karfreitag",
        "2018-04-02" => "Ostermontag",
        "2018-05-01" => "Tag der Arbeit",
        "2018-05-10" => "Christi Himmelfahrt",
        "2018-05-21" => "Pfingstmontag",
        "2018-05-31" => "Fronleichnam",
        "2018-10-03" => "Tag der Deutschen Einheit",
        "2018-10-31" => "Reformationstag",
        "2018-11-01" => "Allerheiligen",
        "2018-12-25" => "1. Weihnachtstag",
        "2018-12-26" => "2. Weihnachtstag",

        "2019-01-01" => "Neujahrstag",
        "2019-04-19" => "Karfreitag",
        "2019-04-22" => "Ostermontag",
        "2019-05-01" => "Tag der Arbeit",
        "2019-05-30" => "Christi Himmelfahrt",
        "2019-06-10" => "Pfingstmontag",
        "2019-06-20" => "Fronleichnam",
        "2019-10-03" => "Tag der Deutschen Einheit",
        "2019-10-31" => "Reformationstag",
        "2019-11-01" => "Allerheiligen",
        "2019-12-25" => "1. Weihnachtstag",
        "2019-12-26" => "2. Weihnachtstag",
    ];

    /**
     * @var array List of days to consider as weekend
     */
    protected $weekend = [
        6,
        7,
    ];

    /**
     * @inheritdoc
     */
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        // Create default object with default timestamp
        parent::__construct('now', $timezone);

        // Modify timestamp with our altered logic
        if (strlen($time)) {
            $this->modify($time);
        }
    }

    public function __clone()
    {
    }

    /**
     * @param DateTime $date
     *
     * @return static
     */
    public static function fromDateTime(DateTime $date)
    {
        $workday = new static("@" . $date->getTimestamp());
        $workday->setTimezone($date->getTimezone());

        return $workday;
    }

    /**
     * @inheritdoc
     */
    public function modify($modify)
    {
        if (preg_match('/(?<direction>next|last)\s+workday/ui', $modify, $matches)) {
            if (strtolower($matches['direction']) == 'next') {
                $this->nextWorkday();
            } else {
                $this->lastWorkday();
            }
            $modify = str_replace($matches[0], '', $modify);
        }

        if (preg_match('/(?<sign>\+|\-)?\s*(?<days>\d+)\s*workdays?/ui', $modify, $matches)) {
            $this->addWorkdays($matches['sign'] . $matches['days']);
            $modify = str_replace($matches[0], '', $modify);
        }

        $modify = trim($modify);
        if ($modify) {
            parent::modify($modify);
        }
    }

    /**
     * @param int $days
     *
     * @return $this
     */
    public function addWorkdays($days)
    {
        $done = 0;
        $days = (int) $days;
        $sign = '+';

        if ($days < 0) {
            $days *= -1;
            $sign = '-';
        }

        while ($done < $days) {
            $this->modify("{$sign} 1 day");
            if ($this->isWorkday()) {
                $done++;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function lastWorkday()
    {
        if (!$this->isWorkday()) {
            $this->addWorkdays(-1);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function nextWorkday()
    {
        if (!$this->isWorkday()) {
            $this->addWorkdays(+1);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getHolidays()
    {
        return $this->holidays;
    }

    /**
     * @return array
     */
    public function getWeekendDays()
    {
        return $this->weekend;
    }

    /**
     * @return bool
     */
    public function isHoliday()
    {
        return in_array($this->format(static::DAY_DATE_FORMAT), array_keys($this->holidays));
    }

    /**
     * @return bool
     */
    public function isWeekend()
    {
        return in_array($this->format(static::DAY_WEEKDAY_FORMAT), $this->weekend);
    }

    /**
     * @return bool
     */
    public function isWorkday()
    {
        return !$this->isWeekend() && !$this->isHoliday();
    }

    /**
     * @param array $holidays
     *
     * @return $this
     */
    public function setHolidays(array $holidays)
    {
        $this->holidays = $holidays;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function toDateTime()
    {
        $date = new static("@" . $this->getTimestamp());
        $date->setTimezone($this->getTimezone());

        return $date;
    }

    /**
     * @return $this
     */
    public function withoutTime()
    {
        $this->setTime(0, 0, 0);

        return $this;
    }
}
