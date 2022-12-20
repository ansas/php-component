<?php

/** @noinspection PhpMissingReturnTypeInspection */
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
     * Path to holiday templates
     */
    private static ?string $holidayTemplatePath;

    /**
     * Name of holiday template
     */
    protected ?string $holidayTemplate;

    /**
     * List of dates to consider as holiday
     */
    protected ?array $holidays;

    /**
     * List of days to consider as weekend
     */
    protected array $weekend = [
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

    /**
     * @throws Exception
     */
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

    final public static function getHolidayPath(): ?string
    {
        return static::$holidayTemplatePath ?? null;
    }

    final public static function setHolidayPath(string $path)
    {
        self::$holidayTemplatePath = $path;
    }

    /**
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
     * @throws Exception
     */
    public function modify($modifier): Workday|false
    {
        if (preg_match('/(?<direction>next|last)\s+workday/ui', $modifier, $matches)) {
            if (strtolower($matches['direction']) == 'next') {
                $this->nextWorkday();
            } else {
                $this->lastWorkday();
            }
            $modifier = str_replace($matches[0], '', $modifier);
        }

        if (preg_match('/(?<sign>[+\-])?\s*(?<days>\d+)\s*workdays?/ui', $modifier, $matches)) {
            $this->addWorkdays($matches['sign'] . $matches['days']);
            $modifier = str_replace($matches[0], '', $modifier);
        }

        $modifier = trim($modifier);
        if ($modifier) {
            parent::modify($modifier);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function addWorkdays(?int $days)
    {
        $done = 0;
        $days = (int) $days;
        $sign = '+';

        if (!$days) {
            return $this;
        }

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
     * @throws Exception
     */
    public function diffWorkdaysSince(Workday $since): int
    {
        return static::diffWorkdays($since, $this, $this->getTimezone());
    }

    /**
     * @throws Exception
     */
    public function diffWorkdaysUntil(Workday $until): int
    {
        return static::diffWorkdays($this, $until, $this->getTimezone());
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function lastWorkday()
    {
        return $this->toWorkday(-1);
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function nextWorkday()
    {
        return $this->toWorkday(1);
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function toWorkday(int $days)
    {
        if (!$this->isWorkday()) {
            $this->addWorkdays($days);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getHolidays(): array
    {
        if (!isset($this->holidays)) {
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

    public function getHolidayTemplate(): ?string
    {
        return $this->holidayTemplate ?? null;
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
     * @throws Exception
     */
    public function isHoliday(): bool
    {
        return in_array($this->format(static::DAY_DATE_FORMAT), array_keys($this->getHolidays()));
    }

    public function isSunday(): bool
    {
        return $this->getWeekday() == 7;
    }

    /**
     * @throws Exception
     */
    public function isSundayOrHoliday(): bool
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
     * @throws Exception
     */
    public function isWeekendOrHoliday(): bool
    {
        return $this->isWeekend() || $this->isHoliday();
    }

    /**
     * @throws Exception
     */
    public function isWorkday(): bool
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
        $this->setTime(0, 0);

        return $this;
    }
}
