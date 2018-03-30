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

class Workday extends DateTime
{
    const DAY_DATE_FORMAT    = 'Y-m-d';
    const DAY_WEEKDAY_FORMAT = 'N';

    protected $holidays = [
        "2018-01-01" => "Neujahrstag",
        "2018-03-30" => "Karfreitag",
        "2018-04-02" => "Ostermontag",
        "2018-05-01" => "Tag der Arbeit",
        "2018-05-10" => "Christi Himmelfahrt",
        "2018-05-21" => "Pfingstmontag",
        "2018-05-31" => "Fronleichnam",
        "2018-10-03" => "Tag der Deutschen Einheit",
        "2018-12-25" => "1. Weihnachtstag",
        "2018-12-26" => "2. Weihnachtstag",
    ];

    protected $weekend = [
        6,
        7,
    ];

    /**
     * @inheritdoc
     */
    public function __construct(string $time = 'now', DateTimeZone $timezone = null)
    {
        // Create default object with default timestamp
        parent::__construct('now', $timezone);

        // Modify timestamp with our altered logic
        $this->modify($time);
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @param DateTime $date
     *
     * @return static
     */
    public static function fromDateTime(DateTime $date)
    {
        return new static("@" . $date->getTimestamp(), $date->getTimezone());
    }

    /**
     * @inheritdoc
     */
    public function modify($modify)
    {
        if (preg_match('/(?<direction>next|last)\s+workday/ui', $modify, $matches)) {
            $sign = strtolower($matches['direction']) == 'next' ? '+' : '-';
            while (!$this->isWorkday()) {
                $this->modify("{$sign} 1 day");
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
     * @return $this
     */
    public function withoutTime()
    {
        $this->setTime(0, 0, 0);

        return $this;
    }
}
