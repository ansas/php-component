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
 * Class Object
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Number
{
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
}
