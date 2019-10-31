<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

/**
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Number
{
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
     * Convert to readable size.
     *
     * @param int    $bytes
     * @param int    $decimals [optional]
     * @param string $system   [optional] binary | metric
     *
     * @return string
     */
    public static function toReadableSize($bytes, $decimals = 1, $system = 'metric')
    {
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

        return sprintf("%.{$decimals}f %s", $bytes / pow($mod, $factor), $units[$system][$factor]);
    }

    /**
     * Convert to readable time.
     *
     * @param float $time
     * @param int   $decimals [optional]
     *
     * @return string
     */
    public static function toReadableTime($time, $decimals = 3)
    {
        $decimals = (int) $decimals;
        $unit     = 'sec';

        return sprintf("%.{$decimals}f %s", $time, $unit);
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
     *
     * @return float
     */
    public static function toNearestStep($value, $step)
    {
        if (!$value || !$step) {
            return (float) $value;
        }

        $value = (float) $value;
        $value = round($value / $step) * $step;

        return round($value, static::countDigits($step));
    }
}
