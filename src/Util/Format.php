<?php

namespace Ansas\Util;

use DateTime;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use IntlTimeZone;
use NumberFormatter;

class Format
{
    protected static string $locale         = 'de_DE';
    protected static string $calendarFormat = 'gregorian';
    protected static string $currencySymbol = 'EUR';
    protected static string $dateFormat     = 'medium';
    protected static string $timeFormat     = 'medium';
    protected static string $numberStyle    = 'decimal';
    protected static string $timezone       = 'Europe/Berlin';
    protected static int    $textLimit      = 250;

    public static function setLocale(string $locale): void
    {
        self::$locale = $locale;
    }

    public static function setCalendarFormat(string $calendarFormat): void
    {
        self::$calendarFormat = $calendarFormat;
    }

    public static function setCurrencySymbol(string $currencySymbol): void
    {
        self::$currencySymbol = $currencySymbol;
    }

    public static function setDateFormat(string $dateFormat): void
    {
        self::$dateFormat = $dateFormat;
    }

    public static function setTimeFormat(string $timeFormat): void
    {
        self::$timeFormat = $timeFormat;
    }

    public static function setNumberStyle(string $numberStyle): void
    {
        self::$numberStyle = $numberStyle;
    }

    public static function setTimezone(string $timezone): void
    {
        self::$timezone = $timezone;
    }

    public static function setTextLimit(int $textLimit): void
    {
        self::$textLimit = $textLimit;
    }

    public static function format($value, string $type, array $options = []): string
    {
        if (method_exists(self::class, $type)) {
            return self::{$type}($value, $options);
        }

        throw new Exception(sprintf('unknown type %s', $type));
    }

    public static function number($value, array $options = []): string
    {
        $formatter = new NumberFormatter(
            $options['locale'] ?? self::$locale,
            $options['style'] ?? self::$numberStyle ?? NumberFormatter::DECIMAL
        );

        $string = $formatter->format($value);

        if ($string === false) {
            throw new Exception('cannot format number');
        }

        return $string;
    }

    public static function currency($value, array $options = []): string
    {
        $formatter = new NumberFormatter($options['locale'] ?? self::$locale, NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        $symbol = $options['currencySymbol'] ?? self::$currencySymbol ?? $formatter->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL);

        return $formatter->formatCurrency($value, $symbol);
    }

    public static function date($value, array $options = []): string
    {
        if (!isset($options['dateFormat'])) {
            $options['dateFormat'] = self::$dateFormat;
        }

        $options['timeFormat'] = 'none';

        return self::getDateTimeFormatted($value, $options);
    }

    public static function time($value, array $options = []): string
    {
        if (!isset($options['timeFormat'])) {
            $options['timeFormat'] = self::$timeFormat;
        }

        $options['dateFormat'] = 'none';

        return self::getDateTimeFormatted($value, $options);
    }

    public static function dateTime($value, array $options = []): string
    {
        if (!isset($options['timeFormat'])) {
            $options['timeFormat'] = self::$timeFormat;
        }

        if (!isset($options['dateFormat'])) {
            $options['dateFormat'] = self::$dateFormat;
        }

        return self::getDateTimeFormatted($value, $options);
    }

    public static function text($value, array $options = []): string
    {
        return Text::truncate($value, $options['textLimit'] ?? self::$textLimit);
    }

    public static function bool($value, array $options = []): string
    {
        if ((bool) $value) {
            return $options['boolTrue'] ?? 'Ja';
        } else {
            return $options['boolFalse'] ?? 'Nein';
        }
    }

    private static function getDateTimeFormatted($value, array $options = [])
    {
        $timezone = new DateTimeZone($options['timezone'] ?? self::$timezone);
        $dateTime = new DateTime($value, $timezone);

        $formatValues = [
            'none'   => IntlDateFormatter::NONE,
            'short'  => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long'   => IntlDateFormatter::LONG,
            'full'   => IntlDateFormatter::FULL,
        ];

        $calendarValues = [
            'gregorian'   => IntlDateFormatter::GREGORIAN,
            'traditional' => IntlDateFormatter::TRADITIONAL,
        ];

        $formatter = IntlDateFormatter::create(
            $options['locale'] ?? self::$locale,
            $formatValues[$options['dateFormat'] ?? self::$dateFormat] ?? IntlDateFormatter::MEDIUM,
            $formatValues[$options['timeFormat'] ?? self::$timeFormat] ?? IntlDateFormatter::MEDIUM,
            IntlTimeZone::createTimeZone($timezone->getName()),
            $calendarValues[$options['calendarFormat'] ?? self::$calendarFormat] ?? IntlDateFormatter::GREGORIAN,
            $options['customFormat'] ?? null
        );

        return $formatter->format($dateTime->getTimestamp());
    }
}
