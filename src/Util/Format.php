<?php

namespace Ansas\Util;

use DateTime;
use DateTimeZone;
use Exception;
use NumberFormatter;

class Format
{
    public static function format($value, string $type, array $options = []): string
    {
        if (method_exists(self::class, $type)) {
            return self::{$type}($value, $options);
        }

        throw new Exception(sprintf('unknown type %s', $type));
    }

    public static function number($value, array $options = []): string
    {
        $formatter = new NumberFormatter($options['locale'] ?? 'de_DE', $options['style'] ?? NumberFormatter::DECIMAL);

        $string = $formatter->format($value);

        if ($string === false) {
            throw new Exception('cannot format number');
        }

        return $string;
    }

    public static function currency($value, array $options = []): string
    {
        $formatter = new NumberFormatter($options['locale'] ?? 'de_DE', NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        return $formatter->formatCurrency($value, $formatter->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL));
    }

    public static function date($value, array $options = []): string
    {
        return self::getDateTimeFormatted($value, $options['dateFormat'] ?? 'd.m.Y', $options);
    }

    public static function dateTime($value, array $options = []): string
    {
        return self::getDateTimeFormatted($value, $options['dateTimeFormat'] ?? 'd.m.Y H:i', $options);
    }

    public static function day($value, array $options = []): string
    {
        return self::getDateTimeFormatted($value, $options['dayFormat'] ?? 'd', $options);
    }

    public static function month($value, array $options = []): string
    {
        return self::getDateTimeFormatted($value, $options['monthFormat'] ?? 'm', $options);
    }

    public static function year($value, array $options = []): string
    {
        return self::getDateTimeFormatted($value, $options['yearFormat'] ?? 'Y', $options);
    }

    public static function text($value, array $options = []): string
    {
        return Text::truncate($value, $options['textLimit'] ?? 250);
    }

    public static function bool($value, array $options = []): string
    {
        if ((bool) $value) {
            return $options['boolTrue'] ?? 'Ja';
        } else {
            return $options['boolFalse'] ?? 'Nein';
        }
    }

    private static function getDateTimeFormatted($value, string $format, array $options = [])
    {
        $timezone = new DateTimeZone($options['timezone'] ?? 'Europe/Berlin	');
        $dateTime = new DateTime($value, $timezone);

        return $dateTime->format($format);
    }
}
