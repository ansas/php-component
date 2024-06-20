<?php

/** @noinspection PhpUnused */

/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

use Exception;
use NumberFormatter;
use InvalidArgumentException;

class Number
{
    /**
     * Set nearest modes (functions)
     */
    const NEAREST_ROUND = 'round';
    const NEAREST_UP    = 'ceil';
    const NEAREST_DOWN  = 'floor';

    /**
     * @param float|int $value
     *
     * @return int
     */
    public static function countDigits($value)
    {
        return strlen(static::getDigits($value));
    }

    /**
     * @param float|int $value
     *
     * @return string
     */
    public static function getDigits($value)
    {
        return (string) substr(strrchr($value, "."), 1);
    }

    /**
     * Check if number is between min and max.
     *
     * @param float|int $value
     * @param float|int $min
     * @param float|int $max
     *
     * @return bool
     */
    public static function isBetween($value, $min, $max)
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Check if two numbers are equal.
     *
     * @param float $v1
     * @param float $v2
     * @param int   $precision [optional]
     *
     * @return bool
     */
    public static function isEqual($v1, $v2, $precision = 2)
    {
        return round((float) $v1, (int) $precision) == round((float) $v2, (int) $precision);
    }

    /**
     * Check if two numbers have same sign.
     *
     * @param float|int $v1
     * @param float|int $v2
     *
     * @return bool
     */
    public static function isSameSign($v1, $v2)
    {
        return ($v1 < 0) == ($v2 < 0);
    }

    public static function toReadableCurrency(float $amount, string $currency = 'EUR', string $locale = 'de_DE'): string
    {
        return NumberFormatter
            ::create($locale, NumberFormatter::CURRENCY)
            ->formatCurrency($amount, $currency)
        ;
    }

    /**
     * @throws Exception
     */
    public static function toReadableSize(
        int    $bytes,
        int    $decimals = 1,
        string $system = 'metric',
        string $locale = null
    ): string {
        $mod = ($system === 'binary') ? 1024 : 1000;

        $units = [
            'binary' => [
                'B',
                'KiB',
                'MiB',
                'GiB',
                'TiB',
                'PiB',
                'EiB',
                'ZiB',
                'YiB',
            ],
            'metric' => [
                'B',
                'kB',
                'MB',
                'GB',
                'TB',
                'PB',
                'EB',
                'ZB',
                'YB',
            ],
        ];

        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf(
            "%s %s",
            Format::number($bytes / pow($mod, $factor), ['fractionDigits' => $decimals, 'locale' => $locale]),
            $units[$system][$factor]
        );
    }

    /**
     * @throws Exception
     */
    public static function toReadableTime(float $time, int $decimals = 3, string $locale = null): string
    {
        return sprintf("%s sec", Format::number($time, ['fractionDigits' => $decimals, 'locale' => $locale]));
    }

    /**
     * @throws Exception
     */
    public static function toReadableWeight(?int $gramm, int $decimals = 1, string $locale = null): ?string
    {
        if ($gramm === null) {
            return null;
        }

        if ($gramm < 1_000) {
            return sprintf('%d g', $gramm);
        }

        return sprintf("%s kg", Format::number($gramm / 1_000, ['fractionDigits' => $decimals, 'locale' => $locale]));
    }

    /**
     * Compare $v1 and $v2 and calculate factor
     *
     * @param float|int $v1
     * @param float|int $v2
     * @param bool      $withZero  [optional]
     * @param int       $precision [optional]
     *
     * @return int
     */
    public static function toFactor($v1, $v2, $withZero = false, $precision = 2)
    {
        if ($withZero && static::isEqual($v1, $v2, $precision)) {
            return 0;
        }

        return ($v1 < $v2) ? -1 : 1;
    }

    /**
     * @param mixed $value
     * @param mixed $max
     *
     * @return mixed
     */
    public static function toMax($value, $max)
    {
        return ($max !== null && $max < $value) ? $max : $value;
    }

    /**
     * @param mixed $value
     * @param mixed $min
     *
     * @return mixed
     */
    public static function toMin($value, $min)
    {
        return ($min !== null && $min > $value) ? $min : $value;
    }

    /**
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     *
     * @return mixed
     */
    public static function toMinMax($value, $min, $max)
    {
        return static::toMin(static::toMax($value, $max), $min);
    }

    /**
     * @param float|int $value
     * @param float|int $step
     * @param string    $mode [optional]
     *
     * @return float
     * @throws InvalidArgumentException
     */
    public static function toNearestStep($value, $step, $mode = self::NEAREST_ROUND)
    {
        if (!$value || !$step) {
            return (float) $value;
        }

        if (!in_array($mode, ['round', 'ceil', 'floor'])) {
            throw new InvalidArgumentException("Mode '{$mode}' not supported");
        }

        $digits = static::countDigits($step);

        $value = (float) $value;
        $value = round($value / $step, $digits + 2);
        $value = $mode($value) * $step;

        return round($value, $digits);
    }

    public static function toNegative(int|float $value): int|float
    {
        return $value ? static::toPositive($value) * -1 : 0;
    }

    public static function toPositive(int|float $value): int|float
    {
        return abs($value);
    }
}
