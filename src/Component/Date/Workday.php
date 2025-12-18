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
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * @method static static|false createFromFormat(string $format, string $datetime, ?DateTimeZone $timezone = null)
 */
class Workday extends DateTime
{
    const string DAY_DATE_FORMAT       = 'Y-m-d';
    const string DAY_WEEKDAY_FORMAT    = 'N';
    const string DEFAULT_TEMPLATE_NAME = 'default';

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
            parent::__construct($time);
            if ($timezone && str_starts_with($time, '@')) {
                $this->setTimezone($timezone);
            }
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
     * @throws Exception
     */
    public static function create(
        $time = 'now',
        DateTimeZone $timezone = null,
        string $holidayTemplate = self::DEFAULT_TEMPLATE_NAME
    ): static {
        return new static($time, $timezone, $holidayTemplate);
    }

    public static function tryFrom(
        $time = 'now',
        DateTimeZone $timezone = null,
        string $holidayTemplate = self::DEFAULT_TEMPLATE_NAME
    ): ?static {
        try {
            return new static($time, $timezone, $holidayTemplate);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    public static function diffDays(Workday $start, Workday $end, DateTimeZone $timezone = null): int
    {
        // Make sure we don't change original objects
        [$start, $end] = self::diffPrepare($start, $end, $timezone, true);

        return (int) $start->diff($end)->format('%R%a');
    }

    /**
     * @return static[]
     */
    public static function diffPrepare(
        Workday $start,
        Workday $end,
        ?DateTimeZone $timezone,
        bool $withoutTime
    ): array {
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
        if ($withoutTime) {
            $start->withoutTime();
            $end->withoutTime();
        }

        return [$start, $end];
    }

    /**
     * @throws Exception
     */
    public static function diffWorkdays(Workday $start, Workday $end, DateTimeZone $timezone = null): int
    {
        // Make sure we don't change original objects
        [$start, $end] = self::diffPrepare($start, $end, $timezone, true);

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
     * @throws Exception
     */
    public static function fromDateTime(DateTimeInterface $date): static
    {
        $workday = new static("@" . $date->getTimestamp());
        $workday->setTimezone($date->getTimezone());

        return $workday;
    }

    final public static function getHolidayPath(): ?string
    {
        return static::$holidayTemplatePath ?? null;
    }

    final public static function setHolidayPath(string $path): void
    {
        self::$holidayTemplatePath = $path;
    }

    public function clone(): static
    {
        return clone $this;
    }

    public function getHour(): int
    {
        return (int) $this->format('H');
    }

    public function getWeekday(): int
    {
        return (int) $this->format(static::DAY_WEEKDAY_FORMAT);
    }

    /**
     * @throws Exception
     */
    public function modify($modifier): static
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
     * @throws Exception
     */
    public function addWorkdays(?int $days): static
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
    public function subWorkdays(?int $days): static
    {
        return $this->addWorkdays($days * -1);
    }

    /**
     * @throws Exception
     */
    public function diffDaysSince(Workday $since): int
    {
        return static::diffDays($since, $this, $this->getTimezone());
    }

    /**
     * @throws Exception
     */
    public function diffDaysUntil(Workday $until): int
    {
        return static::diffDays($this, $until, $this->getTimezone());
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
     * @throws Exception
     */
    public function lastWorkday(): static
    {
        return $this->toWorkday(-1);
    }

    /**
     * @throws Exception
     */
    public function nextWorkday(): static
    {
        return $this->toWorkday(1);
    }

    /**
     * @throws Exception
     */
    public function toWorkday(int $days): static
    {
        if (!$this->isWorkday()) {
            $this->addWorkdays($days);
        }

        return $this;
    }

    public function getDateFormatted(): string
    {
        return $this->format(static::DAY_DATE_FORMAT);
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

    /**
     * @throws Exception
     */
    public function getHolidayName(): ?string
    {
        if (!$this->isHoliday()) {
            return null;
        }

        return $this->getHolidays()[$this->getDateFormatted()] ?? null;
    }

    /**
     * @throws Exception
     */
    public function getHolidayNext(): ?static
    {
        $holidays = $this->getHolidays();
        ksort($holidays);

        foreach ($holidays as $date => $name) {
            if ($date >= $this->getDateFormatted()) {
                return static
                    ::create($date, $this->getTimezone(), $this->getHolidayTemplate())
                    ->setHolidays($this->getHolidays())
                ;
            }
        }

        return null;
    }

    public function getHolidayTemplate(): ?string
    {
        return $this->holidayTemplate ?? null;
    }

    public function getWeekendDays(): array
    {
        return $this->weekend;
    }

    public function hasTime(): bool
    {
        return $this->format('H:i:s') != '00:00:00';
    }

    /**
     * @throws Exception
     */
    public function isFirstDayOfMonth(): bool
    {
        return $this->format('d') == 1;
    }

    /**
     * @throws Exception
     */
    public function isLastDayOfMonth(): bool
    {
        return $this->format('d') == $this->format('t');
    }

    /**
     * @throws Exception
     */
    public function isHoliday(): bool
    {
        return in_array($this->getDateFormatted(), array_keys($this->getHolidays()));
    }

    public function isSunday(): bool
    {
        return $this->getWeekday() == 7;
    }

    /**
     * @throws Exception
     */
    public function isToday(): bool
    {
        return $this->format('Y-m-d') == static::create('now', $this->getTimezone())->format('Y-m-d');
    }

    /**
     * @throws Exception
     */
    public function isPast(): bool
    {
        return $this < static::create();
    }

    /**
     * @throws Exception
     */
    public function isFuture(): bool
    {
        return $this > static::create();
    }

    /**
     * @throws Exception
     */
    public function isNow():bool
    {
        return $this == static::create();
    }

    /**
     * @throws Exception
     */
    public function isSundayOrHoliday(): bool
    {
        return $this->isSunday() || $this->isHoliday();
    }

    public function isWeekend(): bool
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

    public function setHolidays(?array $holidays): static
    {
        $this->holidays = $holidays;

        return $this;
    }

    public function setHolidayTemplate(string $holidayTemplate): static
    {
        if ($this->getHolidayTemplate() != $holidayTemplate) {
            $this->holidayTemplate = $holidayTemplate;
            $this->setHolidays(null);
        }

        return $this;
    }

    public function setWeekendDays(array $weekend): static
    {
        $this->weekend = $weekend;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function toDateTime(): DateTime
    {
        $date = new DateTime("@" . $this->getTimestamp());
        $date->setTimezone($this->getTimezone());

        return $date;
    }

    public function toMax(Workday $max): static
    {
        return min($this, $max);
    }

    public function toMin(Workday $min): static
    {
        return max($this, $min);
    }

    public function toUtc(): static
    {
        $this->setTimezone(new DateTimeZone('UTC'));

        return $this;
    }

    public function withoutTime(): static
    {
        $this->setTime(0, 0);

        return $this;
    }

    public function endOfDay(): static
    {
        $this->setTime(23, 59, 59);

        return $this;
    }
}
