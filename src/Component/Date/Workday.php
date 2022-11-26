<?php

/** @noinspection PhpUnused */
/** @noinspection SpellCheckingInspection */

/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Date;

use Ansas\Util\File;
use Ansas\Util\Path;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Workday
 *
 * @package Ansas\Component\Date
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Workday extends DateTime
{
    const DAY_DATE_FORMAT       = 'Y-m-d';
    const DAY_WEEKDAY_FORMAT    = 'N';
    const DEFAULT_TEMPLATE_NAME = 'default';

    /**
     * @var string Path to holiday templates
     */
    private static $holidayTemplatePath;

    /**
     * @var string Name of holiday template
     */
    protected $holidayTemplate;

    /**
     * @var array|null List of dates to consider as holiday
     */
    protected $holidays;

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
    public function __construct(
        $time = 'now',
        DateTimeZone $timezone = null,
        string $holidayTemplate = self::DEFAULT_TEMPLATE_NAME
    ) {
        $this->setHolidayTemplate($holidayTemplate);

        // Auto-detect timestamps missing prefix '@'
        if (ctype_digit((string) $time)) {
            $time = '@' . $time;
        }

        // Important: parent constructor ignores timezone if time is timestamp or contains timezone
        // @see: https://www.php.net/manual/de/datetime.construct.php
        // @see: https://en.wikipedia.org/wiki/ISO_8601#Time_zone_designators
        if (preg_match('/^@|(?:[+\-]\d\d(:?\d\d)?)$|\d\dZ$/ui', $time)) {
            parent::__construct($time, $timezone);
        } else {
            // Create default object with current timestamp
            parent::__construct('now', $timezone);

            // Modify timestamp with our altered logic
            if (strlen($time)) {
                $this->modify($time);
            }
        }

        // Sanitize timezone (always use UTC if offset ist 0)
        if (!$this->getOffset()) {
            $this->toUtc();
        }
    }

    public function __clone()
    {
    }

    /**
     * Create new instance.
     *
     * @param string       $time     [optional]
     * @param DateTimeZone $timezone [optional]
     *
     * @return static
     * @throws Exception
     */
    public static function create(
        $time = 'now',
        DateTimeZone $timezone = null,
        string $holidayTemplate = self::DEFAULT_TEMPLATE_NAME
    ) {
        return new static($time, $timezone, $holidayTemplate);
    }

    public static function diffWorkdays(Workday $start, Workday $end, DateTimeZone $timezone = null): int
    {
        // Make sure we don't change original objects
        $start = clone $start;
        $end   = clone $end;

        // Both dates must use same timezone
        if ($timezone) {
            $start->setTimezone($timezone);
            $end->setTimezone($timezone);
        } else {
            $end->setTimezone($start->getTimezone());
        }

        // Compare without time
        $start->withoutTime();
        $end->withoutTime();

        $diff    = 0;
        $factor  = $start > $end ? -1 : 1;
        $special = !$start->isWorkday() && !$end->isWorkday();

        while ($start != $end) {
            $start->addWorkdays($factor);
            $diff += $factor;

            // Stop if start has overtaken end
            if ($factor != ($start > $end ? -1 : 1)) {
                break;
            }
        }

        // Special case: both dates were not workdays and diff would be one workday
        if ($special && $diff == $factor) {
            return 0;
        }

        return $diff;
    }

    /**
     * @param DateTime $date
     *
     * @return static
     * @throws Exception
     */
    public static function fromDateTime(DateTime $date)
    {
        $workday = new static("@" . $date->getTimestamp());
        $workday->setTimezone($date->getTimezone());

        return $workday;
    }

    /**
     * @return string
     */
    final public static function getHolidayPath()
    {
        return static::$holidayTemplatePath;
    }

    /**
     * @param string $path
     */
    final public static function setHolidayPath(string $path)
    {
        self::$holidayTemplatePath = $path;
    }

    /**
     * Clone instance.
     *
     * @return static
     */
    public function clone()
    {
        return clone $this;
    }

    public function getWeekday(): int
    {
        return (int) $this->format(static::DAY_WEEKDAY_FORMAT);
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

        if (preg_match('/(?<sign>[+\-])?\s*(?<days>\d+)\s*workdays?/ui', $modify, $matches)) {
            $this->addWorkdays($matches['sign'] . $matches['days']);
            $modify = str_replace($matches[0], '', $modify);
        }

        $modify = trim($modify);
        if ($modify) {
            parent::modify($modify);
        }

        return $this;
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

    public function diffWorkdaysSince(Workday $since): int
    {
        return static::diffWorkdays($since, $this, $this->getTimezone());
    }

    public function diffWorkdaysUntil(Workday $until): int
    {
        return static::diffWorkdays($this, $until, $this->getTimezone());
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
        if ($this->holidays === null) {
            $path = $this->getHolidayPath();
            if (!$path) {
                throw new Exception("Call setHolidayPath() first");
            }

            $filename = Path::combine($path, $this->getHolidayTemplate() . '.json');
            if (!File::exists($filename)) {
                throw new Exception("Holiday template does not exist");
            }

            $holidays = json_decode(File::getContent($filename), true);
            if (!is_array($holidays)) {
                throw new Exception("Holiday template not valid");
            }

            $this->setHolidays($holidays);
        }

        return $this->holidays;
    }

    /**
     * @return string
     */
    public function getHolidayTemplate()
    {
        return $this->holidayTemplate;
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
    public function hasTime()
    {
        return $this->format('H:i:s') != '00:00:00';
    }

    /**
     * @return bool
     */
    public function isHoliday()
    {
        return in_array($this->format(static::DAY_DATE_FORMAT), array_keys($this->getHolidays()));
    }

    /**
     * @return bool
     */
    public function isSunday()
    {
        return $this->getWeekday() == 7;
    }

    /**
     * @return bool
     */
    public function isSundayOrHoliday()
    {
        return $this->isSunday() || $this->isHoliday();
    }

    /**
     * @return bool
     */
    public function isWeekend()
    {
        return in_array($this->getWeekday(), $this->weekend);
    }

    /**
     * @return bool
     */
    public function isWeekendOrHoliday()
    {
        return $this->isWeekend() || $this->isHoliday();
    }

    /**
     * @return bool
     */
    public function isWorkday()
    {
        return !$this->isWeekend() && !$this->isHoliday();
    }

    /**
     * @param array|null $holidays
     *
     * @return $this
     */
    public function setHolidays(?array $holidays)
    {
        $this->holidays = $holidays;

        return $this;
    }

    /**
     * @param string $holidayTemplate
     *
     * @return $this
     */
    public function setHolidayTemplate(string $holidayTemplate)
    {
        if ($this->getHolidayTemplate() != $holidayTemplate) {
            $this->holidayTemplate = $holidayTemplate;
            $this->setHolidays(null);
        }

        return $this;
    }

    /**
     * array of integer, 1 (for Monday) through 7 (for Sunday)
     *
     * @param array $weekend
     *
     * @return $this
     */
    public function setWeekendDays(array $weekend)
    {
        $this->weekend = $weekend;

        return $this;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    public function toDateTime()
    {
        $date = new DateTime("@" . $this->getTimestamp());
        $date->setTimezone($this->getTimezone());

        return $date;
    }

    /**
     * @return $this
     */
    public function toUtc()
    {
        $this->setTimezone(new DateTimeZone('UTC'));

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
