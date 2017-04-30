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

class Workday extends DateTime
{
    const DAY_DATE_FORMAT    = 'Y-m-d';
    const DAY_WEEKDAY_FORMAT = 'N';

    protected $holidays = [
        "2017-01-01" => "Neujahrstag",
        "2017-04-14" => "Karfreitag",
        "2017-04-17" => "Ostermontag",
        "2017-05-01" => "Tag der Arbeit",
        "2017-05-25" => "Christi Himmelfahrt",
        "2017-06-05" => "Pfingstmontag",
        "2017-10-03" => "Tag der Deutschen Einheit",
        "2017-10-31" => "Reformationstag",
        "2017-12-25" => "1. Weihnachtstag",
        "2017-12-26" => "2. Weihnachtstag",
    ];

    protected $weekend = [
        6,
        7,
    ];

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
     * @param int $days
     *
     * @return $this
     */
    public function addWorkdays($days)
    {
        $done = 0;

        while ($done < $days) {
            $this->modify("+1 day");
            if (!$this->isWeekend() && !$this->isHoliday()) {
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
     * @return $this
     */
    public function withoutTime()
    {
        $this->setTime(0, 0, 0);

        return $this;
    }
}
